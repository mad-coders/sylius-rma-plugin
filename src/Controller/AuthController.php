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

use Exception;
use Madcoders\SyliusRmaPlugin\Email\AuthCodeEmailSenderInterface;
use Madcoders\SyliusRmaPlugin\Entity\AuthCode;
use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnAuthStartType;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnAuthVerificationType;
use Madcoders\SyliusRmaPlugin\Provider\OrderByNumberProviderInterface;
use Madcoders\SyliusRmaPlugin\Security\OrderReturnAuthorizerInterface;
use Madcoders\SyliusRmaPlugin\Security\Voter\OrderReturnVoter;
use Madcoders\SyliusRmaPlugin\Services\AuthCode\AuthCodeFactoryInterface;
use Madcoders\SyliusRmaPlugin\Services\AuthCode\AuthThrottlerInterface;
use Madcoders\SyliusRmaPlugin\Services\ReturnEligibilityCheckerInterface;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\WithdrawalEligibilityCheckerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Webmozart\Assert\Assert;

final readonly class AuthController
{
    /**
     * @param RepositoryInterface<AuthCodeInterface> $authCodeRepository
     */
    public function __construct(
        private FormFactoryInterface $formFactory,
        private Environment $templatingEngine,
        private RouterInterface $router,
        private AuthCodeEmailSenderInterface $authCodeEmailSender,
        private TranslatorInterface $translator,
        private OrderReturnAuthorizerInterface $orderReturnAuthorizer,
        private OrderByNumberProviderInterface $orderByNumberProvider,
        private AuthCodeFactoryInterface $authCodeFactory,
        private AuthorizationCheckerInterface $authorizationChecker,
        private RepositoryInterface $authCodeRepository,
        private WithdrawalEligibilityCheckerInterface $withdrawalEligibilityChecker,
        private ReturnEligibilityCheckerInterface $returnEligibilityChecker,
        private AuthThrottlerInterface $authThrottler,
    ) {
    }

    public function start(Request $request, string $template): Response
    {
        $formType = $this->getSyliusAttribute($request, 'form', ReturnAuthStartType::class);
        $redirectToOrderReturnRoute = $this->getSyliusAttribute($request, 'redirect_to_order_return', 'madcoders_rma_return_form');
        $form = $this->formFactory->create($formType);

        if ($request->isMethod(Request::METHOD_POST) && $form->handleRequest($request)->isValid()) {
            /** @var array $data */
            $data = $form->getData();
            Assert::string($data['orderNumber']);
            $orderNumber = $data['orderNumber'];

            $order = $this->orderByNumberProvider->findOneByNumber($orderNumber);
            if (null === $order) {
                return $this->errorRedirect(
                    $request,
                    'madcoders_rma.ui.first_step.error.order_number_not_valid',
                    ['%orderNumber%' => $orderNumber],
                );
            }

            // A pre-shipment order that qualifies for withdrawal is routed to the withdrawal flow
            // instead of being rejected as "not fulfilled yet".
            $isWithdrawable = $this->withdrawalEligibilityChecker->isWithdrawable($order);

            if (!$isWithdrawable && !$this->returnEligibilityChecker->isReturnable($order)) {
                return $this->errorRedirect(
                    $request,
                    'madcoders_rma.ui.first_step.error.order_not_fullfiled_yet',
                    ['%orderNumber%' => $orderNumber],
                );
            }

            // redirect forward if access is already granted
            if ($this->authorizationChecker->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order)) {
                $forwardRoute = $isWithdrawable
                    ? $this->getSyliusAttribute($request, 'redirect_to_withdrawal', 'madcoders_rma_withdrawal')
                    : $redirectToOrderReturnRoute;

                return new RedirectResponse($this->router->generate($forwardRoute, ['orderNumber' => str_replace('#', '', (string) $order->getNumber())]));
            }

            // Rate limit code requests per client IP + order so an attacker cannot mint an endless
            // stream of fresh codes (each also e-mails the real customer) to keep brute forcing the
            // verification step (security issue #26).
            $retryAfter = $this->authThrottler->throttle('rma_start_' . ((string) $request->getClientIp()) . '_' . $orderNumber);
            if (null !== $retryAfter) {
                return $this->tooManyRequestsResponse($request, $template, ['form' => $form->createView()], $retryAfter);
            }

            $authCode = $this->authCodeFactory->createForOrder($order);
            $this->authCodeEmailSender->sendAuthCodeEmail($authCode, $order);

            $successMessage = $this->getSyliusAttribute(
                $request,
                'success_flash',
                $this->translator->trans('madcoders_rma.ui.first_step.success.message', ['%orderNumber%' => $orderNumber]),
            );

            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('success', $successMessage);

            $redirectRoute = $this->getSyliusAttribute($request, 'redirect', '');

            if ('' !== $redirectRoute) {
                return new RedirectResponse($this->router->generate($redirectRoute, ['code' => $authCode->getHash()]));
            }

            return $this->errorRedirect($request, 'madcoders_rma.ui.first_step.error.order_number_not_valid');
        }

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine->render($templateWithAttribute, ['form' => $form->createView()]));
    }

    private function errorRedirect(Request $request, string $errorMessage, array $context = []): RedirectResponse
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $this->translator->trans($errorMessage, $context));

        $redirectRoute = $this->getSyliusAttribute($request, 'error_redirect', '');
        if ('' !== $redirectRoute) {
            return new RedirectResponse($this->router->generate($redirectRoute));
        }

        return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
    }

    /**
     * Flashes a translated error and sends the customer back to the start of the flow. Used for the
     * terminal auth-code outcomes (expired code, lockout) after the code has been invalidated.
     */
    private function flashToStart(Request $request, string $messageKey): RedirectResponse
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $this->translator->trans($messageKey));

        return new RedirectResponse($this->router->generate('madcoders_rma_start'));
    }

    /**
     * Renders the current step with an HTTP 429 status and a Retry-After header when the request is
     * rate limited, so both humans and clients get a proper "too many requests" response.
     *
     * @param array<string, mixed> $context
     */
    private function tooManyRequestsResponse(Request $request, string $template, array $context, int $retryAfter): Response
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $this->translator->trans('madcoders_rma.ui.verification_step.error.too_many_requests'));

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response(
            $this->templatingEngine->render($templateWithAttribute, $context),
            Response::HTTP_TOO_MANY_REQUESTS,
            ['Retry-After' => (string) $retryAfter],
        );
    }

    /**
     * @throws Exception
     */
    public function verification(Request $request, string $template, string $code): Response
    {
        $redirectRoute = $this->getSyliusAttribute($request, 'redirect', '');
        $redirectErrorRoute = $this->getSyliusAttribute($request, 'error_redirect', '');

        if ('' === $redirectRoute) {
            throw new \InvalidArgumentException('$redirectRoute has not been configured properly');
        }

        if ('' === $redirectErrorRoute) {
            throw new \InvalidArgumentException('$redirectErrorRoute has not been configured properly');
        }

        $authData = $this->authCodeRepository->findOneBy(['hash' => $code]);
        if (!$authData instanceof AuthCodeInterface) {
            throw new NotFoundHttpException(sprintf('Auth code %s has not been found', $code));
        }

        // Expired code: invalidate it (so its hash cannot be replayed) and send the customer back.
        if ($authData->getExpiresAt() < (new \DateTime())) {
            $this->authCodeRepository->remove($authData);

            return $this->flashToStart($request, 'madcoders_rma.ui.verification_step.error.code_expired');
        }

        // Too many attempts already recorded: hard stop *before* evaluating any further guess and
        // invalidate the code. This makes the attempts counter an actual lockout instead of a
        // cosmetic message, and prevents the hash from being reused (security issue #26).
        if ($authData->getAttempts() >= AuthCode::DEFAULT_MAX_ATTEMPTS) {
            $this->authCodeRepository->remove($authData);

            return $this->flashToStart($request, 'madcoders_rma.ui.verification_step.error.max_attempts_exceeded');
        }

        $order = $this->orderByNumberProvider->findOneByNumber($authData->getOrderNumber());
        if (!$order instanceof OrderInterface) {
            throw new NotFoundHttpException(sprintf('Order %s has not been found', $authData->getOrderNumber()));
        }

        // A withdrawable pre-shipment order continues to the withdrawal flow once authorized;
        // everything else proceeds to the post-shipment return form.
        $successRoute = $this->withdrawalEligibilityChecker->isWithdrawable($order)
            ? $this->getSyliusAttribute($request, 'redirect_to_withdrawal', 'madcoders_rma_withdrawal')
            : $redirectRoute;

        // redirect forward if access is already granted
        if ($this->authorizationChecker->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order)) {
            return new RedirectResponse($this->router->generate($successRoute, ['orderNumber' => str_replace('#', '', (string) $order->getNumber())]));
        }

        $formType = $this->getSyliusAttribute($request, 'form', ReturnAuthVerificationType::class);
        $form = $this->formFactory->create($formType);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $throttleKey = 'rma_verify_' . ((string) $request->getClientIp()) . '_' . $authData->getOrderNumber();

            // Rate limit verification attempts per client IP + order (security issue #26).
            $retryAfter = $this->authThrottler->throttle($throttleKey);
            if (null !== $retryAfter) {
                return $this->tooManyRequestsResponse($request, $template, ['code' => $code, 'form' => $form->createView()], $retryAfter);
            }

            /** @var array $data */
            $data = $form->getData();
            $authCode = $data['authCode'];
            Assert::integer($authCode);

            $orderNumber = $authData->getOrderNumber();
            $authDataCode = $authData->getAuthCode();

            // Constant-time comparison so the code cannot be recovered through response timing.
            if (hash_equals((string) $authDataCode, (string) $authCode)) {
                // Success path: authorize, consume the one-time code, and clear the throttle counter.
                $this->orderReturnAuthorizer->authorize($order);
                $this->authCodeRepository->remove($authData);
                $this->authThrottler->reset($throttleKey);

                return new RedirectResponse($this->router->generate($successRoute, ['orderNumber' => $orderNumber]));
            }

            // Wrong code: record the attempt.
            $authData->increaseNumberOfAttempts();

            // On reaching the limit, invalidate the code so it cannot be brute forced further.
            if ($authData->getAttempts() >= AuthCode::DEFAULT_MAX_ATTEMPTS) {
                $this->authCodeRepository->remove($authData);

                return $this->flashToStart($request, 'madcoders_rma.ui.verification_step.error.max_attempts_exceeded');
            }

            $this->authCodeRepository->add($authData);

            $errorMessage = $this->getSyliusAttribute(
                $request,
                'error_flash',
                $this->translator->trans(
                    'madcoders_rma.ui.verification_step.error.code_not_valid',
                    ['%max%' => AuthCode::DEFAULT_MAX_ATTEMPTS, '%attempts%' => $authData->getAttempts()],
                ),
            );

            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('error', $errorMessage);

            $errorRedirectRoute = $this->getSyliusAttribute($request, 'error_redirect', 'madcoders_rma_verification');
            if ('' === $errorRedirectRoute) {
                return new RedirectResponse($this->router->generate('madcoders_rma_start'));
            }

            return new RedirectResponse($this->router->generate($errorRedirectRoute, ['code' => $code]));
        }

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine->render($templateWithAttribute, [
            'code' => $code, 'form' => $form->createView(),
        ]));
    }

    /**
     * @return ($default is null ? string|null : string)
     */
    private function getSyliusAttribute(Request $request, string $attributeName, ?string $default): ?string
    {
        $attributes = $request->attributes->get('_sylius');

        if (!is_array($attributes)) {
            return $default;
        }

        if (!isset($attributes[$attributeName]) || !is_string($attributes[$attributeName])) {
            return $default;
        }

        if ('' === $attributes[$attributeName]) {
            return $default;
        }

        return $attributes[$attributeName];
    }
}
