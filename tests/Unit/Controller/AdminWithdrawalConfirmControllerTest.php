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

use Madcoders\SyliusRmaPlugin\Controller\AdminWithdrawalConfirmController;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class AdminWithdrawalConfirmControllerTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_confirms_a_withdrawal_by_applying_the_withdraw_transition_without_touching_the_order()
    {
        $orderReturn = $this->prophesize(OrderReturnInterface::class);

        $stateMachine = $this->prophesize(StateMachineInterface::class);
        $stateMachine->can(OrderReturnInterface::TRANSITION_WITHDRAW)->willReturn(true);
        $stateMachine->apply(OrderReturnInterface::TRANSITION_WITHDRAW)->shouldBeCalled();

        $stateMachineFactory = $this->prophesize(StateMachineFactoryInterface::class);
        $stateMachineFactory->get($orderReturn->reveal(), OrderReturnInterface::GRAPH)->willReturn($stateMachine->reveal());

        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findOneBy(['id' => 7])->willReturn($orderReturn->reveal());
        $repository->add($orderReturn->reveal())->shouldBeCalled();

        $controller = $this->controller($stateMachineFactory->reveal(), $repository->reveal(), true);

        $response = $controller->__invoke($this->request(), 7);

        $this->assertSame('/admin/order-returns/7', $response->getTargetUrl());
    }

    /** @test */
    function it_flashes_an_error_when_the_return_cannot_be_withdrawn()
    {
        $orderReturn = $this->prophesize(OrderReturnInterface::class);

        $stateMachine = $this->prophesize(StateMachineInterface::class);
        $stateMachine->can(OrderReturnInterface::TRANSITION_WITHDRAW)->willReturn(false);
        $stateMachine->apply(Argument::any())->shouldNotBeCalled();

        $stateMachineFactory = $this->prophesize(StateMachineFactoryInterface::class);
        $stateMachineFactory->get($orderReturn->reveal(), OrderReturnInterface::GRAPH)->willReturn($stateMachine->reveal());

        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findOneBy(['id' => 7])->willReturn($orderReturn->reveal());
        $repository->add(Argument::any())->shouldNotBeCalled();

        $controller = $this->controller($stateMachineFactory->reveal(), $repository->reveal(), true);

        $response = $controller->__invoke($this->request(), 7);

        $this->assertSame('/admin/order-returns/7', $response->getTargetUrl());
    }

    /** @test */
    function it_rejects_an_invalid_csrf_token()
    {
        $orderReturn = $this->prophesize(OrderReturnInterface::class);

        $stateMachineFactory = $this->prophesize(StateMachineFactoryInterface::class);
        $stateMachineFactory->get(Argument::cetera())->shouldNotBeCalled();

        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findOneBy(['id' => 7])->willReturn($orderReturn->reveal());
        $repository->add(Argument::any())->shouldNotBeCalled();

        $controller = $this->controller($stateMachineFactory->reveal(), $repository->reveal(), false);

        $response = $controller->__invoke($this->request(), 7);

        $this->assertSame('/admin/order-returns/7', $response->getTargetUrl());
    }

    private function request(): Request
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $request->attributes->set('_csrf_token', 'token');

        return $request;
    }

    private function controller(
        StateMachineFactoryInterface $stateMachineFactory,
        RepositoryInterface $repository,
        bool $csrfValid,
    ): AdminWithdrawalConfirmController {
        $csrfTokenManager = $this->prophesize(CsrfTokenManagerInterface::class);
        $csrfTokenManager->isTokenValid(Argument::any())->willReturn($csrfValid);

        $router = $this->prophesize(RouterInterface::class);
        $router->generate('madcoders_rma_admin_order_return_show', ['id' => 7])->willReturn('/admin/order-returns/7');

        $translator = $this->prophesize(TranslatorInterface::class);
        $translator->trans(Argument::any())->willReturnArgument(0);

        return new AdminWithdrawalConfirmController(
            $repository,
            $stateMachineFactory,
            $router->reveal(),
            $csrfTokenManager->reveal(),
            $translator->reveal(),
        );
    }
}
