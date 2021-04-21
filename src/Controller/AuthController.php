<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Controller;

use Madcoders\SyliusRmaPlugin\Form\Type\ReturnAuthStartType;
use Madcoders\SyliusRmaPlugin\Entity\AuthCode;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnAuthVerificationType;
use Madcoders\SyliusRmaPlugin\Security\OrderReturnAuthorizerInterface;
use Madcoders\SyliusRmaPlugin\Security\Voter\OrderReturnVoter;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Madcoders\SyliusRmaPlugin\Email\AuthCodeEmailSenderInterface;

final class AuthController extends AbstractController
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface|Environment */
    private $templatingEngine;

    /** @var ChannelContextInterface */
    private $channelContext;

    /** @var RouterInterface */
    private $router;

    /** @var AuthCodeEmailSenderInterface */
    private $authCodeEmailSender;

    /** @var TranslatorInterface */
    private $translator;

    /** @var OrderReturnAuthorizerInterface */
    private $orderReturnAuthorizer;

    /** @var SessionInterface */
    private $session;

    public function __construct(
        FormFactoryInterface $formFactory,
        $templatingEngine,
        ChannelContextInterface $channelContext,
        RouterInterface $router,
        AuthCodeEmailSenderInterface $authCodeEmailSender,
        TranslatorInterface $translator,
        OrderReturnAuthorizerInterface $orderReturnAuthorizer,
        SessionInterface $session
    )
    {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->channelContext = $channelContext;
        $this->router = $router;
        $this->authCodeEmailSender = $authCodeEmailSender;
        $this->translator = $translator;
        $this->orderReturnAuthorizer = $orderReturnAuthorizer;
        $this->session = $session;
    }

    public function start(Request $request, string $template): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $formType = $this->getSyliusAttribute($request, 'form', ReturnAuthStartType::class);
        $redirectToOrderReturnRoute = $this->getSyliusAttribute($request, 'redirect_to_order_return', 'madcoders_rma_return_form');
        $form = $this->formFactory->create($formType);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            /** @var array $data */
            $data = $form->getData();
            $orderNumber = trim(str_replace(['#'], '', $data['orderNumber']));
            $order = $this->getDoctrine()
                ->getRepository(Order::class)
                ->findOneBy(array('number' => $orderNumber));

            if (!$order) {
                return $this->errorRedirect(
                    $request,
                    'madcoders_rma.ui.first_step.error.order_number_not_valid',
                    [ '%orderNumber%' => $orderNumber ]
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
            if ($this->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order)) {
                return new RedirectResponse($this->router->generate($redirectToOrderReturnRoute, [ 'orderNumber' => $order->getNumber() ]));
            }

            /** @var CustomerInterface $customer */
            $customer = $order->getCustomer();
            $customerEmail = $customer->getEmail();
            $authCode = mt_rand(100000, 999999);
            $hash = hash('sha256', $orderNumber.time());
            $startDate =  new \DateTime();
            $dateInterval = new \DateInterval('PT5M');

            $authCodeData = new AuthCode();
            $authCodeData->setOrderNumber($orderNumber);
            $authCodeData->setAuthCode($authCode);
            $authCodeData->setHash($hash);
            $authCodeData->setExpiresAt($startDate->add($dateInterval));

            $entityManager->persist($authCodeData);

            /** @var ChannelInterface $channelVariable */
            $channel = $this->channelContext->getChannel();

            $this->authCodeEmailSender->sendAuthCodeEmail($authCodeData, $channel, $hash, $customerEmail);

            $entityManager->flush();

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
                return new RedirectResponse($this->router->generate($redirectRoute, ['code' => $hash]));
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

        // TODO: inject repository instead
        $authData = $this->getDoctrine()
            ->getRepository(AuthCode::class)
            ->findOneBy(array('hash' => $code));

        if (!$authData) {
            $this->createNotFoundException(sprintf('Auth code %s has not been found', $code));
        }

        // TODO: inject repository instead
        // load order
        $order = $this->getDoctrine()
            ->getRepository(Order::class)
            ->findOneBy(array('orderNumber' => $authData->getOrderNumber()));

        // redirect forward if access is already granted
        if ($this->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order)) {
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
                $this->orderReturnAuthorizer->authorize($order);

                return new RedirectResponse($this->router->generate($redirectRoute, [ 'order' => $orderNumber ]));
            }

            $errorMessage = $this->getSyliusAttribute(
                $request,
                'error_flash',
                $this->translator->trans('madcoders_rma.ui.verification_step.error.code_not_valid')
            );

            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('error', $errorMessage);

            return new RedirectResponse($this->router->generate($redirectRoute, [ 'code' => $code]));
        }

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine->render($templateWithAttribute, ['code' => $code, 'form' => $form->createView()]));
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
