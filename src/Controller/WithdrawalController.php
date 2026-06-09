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
use Madcoders\SyliusRmaPlugin\Provider\OrderByNumberProviderInterface;
use Madcoders\SyliusRmaPlugin\Security\Voter\OrderReturnVoter;
use Madcoders\SyliusRmaPlugin\Services\ReturnRequestBuilder;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\OrderWithdrawalProcessorInterface;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\WithdrawalEligibilityCheckerInterface;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\WithdrawalPath;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final readonly class WithdrawalController
{
    private const CSRF_TOKEN_ID = 'madcoders_rma_withdrawal';

    /**
     * @param RepositoryInterface<OrderReturnInterface> $orderReturnRepository
     */
    public function __construct(
        private Environment $twig,
        private RouterInterface $router,
        private AuthorizationCheckerInterface $authorizationChecker,
        private OrderByNumberProviderInterface $orderByNumberProvider,
        private WithdrawalEligibilityCheckerInterface $withdrawalEligibilityChecker,
        private ReturnRequestBuilder $returnRequestBuilder,
        private OrderWithdrawalProcessorInterface $orderWithdrawalProcessor,
        private StateMachineFactoryInterface $stateMachineFactory,
        private RepositoryInterface $orderReturnRepository,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function withdrawIndex(Request $request, string $orderNumber, string $template): Response
    {
        $order = $this->orderByNumberProvider->findOneByNumber($orderNumber);
        if (!$order instanceof OrderInterface) {
            return $this->errorRedirect($request, 'madcoders_rma.ui.first_step.error.order_number_not_valid', ['%orderNumber%' => $orderNumber]);
        }

        if (!$this->authorizationChecker->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order)) {
            return $this->errorRedirect($request, 'madcoders_rma.ui.return.user_not_privileges_to_this_order');
        }

        $path = $this->withdrawalEligibilityChecker->resolvePath($order);
        if (WithdrawalPath::NONE === $path) {
            return $this->errorRedirect($request, 'madcoders_rma.ui.first_step.error.withdrawal_not_available', ['%orderNumber%' => $orderNumber]);
        }

        if ($request->isMethod('POST') && $this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_TOKEN_ID, (string) $request->request->get('_token')))) {
            $orderReturn = $this->returnRequestBuilder->build($orderNumber);

            if (WithdrawalPath::UNPAID_AUTOCANCEL === $path) {
                $this->orderWithdrawalProcessor->process($order, $orderReturn);
            } else {
                $stateMachine = $this->stateMachineFactory->get($orderReturn, OrderReturnInterface::GRAPH);
                if ($stateMachine->can(OrderReturnInterface::TRANSITION_REQUEST_CANCELLATION)) {
                    $stateMachine->apply(OrderReturnInterface::TRANSITION_REQUEST_CANCELLATION);
                }
            }

            $this->orderReturnRepository->add($orderReturn);

            return new RedirectResponse($this->router->generate('madcoders_rma_withdrawal_success', ['returnNumber' => $orderReturn->getReturnNumber()]));
        }

        return new Response($this->twig->render($template, [
            'order' => $order,
            'orderNumber' => $orderNumber,
            'isUnpaid' => WithdrawalPath::UNPAID_AUTOCANCEL === $path,
            'csrfToken' => $this->csrfTokenManager->getToken(self::CSRF_TOKEN_ID)->getValue(),
        ]));
    }

    public function successIndex(Request $request, string $returnNumber, string $template): Response
    {
        $orderReturn = $this->orderReturnRepository->findOneBy(['returnNumber' => $returnNumber]);
        if (!$orderReturn instanceof OrderReturnInterface) {
            return $this->errorRedirect($request, 'madcoders_rma.ui.first_step.error.order_number_not_valid', ['%orderNumber%' => $returnNumber]);
        }

        return new Response($this->twig->render($template, [
            'orderReturn' => $orderReturn,
            'withdrawn' => OrderReturnInterface::STATUS_WITHDRAWN === $orderReturn->getOrderReturnStatus(),
        ]));
    }

    private function errorRedirect(Request $request, string $errorMessage, array $context = []): RedirectResponse
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $this->translator->trans($errorMessage, $context));

        return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
    }
}
