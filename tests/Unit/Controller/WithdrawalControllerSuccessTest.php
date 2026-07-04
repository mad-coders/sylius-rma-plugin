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

use Doctrine\Persistence\ManagerRegistry;
use Madcoders\SyliusRmaPlugin\Controller\WithdrawalController;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Provider\OrderByNumberProviderInterface;
use Madcoders\SyliusRmaPlugin\Security\Voter\OrderReturnVoter;
use Madcoders\SyliusRmaPlugin\Services\ReturnRequestBuilder;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\InstantCancellationEligibilityCheckerInterface;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\OrderWithdrawalProcessorInterface;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\WithdrawalEligibilityCheckerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;
use Twig\Environment;

class WithdrawalControllerSuccessTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_denies_the_success_page_when_the_order_return_is_not_authorized(): void
    {
        $orderReturn = $this->prophesize(OrderReturnInterface::class);
        $orderReturn->getOrderNumber()->willReturn('000000123');

        $order = $this->prophesize(OrderInterface::class);

        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findOneBy(['returnNumber' => 'RMA-000000123-1'])->willReturn($orderReturn->reveal());

        $orderByNumberProvider = $this->prophesize(OrderByNumberProviderInterface::class);
        $orderByNumberProvider->findOneByNumber('000000123')->willReturn($order->reveal());

        $authorizationChecker = $this->prophesize(AuthorizationCheckerInterface::class);
        $authorizationChecker->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order->reveal())->willReturn(false);

        $twig = $this->prophesize(Environment::class);
        $twig->render(Argument::cetera())->shouldNotBeCalled();

        $router = $this->prophesize(RouterInterface::class);
        $router->generate('sylius_shop_homepage')->willReturn('/');

        $controller = $this->controller($twig->reveal(), $router->reveal(), $authorizationChecker->reveal(), $orderByNumberProvider->reveal(), $repository->reveal());

        $response = $controller->successIndex($this->request(), 'RMA-000000123-1', '@template.html.twig');

        $this->assertSame('/', $response->headers->get('Location'));
    }

    /** @test */
    function it_renders_the_success_page_for_an_authorized_order_return(): void
    {
        $orderReturn = $this->prophesize(OrderReturnInterface::class);
        $orderReturn->getOrderNumber()->willReturn('000000123');
        $orderReturn->getOrderReturnStatus()->willReturn(OrderReturnInterface::STATUS_WITHDRAWN);

        $order = $this->prophesize(OrderInterface::class);

        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findOneBy(['returnNumber' => 'RMA-000000123-1'])->willReturn($orderReturn->reveal());

        $orderByNumberProvider = $this->prophesize(OrderByNumberProviderInterface::class);
        $orderByNumberProvider->findOneByNumber('000000123')->willReturn($order->reveal());

        $authorizationChecker = $this->prophesize(AuthorizationCheckerInterface::class);
        $authorizationChecker->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order->reveal())->willReturn(true);

        $twig = $this->prophesize(Environment::class);
        $twig->render('@template.html.twig', Argument::that(static fn (array $context): bool => true === $context['withdrawn']))
            ->willReturn('rendered');

        $router = $this->prophesize(RouterInterface::class);

        $controller = $this->controller($twig->reveal(), $router->reveal(), $authorizationChecker->reveal(), $orderByNumberProvider->reveal(), $repository->reveal());

        $response = $controller->successIndex($this->request(), 'RMA-000000123-1', '@template.html.twig');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('rendered', $response->getContent());
    }

    private function request(): Request
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    private function controller(
        Environment $twig,
        RouterInterface $router,
        AuthorizationCheckerInterface $authorizationChecker,
        OrderByNumberProviderInterface $orderByNumberProvider,
        RepositoryInterface $orderReturnRepository,
    ): WithdrawalController {
        $translator = $this->prophesize(TranslatorInterface::class);
        $translator->trans(Argument::cetera())->willReturnArgument(0);

        return new WithdrawalController(
            $twig,
            $router,
            $authorizationChecker,
            $orderByNumberProvider,
            $this->prophesize(WithdrawalEligibilityCheckerInterface::class)->reveal(),
            $this->prophesize(InstantCancellationEligibilityCheckerInterface::class)->reveal(),
            $this->prophesize(ReturnRequestBuilder::class)->reveal(),
            $this->prophesize(OrderWithdrawalProcessorInterface::class)->reveal(),
            $this->prophesize(StateMachineFactoryInterface::class)->reveal(),
            $orderReturnRepository,
            $this->prophesize(CsrfTokenManagerInterface::class)->reveal(),
            $translator->reveal(),
            $this->prophesize(FormFactoryInterface::class)->reveal(),
            $this->prophesize(ManagerRegistry::class)->reveal(),
        );
    }
}
