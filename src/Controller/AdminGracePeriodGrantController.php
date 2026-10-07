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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodLogInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Madcoders\SyliusRmaPlugin\Form\Type\GracePeriodType;
use Madcoders\SyliusRmaPlugin\Services\GracePeriod\GracePeriodManagerInterface;
use Madcoders\SyliusRmaPlugin\Services\RmaAdminUserData;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
 * Grants a grace period on a return reason for one order, or changes the existing one (#67).
 * The form carries Symfony's CSRF token; invalid input is reported as flash messages and
 * nothing is saved.
 */
final readonly class AdminGracePeriodGrantController
{
    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     * @param RepositoryInterface<OrderReturnReasonInterface> $reasonRepository
     */
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private RepositoryInterface $reasonRepository,
        private GracePeriodManagerInterface $gracePeriodManager,
        private RmaAdminUserData $adminUserData,
        private FormFactoryInterface $formFactory,
        private RouterInterface $router,
        private TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Request $request, int $orderId): RedirectResponse
    {
        $order = $this->orderRepository->find($orderId);
        if (!$order instanceof OrderInterface) {
            throw new NotFoundHttpException(sprintf('Order %d has not been found', $orderId));
        }

        $reasons = $this->enabledReasonsByCode();
        $choices = [];
        foreach ($reasons as $code => $reason) {
            $choices[$reason->getName() ?? $code] = $code;
        }

        $form = $this->formFactory->create(GracePeriodType::class, null, ['reasons' => $choices]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $messages = [];
            foreach ($form->getErrors(true) as $error) {
                if ($error instanceof FormError) {
                    $messages[] = $error->getMessage();
                }
            }

            return $this->flashRedirect($request, 'error', [] === $messages ? [$this->translator->trans('madcoders_rma.admin.grace_period.flashes.invalid')] : $messages, $orderId);
        }

        /** @var array{reason: string, extraDays: string, note: string|null} $data */
        $data = $form->getData();
        Assert::keyExists($reasons, $data['reason']);
        Assert::regex($data['extraDays'], '/^\d+$/');

        $log = $this->gracePeriodManager->grant(
            $order,
            $reasons[$data['reason']],
            (int) $data['extraDays'],
            $data['note'],
            $this->adminUserData->getAdminUserData(),
        );

        $message = match ($log?->getAction()) {
            OrderReturnReasonGracePeriodLogInterface::ACTION_GRANTED => 'madcoders_rma.admin.grace_period.flashes.granted',
            OrderReturnReasonGracePeriodLogInterface::ACTION_UPDATED => 'madcoders_rma.admin.grace_period.flashes.updated',
            default => 'madcoders_rma.admin.grace_period.flashes.unchanged',
        };

        return $this->flashRedirect($request, 'success', [$this->translator->trans($message)], $orderId);
    }

    /**
     * @return array<string, OrderReturnReasonInterface>
     */
    private function enabledReasonsByCode(): array
    {
        $reasons = [];
        /** @var OrderReturnReasonInterface $reason */
        foreach ($this->reasonRepository->findBy(['enabled' => true], ['position' => 'ASC']) as $reason) {
            $code = $reason->getCode();
            if (null !== $code) {
                $reasons[$code] = $reason;
            }
        }

        return $reasons;
    }

    /**
     * @param list<string> $messages already translated
     */
    private function flashRedirect(Request $request, string $type, array $messages, int $orderId): RedirectResponse
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        foreach ($messages as $message) {
            $flashBag->add($type, $message);
        }

        return new RedirectResponse($this->router->generate('sylius_admin_order_show', ['id' => $orderId]));
    }
}
