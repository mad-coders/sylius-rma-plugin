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

use Madcoders\SyliusRmaPlugin\Form\Type\GracePeriodType;
use Madcoders\SyliusRmaPlugin\Services\GracePeriod\GracePeriodOverviewRow;
use Madcoders\SyliusRmaPlugin\Services\GracePeriod\OrderGracePeriodOverviewProvider;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * Renders the grace period panel on the Sylius admin order page (#67). Embedded with
 * render(controller()) from a sylius_ui block, so the panel can build a real form while the
 * core order page stays untouched.
 */
final readonly class AdminGracePeriodPanelController
{
    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private OrderGracePeriodOverviewProvider $overviewProvider,
        private FormFactoryInterface $formFactory,
        private RouterInterface $router,
        private Environment $twig,
    ) {
    }

    public function __invoke(int $orderId): Response
    {
        $order = $this->orderRepository->find($orderId);
        if (!$order instanceof OrderInterface) {
            throw new NotFoundHttpException(sprintf('Order %d has not been found', $orderId));
        }

        $rows = $this->overviewProvider->getRows($order);

        $form = $this->formFactory->create(GracePeriodType::class, null, [
            'action' => $this->router->generate('madcoders_rma_admin_order_grace_period_grant', ['orderId' => $orderId]),
            'reasons' => $this->reasonChoices($rows),
        ]);

        return new Response($this->twig->render('@MadcodersSyliusRmaPlugin/Admin/Order/GracePeriod/_panel.html.twig', [
            'order' => $order,
            'rows' => $rows,
            'history' => $this->overviewProvider->getHistory($order),
            'shipped_at' => $this->overviewProvider->getShippedAt($order),
            'form' => $form->createView(),
        ]));
    }

    /**
     * @param list<GracePeriodOverviewRow> $rows
     *
     * @return array<string, string> reason name => reason code
     */
    private function reasonChoices(array $rows): array
    {
        $choices = [];
        foreach ($rows as $row) {
            $code = (string) $row->reason->getCode();
            $choices[$row->reason->getName() ?? $code] = $code;
        }

        return $choices;
    }
}
