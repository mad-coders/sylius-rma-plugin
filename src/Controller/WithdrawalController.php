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

use Doctrine\Persistence\ManagerRegistry;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsent;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnConsentFormType;
use Madcoders\SyliusRmaPlugin\Form\Type\WithdrawalReturnFormType;
use Madcoders\SyliusRmaPlugin\Provider\OrderByNumberProviderInterface;
use Madcoders\SyliusRmaPlugin\Security\Voter\OrderReturnVoter;
use Madcoders\SyliusRmaPlugin\Services\ReturnRequestBuilder;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\InstantCancellationEligibilityCheckerInterface;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\OrderWithdrawalProcessorInterface;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\WithdrawalEligibilityCheckerInterface;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
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
        private InstantCancellationEligibilityCheckerInterface $instantCancellationEligibilityChecker,
        private ReturnRequestBuilder $returnRequestBuilder,
        private OrderWithdrawalProcessorInterface $orderWithdrawalProcessor,
        private StateMachineFactoryInterface $stateMachineFactory,
        private RepositoryInterface $orderReturnRepository,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private TranslatorInterface $translator,
        private FormFactoryInterface $formFactory,
        private ManagerRegistry $managerRegistry,
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

        if (!$this->withdrawalEligibilityChecker->isWithdrawable($order)) {
            return $this->errorRedirect($request, 'madcoders_rma.ui.first_step.error.withdrawal_not_available', ['%orderNumber%' => $orderNumber]);
        }

        // Unpaid order: instant, whole-order withdrawal from a single confirmation screen.
        if ($this->instantCancellationEligibilityChecker->isEligible($order)) {
            return $this->instantWithdraw($request, $order, $orderNumber, $template);
        }

        // Paid order: reuse the standard return item-selection screen so the customer can choose
        // which items/quantities to withdraw (partial withdrawals are allowed).
        return $this->requestWithdrawalForm($request, $orderNumber);
    }

    /**
     * @throws \Exception
     */
    private function instantWithdraw(Request $request, OrderInterface $order, string $orderNumber, string $template): Response
    {
        if ($request->isMethod('POST') && $this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_TOKEN_ID, (string) $request->request->get('_token')))) {
            $orderReturn = $this->returnRequestBuilder->build($orderNumber);

            // Fast-forward: cancel the order and resolve the return straight to "withdrawn".
            $this->orderWithdrawalProcessor->process($order, $orderReturn);
            $this->orderReturnRepository->add($orderReturn);

            return new RedirectResponse($this->router->generate('madcoders_rma_withdrawal_success', ['returnNumber' => $orderReturn->getReturnNumber()]));
        }

        return new Response($this->twig->render($template, [
            'order' => $order,
            'orderNumber' => $orderNumber,
            'isUnpaid' => true,
            'csrfToken' => $this->csrfTokenManager->getToken(self::CSRF_TOKEN_ID)->getValue(),
        ]));
    }

    /**
     * @throws \Exception
     */
    private function requestWithdrawalForm(Request $request, string $orderNumber): Response
    {
        $orderReturn = $this->returnRequestBuilder->build($orderNumber);
        $returnNumber = $orderReturn->getReturnNumber();
        $form = $this->formFactory->create(WithdrawalReturnFormType::class, $orderReturn);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->orderReturnRepository->add($orderReturn);

            return new RedirectResponse($this->router->generate('madcoders_rma_withdrawal_form_accept', ['returnNumber' => $returnNumber]));
        }

        return new Response($this->twig->render('@MadcodersSyliusRmaPlugin/Return/view.html.twig', [
            'orderNumber' => $orderNumber,
            'form' => $form->createView(),
            'formAction' => $this->router->generate('madcoders_rma_withdrawal', ['orderNumber' => $orderNumber]),
        ]));
    }

    /**
     * @throws \Exception
     */
    public function withdrawAcceptIndex(Request $request, string $returnNumber, string $template): Response
    {
        $orderReturn = $this->orderReturnRepository->findOneBy(['returnNumber' => $returnNumber]);
        if (!$orderReturn instanceof OrderReturnInterface) {
            return $this->errorRedirect($request, 'madcoders_rma.ui.first_step.error.order_number_not_valid', ['%orderNumber%' => $returnNumber]);
        }

        $order = $this->orderByNumberProvider->findOneByNumber($orderReturn->getOrderNumber());
        if (!$order instanceof OrderInterface) {
            return $this->errorRedirect($request, 'madcoders_rma.ui.first_step.error.order_number_not_valid', ['%orderNumber%' => $orderReturn->getOrderNumber()]);
        }

        if (!$this->authorizationChecker->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order)) {
            return $this->errorRedirect($request, 'madcoders_rma.ui.return.user_not_privileges_to_this_order');
        }

        $consentData = ['consents' => []];
        /** @var OrderReturnConsent $consent */
        foreach ($this->managerRegistry->getRepository(OrderReturnConsent::class)->findBy(['enabled' => true], ['position' => 'asc']) as $consent) {
            $consentData['consents'][] = [
                'code' => $consent->getCode(),
                'label' => $consent->getTranslation()->getName(),
                'consentRequire' => $consent->isConsentRequire(),
            ];
        }

        $form = $this->formFactory->create(ReturnConsentFormType::class, $consentData);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            /** @var array $data */
            $data = $form->getData();
            $orderReturn->setOrderReturnConsents((array) $data['consents']);

            $stateMachine = $this->stateMachineFactory->get($orderReturn, OrderReturnInterface::GRAPH);
            if (!$stateMachine->can(OrderReturnInterface::TRANSITION_REQUEST_WITHDRAWAL)) {
                return $this->errorRedirect($request, 'madcoders_rma.ui.withdrawal.error.not_cancellable', ['%orderNumber%' => $orderReturn->getOrderNumber()]);
            }

            // Records the customer's item selection and fires the withdrawal-requested notifier
            // (changelog + e-mail). The Sylius order is left untouched; the admin resolves it.
            $stateMachine->apply(OrderReturnInterface::TRANSITION_REQUEST_WITHDRAWAL);

            $this->orderReturnRepository->add($orderReturn);

            return new RedirectResponse($this->router->generate('madcoders_rma_withdrawal_success', ['returnNumber' => $returnNumber]));
        }

        return new Response($this->twig->render($template, [
            'orderNumber' => $orderReturn->getOrderNumber(),
            'returnOrder' => $orderReturn,
            'form' => $form->createView(),
            'formAction' => $this->router->generate('madcoders_rma_withdrawal_form_accept', ['returnNumber' => $returnNumber]),
            'editAction' => $this->router->generate('madcoders_rma_withdrawal', ['orderNumber' => $orderReturn->getOrderNumber()]),
        ]));
    }

    public function successIndex(Request $request, string $returnNumber, string $template): Response
    {
        $orderReturn = $this->orderReturnRepository->findOneBy(['returnNumber' => $returnNumber]);
        if (!$orderReturn instanceof OrderReturnInterface) {
            return $this->errorRedirect($request, 'madcoders_rma.ui.first_step.error.order_number_not_valid', ['%orderNumber%' => $returnNumber]);
        }

        // Return numbers are predictable (RMA-{orderNumber}-{n}), so this page must not be readable
        // by anyone who guesses the number: gate it on the same order-return authorization as the
        // rest of the withdrawal flow (see security issue #27).
        $order = $this->orderByNumberProvider->findOneByNumber($orderReturn->getOrderNumber());
        if (!$order instanceof OrderInterface) {
            return $this->errorRedirect($request, 'madcoders_rma.ui.first_step.error.order_number_not_valid', ['%orderNumber%' => $orderReturn->getOrderNumber()]);
        }

        if (!$this->authorizationChecker->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order)) {
            return $this->errorRedirect($request, 'madcoders_rma.ui.return.user_not_privileges_to_this_order');
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
