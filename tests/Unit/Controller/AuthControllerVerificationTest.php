<?php

/*
 * This file is part of package:
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS Team (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Controller;

use Madcoders\SyliusRmaPlugin\Controller\AuthController;
use Madcoders\SyliusRmaPlugin\Email\AuthCodeEmailSenderInterface;
use Madcoders\SyliusRmaPlugin\Entity\AuthCode;
use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Madcoders\SyliusRmaPlugin\Provider\OrderByNumberProviderInterface;
use Madcoders\SyliusRmaPlugin\Security\OrderReturnAuthorizerInterface;
use Madcoders\SyliusRmaPlugin\Security\Voter\OrderReturnVoter;
use Madcoders\SyliusRmaPlugin\Services\AuthCode\AuthCodeFactoryInterface;
use Madcoders\SyliusRmaPlugin\Services\AuthCode\AuthThrottlerInterface;
use Madcoders\SyliusRmaPlugin\Services\ReturnEligibilityCheckerInterface;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\WithdrawalEligibilityCheckerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;
use Twig\Environment;

class AuthControllerVerificationTest extends UnitTestCase
{
    use ProphecyTrait;

    private const CODE = 87654321;

    /** @var ObjectProphecy<RepositoryInterface<AuthCodeInterface>> */
    private ObjectProphecy $authCodeRepository;

    /** @var ObjectProphecy<OrderReturnAuthorizerInterface> */
    private ObjectProphecy $orderReturnAuthorizer;

    /** @var ObjectProphecy<AuthThrottlerInterface> */
    private ObjectProphecy $authThrottler;

    /** @var ObjectProphecy<RouterInterface> */
    private ObjectProphecy $router;

    /** @var ObjectProphecy<OrderInterface> */
    private ObjectProphecy $order;

    protected function setUp(): void
    {
        $this->authCodeRepository = $this->prophesize(RepositoryInterface::class);
        $this->orderReturnAuthorizer = $this->prophesize(OrderReturnAuthorizerInterface::class);
        $this->authThrottler = $this->prophesize(AuthThrottlerInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->order = $this->prophesize(OrderInterface::class);
    }

    /** @test */
    function it_locks_out_and_invalidates_the_code_once_the_attempt_limit_is_reached(): void
    {
        $authCode = $this->authCode();
        $authCode->getAttempts()->willReturn(AuthCode::DEFAULT_MAX_ATTEMPTS);
        $this->givenStoredCode($authCode);

        // the code is deleted and no guess is evaluated
        $this->authCodeRepository->remove($authCode->reveal())->shouldBeCalled();
        $this->orderReturnAuthorizer->authorize(Argument::any())->shouldNotBeCalled();
        $this->router->generate('madcoders_rma_start')->willReturn('/start');

        $response = $this->controller(self::CODE)->verification($this->postRequest(), '@template', 'the-hash');

        $this->assertSame('/start', $response->headers->get('Location'));
    }

    /** @test */
    function it_authorizes_and_consumes_the_code_on_a_correct_guess(): void
    {
        $authCode = $this->authCode();
        $authCode->getAttempts()->willReturn(0);
        $this->givenStoredCode($authCode);
        $this->authThrottler->throttle(Argument::any())->willReturn(null);

        $this->orderReturnAuthorizer->authorize($this->order->reveal())->shouldBeCalled();
        $this->authCodeRepository->remove($authCode->reveal())->shouldBeCalled();
        $this->authThrottler->reset(Argument::any())->shouldBeCalled();
        $this->router->generate('madcoders_rma_return_form', ['orderNumber' => '000000123'])->willReturn('/return-form');

        $response = $this->controller(self::CODE)->verification($this->postRequest(), '@template', 'the-hash');

        $this->assertSame('/return-form', $response->headers->get('Location'));
    }

    /** @test */
    function a_wrong_guess_that_reaches_the_limit_invalidates_the_code(): void
    {
        $authCode = $this->authCode();
        $authCode->increaseNumberOfAttempts()->shouldBeCalled();
        // < limit at the top-of-method guard, == limit after the failed guess increments it
        $authCode->getAttempts()->willReturn(AuthCode::DEFAULT_MAX_ATTEMPTS - 1, AuthCode::DEFAULT_MAX_ATTEMPTS);
        $this->givenStoredCode($authCode);
        $this->authThrottler->throttle(Argument::any())->willReturn(null);

        $this->orderReturnAuthorizer->authorize(Argument::any())->shouldNotBeCalled();
        $this->authCodeRepository->remove($authCode->reveal())->shouldBeCalled();
        $this->router->generate('madcoders_rma_start')->willReturn('/start');

        // submit a value that does not match self::CODE
        $response = $this->controller(11111111)->verification($this->postRequest(), '@template', 'the-hash');

        $this->assertSame('/start', $response->headers->get('Location'));
    }

    /** @test */
    function it_returns_429_with_retry_after_when_the_verification_is_rate_limited(): void
    {
        $authCode = $this->authCode();
        $authCode->getAttempts()->willReturn(0);
        $this->givenStoredCode($authCode);
        $this->authThrottler->throttle(Argument::any())->willReturn(42);

        $this->orderReturnAuthorizer->authorize(Argument::any())->shouldNotBeCalled();

        $response = $this->controller(self::CODE)->verification($this->postRequest(), '@template', 'the-hash');

        $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
        $this->assertSame('42', $response->headers->get('Retry-After'));
    }

    /**
     * @return ObjectProphecy<AuthCodeInterface>
     */
    private function authCode(): ObjectProphecy
    {
        $authCode = $this->prophesize(AuthCodeInterface::class);
        $authCode->getExpiresAt()->willReturn((new \DateTime())->modify('+5 minutes'));
        $authCode->getAuthCode()->willReturn(self::CODE);
        $authCode->getOrderNumber()->willReturn('000000123');

        return $authCode;
    }

    /**
     * @param ObjectProphecy<AuthCodeInterface> $authCode
     */
    private function givenStoredCode(ObjectProphecy $authCode): void
    {
        $this->authCodeRepository->findOneBy(['hash' => 'the-hash'])->willReturn($authCode->reveal());
    }

    private function postRequest(): Request
    {
        $request = Request::create('/verify', 'POST');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $request->attributes->set('_sylius', [
            'redirect' => 'madcoders_rma_return_form',
            'error_redirect' => 'madcoders_rma_verification',
        ]);

        return $request;
    }

    private function controller(int $submittedCode): AuthController
    {
        $form = $this->prophesize(FormInterface::class);
        $formDouble = $form->reveal();
        $form->handleRequest(Argument::any())->willReturn($formDouble);
        $form->isValid()->willReturn(true);
        $form->getData()->willReturn(['authCode' => $submittedCode]);
        $form->createView()->willReturn(new FormView());

        $formFactory = $this->prophesize(FormFactoryInterface::class);
        $formFactory->create(Argument::cetera())->willReturn($formDouble);

        $orderProvider = $this->prophesize(OrderByNumberProviderInterface::class);
        $orderProvider->findOneByNumber('000000123')->willReturn($this->order->reveal());

        $withdrawalChecker = $this->prophesize(WithdrawalEligibilityCheckerInterface::class);
        $withdrawalChecker->isWithdrawable(Argument::any())->willReturn(false);

        $authorizationChecker = $this->prophesize(AuthorizationCheckerInterface::class);
        $authorizationChecker->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, Argument::any())->willReturn(false);

        $translator = $this->prophesize(TranslatorInterface::class);
        $translator->trans(Argument::cetera())->willReturnArgument(0);

        $twig = $this->prophesize(Environment::class);
        $twig->render(Argument::cetera())->willReturn('rendered');

        return new AuthController(
            $formFactory->reveal(),
            $twig->reveal(),
            $this->router->reveal(),
            $this->prophesize(AuthCodeEmailSenderInterface::class)->reveal(),
            $translator->reveal(),
            $this->orderReturnAuthorizer->reveal(),
            $orderProvider->reveal(),
            $this->prophesize(AuthCodeFactoryInterface::class)->reveal(),
            $authorizationChecker->reveal(),
            $this->authCodeRepository->reveal(),
            $withdrawalChecker->reveal(),
            $this->prophesize(ReturnEligibilityCheckerInterface::class)->reveal(),
            $this->authThrottler->reveal(),
        );
    }
}
