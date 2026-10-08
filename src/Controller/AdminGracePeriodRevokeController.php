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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodInterface;
use Madcoders\SyliusRmaPlugin\Services\GracePeriod\GracePeriodManagerInterface;
use Madcoders\SyliusRmaPlugin\Services\RmaAdminUserData;
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
 * Revokes an order's grace period; the reason falls back to its base deadline (#67).
 */
final readonly class AdminGracePeriodRevokeController
{
    public const CSRF_TOKEN_PREFIX = 'madcoders_rma_grace_period_revoke_';

    /**
     * @param RepositoryInterface<OrderReturnReasonGracePeriodInterface> $gracePeriodRepository
     */
    public function __construct(
        private RepositoryInterface $gracePeriodRepository,
        private GracePeriodManagerInterface $gracePeriodManager,
        private RmaAdminUserData $adminUserData,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private RouterInterface $router,
        private TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Request $request, int $orderId, int $id): RedirectResponse
    {
        $gracePeriod = $this->gracePeriodRepository->find($id);
        if (!$gracePeriod instanceof OrderReturnReasonGracePeriodInterface || $gracePeriod->getOrder()->getId() !== $orderId) {
            throw new NotFoundHttpException(sprintf('Grace period %d has not been found for order %d', $id, $orderId));
        }

        $csrfToken = $request->request->getString('_token');
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_TOKEN_PREFIX . $id, $csrfToken))) {
            return $this->flashRedirect($request, 'error', 'sylius.ui.invalid_csrf_token', $orderId);
        }

        $this->gracePeriodManager->revoke($gracePeriod, $this->adminUserData->getAdminUserData());

        return $this->flashRedirect($request, 'success', 'madcoders_rma.admin.grace_period.flashes.revoked', $orderId);
    }

    private function flashRedirect(Request $request, string $type, string $message, int $orderId): RedirectResponse
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add($type, $this->translator->trans($message));

        return new RedirectResponse($this->router->generate('sylius_admin_order_show', ['id' => $orderId]));
    }
}
