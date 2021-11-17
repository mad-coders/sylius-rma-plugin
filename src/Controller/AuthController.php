<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Controller;

use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnAuthStartType;
use Madcoders\SyliusRmaPlugin\Entity\AuthCode;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnAuthVerificationType;
use Madcoders\SyliusRmaPlugin\Provider\OrderByNumberProviderInterface;
use Madcoders\SyliusRmaPlugin\Security\OrderReturnAuthorizerInterface;
use Madcoders\SyliusRmaPlugin\Security\Voter\OrderReturnVoter;
use Madcoders\SyliusRmaPlugin\Services\AuthCode\AuthCodeFactoryInterface;
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
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Madcoders\SyliusRmaPlugin\Email\AuthCodeEmailSenderInterface;
use Exception;

final class AuthController
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface|Environment */
    private $templatingEngine;

    /** @var RouterInterface */
    private $router;

    /** @var AuthCodeEmailSenderInterface */
    private $authCodeEmailSender;

    /** @var TranslatorInterface */
    private $translator;

    /** @var OrderReturnAuthorizerInterface */
    private $orderReturnAuthorizer;

    /** @var OrderByNumberProviderInterface */
    private $orderByNumberProvider;

    /** @var AuthCodeFactoryInterface  */
    private $authCodeFactory;

    /** @var AuthorizationCheckerInterface  */
    private $authorizationChecker;

    /** @var RepositoryInterface  */
    private $authCodeRepository;

    public function __construct(
        FormFactoryInterface $formFactory,
        Environment $templatingEngine,
        RouterInterface $router,
        AuthCodeEmailSenderInterface $authCodeEmailSender,
        TranslatorInterface $translator,
        OrderReturnAuthorizerInterface $orderReturnAuthorizer,
        OrderByNumberProviderInterface $orderByNumberProvider,
        AuthCodeFactoryInterface $authCodeFactory,
        AuthorizationCheckerInterface $authorizationChecker,
        RepositoryInterface $authCodeRepository
    )
    {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->router = $router;
        $this->authCodeEmailSender = $authCodeEmailSender;
        $this->translator = $translator;
        $this->orderReturnAuthorizer = $orderReturnAuthorizer;
        $this->orderByNumberProvider = $orderByNumberProvider;
        $this->authCodeFactory = $authCodeFactory;
        $this->authorizationChecker = $authorizationChecker;
        $this->authCodeRepository = $authCodeRepository;
    }

    public function start(Request $request, string $template): Response
    {
        $formType = $this->getSyliusAttribute($request, 'form', ReturnAuthStartType::class);
        $redirectToOrderReturnRoute = $this->getSyliusAttribute($request, 'redirect_to_order_return', 'madcoders_rma_return_form');
        $form = $this->formFactory->create($formType);

        if ($request->isMethod(Request::METHOD_POST) && $form->handleRequest($request)->isValid()) {

            /** @var array $data */
            $data = $form->getData();
            $orderNumber = (string)$data['orderNumber'];

            if (!$order = $this->orderByNumberProvider->findOneByNumber($orderNumber)) {
                return $this->errorRedirect(
                    $request,
                    'madcoders_rma.ui.first_step.error.order_number_not_valid',
                    ['%orderNumber%' => $orderNumber]
                );
            }

            if ($order->getState() !== OrderInterface::STATE_FULFILLED) {
                return $this->errorRedirect(
                    $request,
                    'madcoders_rma.ui.first_step.error.order_not_fullfiled_yet',
                    [ '%orderNumber%' => $orderNumber ]
                );
            }

            // redirect forward if access is already granted
            if ($this->authorizationChecker->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order)) {
                return new RedirectResponse($this->router->generate($redirectToOrderReturnRoute, [ 'orderNumber' => $order->getNumber() ]));
            }

            $authCode = $this->authCodeFactory->createForOrder($order);
            $this->authCodeEmailSender->sendAuthCodeEmail($authCode, $order);

            $successMessage = $this->getSyliusAttribute(
                $request,
                'success_flash',
                $this->translator->trans('madcoders_rma.ui.first_step.success.message', [ '%orderNumber%' => $orderNumber ])
            );

            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('success', $successMessage);

            $redirectRoute = $this->getSyliusAttribute($request, 'redirect', '');

            if ($redirectRoute) {
                return new RedirectResponse($this->router->generate($redirectRoute, ['code' => $authCode->getHash()]));
            }

            return $this->errorRedirect($request, 'madcoders_rma.ui.first_step.error.order_number_not_valid');
        }

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine->render($templateWithAttribute, ['form' => $form->createView()]));
    }

    private function errorRedirect(Request $request, string $errorMessage, array $context = []): Response
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $this->translator->trans($errorMessage, $context));

        $redirectRoute = $this->getSyliusAttribute($request, 'error_redirect', '');
        if ($redirectRoute) {
            return new RedirectResponse($this->router->generate($redirectRoute));
        }
        return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
    }

    /**
     * @param Request $request
     * @param string $template
     * @param string $code
     * @return Response
     * @throws Exception
     */
    public function verification(Request $request, string $template, string $code): Response
    {
        $redirectRoute = $this->getSyliusAttribute($request, 'redirect', '');
        $redirectErrorRoute = $this->getSyliusAttribute($request, 'error_redirect', '');

        if (!$redirectRoute) {
            throw new \InvalidArgumentException('$redirectRoute has not been configured properly');
        }

        if (!$redirectErrorRoute) {
            throw new \InvalidArgumentException('$redirectErrorRoute has not been configured properly');
        }

        $authData = $this->authCodeRepository->findOneBy(array('hash' => $code));
        if (!$authData instanceof AuthCodeInterface) {
            throw new NotFoundHttpException(sprintf('Auth code %s has not been found', $code));
        }

        // TODO: needs to be shorten
        if ($authData->getExpiresAt() < (new \DateTime())) {
            $errorMessage = $this->getSyliusAttribute(
                $request,
                'error_flash',
                $this->translator->trans('madcoders_rma.ui.verification_step.error.code_expired')
            );

            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('error', $errorMessage);

            return new RedirectResponse($this->router->generate('madcoders_rma_start'));
        }

        $order = $this->orderByNumberProvider->findOneByNumber($authData->getOrderNumber());

        // redirect forward if access is already granted
        if ($this->authorizationChecker->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order)) {
            return new RedirectResponse($this->router->generate($redirectRoute, [ 'orderNumber' => $order->getNumber() ]));
        }

        $formType = $this->getSyliusAttribute($request, 'form', ReturnAuthVerificationType::class);
        $form = $this->formFactory->create($formType);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $data = $form->getData();
            $authCode = $data['authCode'];

            $orderNumber = $authData->getOrderNumber();
            $authDataCode = $authData->getAuthCode();

            if ($authDataCode === $authCode) {
                // this is success path
                $this->orderReturnAuthorizer->authorize($order);

                return new RedirectResponse($this->router->generate($redirectRoute, [ 'orderNumber' => $orderNumber ]));
            }

            // this is error handling
            $authData->increaseNumberOfAttempts();
            $this->authCodeRepository->add($authData);

            if ($authData->getAttempts() >= AuthCode::DEFAULT_MAX_ATTEMPTS) {
                $errorMessage = $this->getSyliusAttribute(
                    $request,
                    'error_flash',
                    $this->translator->trans('madcoders_rma.ui.verification_step.error.max_attempts_exceeded')
                );

                /** @var FlashBagInterface $flashBag */
                $flashBag = $request->getSession()->getBag('flashes');
                $flashBag->add('error', $errorMessage);

                return new RedirectResponse($this->router->generate('madcoders_rma_start'));
            }

            $errorMessage = $this->getSyliusAttribute(
                $request,
                'error_flash',
                $this->translator->trans('madcoders_rma.ui.verification_step.error.code_not_valid',
                    [ '%max%' => AuthCode::DEFAULT_MAX_ATTEMPTS, '%attempts%' => $authData->getAttempts() ]
                )
            );

            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('error', $errorMessage);

            if (!$errorRedirectRoute = $this->getSyliusAttribute($request, 'error_redirect', 'madcoders_rma_verification')) {
                return new RedirectResponse($this->router->generate('madcoders_rma_start'));
            }

            return new RedirectResponse($this->router->generate($errorRedirectRoute, [ 'code' => $code]));
        }

        if (!$templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template)) {
            throw new Exception('Template not find');
        }

        return new Response($this->templatingEngine->render($templateWithAttribute, [
            'code' => $code, 'form' => $form->createView()
        ]));
    }

    private function getSyliusAttribute(Request $request, string $attributeName, ?string $default): ?string
    {
        $attributes = $request->attributes->get('_sylius');

        if (!is_array($attributes)) {
            return null;
        }

        if (!isset($attributes[$attributeName]) || !is_string($attributes[$attributeName])) {
            return $default;
        }

        if (empty($attributes[$attributeName])) {
            return $default;
        }

        return $attributes[$attributeName];
    }
}
