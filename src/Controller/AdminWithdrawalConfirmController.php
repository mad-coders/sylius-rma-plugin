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

namespace Madcoders\SyliusRmaPlugin\Controller;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Admin action that approves a paid withdrawal request: resolves the return to "withdrawn" by
 * applying the `withdraw` transition (which fires the resolution notifier - changelog + e-mail).
 * Because a paid withdrawal may be partial, the underlying Sylius order is intentionally left
 * untouched here; the refund and any order cancellation remain manual admin actions. Only the
 * unpaid, instant customer path cancels the whole order (via {@see OrderWithdrawalProcessor}).
 */
final readonly class AdminWithdrawalConfirmController
{
    /**
     * @param RepositoryInterface<OrderReturnInterface> $orderReturnRepository
     */
    public function __construct(
        private RepositoryInterface $orderReturnRepository,
        private StateMachineFactoryInterface $stateMachineFactory,
        private RouterInterface $router,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Request $request, int $id): RedirectResponse
    {
        $orderReturn = $this->orderReturnRepository->findOneBy(['id' => $id]);
        if (!$orderReturn instanceof OrderReturnInterface) {
            throw new NotFoundHttpException(sprintf('Order return %d has not been found', $id));
        }

        $csrfToken = $request->attributes->getString('_csrf_token');
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken((string) $id, $csrfToken))) {
            return $this->flashRedirect($request, 'error', 'sylius.ui.invalid_csrf_token', $id);
        }

        $stateMachine = $this->stateMachineFactory->get($orderReturn, OrderReturnInterface::GRAPH);
        if (!$stateMachine->can(OrderReturnInterface::TRANSITION_WITHDRAW)) {
            return $this->flashRedirect($request, 'error', 'madcoders_rma.ui.withdrawal.error.not_cancellable', $id);
        }

        $stateMachine->apply(OrderReturnInterface::TRANSITION_WITHDRAW);
        $this->orderReturnRepository->add($orderReturn);

        return $this->flashRedirect($request, 'success', 'madcoders.admin.history.withdrawal_confirmed', $id);
    }

    private function flashRedirect(Request $request, string $type, string $message, int $id): RedirectResponse
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add($type, $this->translator->trans($message));

        return new RedirectResponse($this->router->generate('madcoders_rma_admin_order_return_show', ['id' => $id]));
    }
}
