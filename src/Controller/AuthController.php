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
    private $authCodeEmailManager;

    /** @var SessionInterface */
    private $session;

    /**
     * AuthController constructor.
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface|Environment $templatingEngine
     * @param ChannelContextInterface $channelContext
     * @param RouterInterface $router
     * @param AuthCodeEmailSenderInterface $authCodeEmailManager
     * @param SessionInterface $session
     */
    public function __construct(FormFactoryInterface $formFactory, $templatingEngine, ChannelContextInterface $channelContext, RouterInterface $router, AuthCodeEmailSenderInterface $authCodeEmailManager, SessionInterface $session)
    {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->channelContext = $channelContext;
        $this->router = $router;
        $this->authCodeEmailManager = $authCodeEmailManager;
        $this->session = $session;
    }

    public function start(Request $request, string $template): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $formType = $this->getSyliusAttribute($request, 'form', ReturnAuthStartType::class);
        $form = $this->formFactory->create($formType);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $data = $form->getData();
            $orderNumber = $data['orderNumber'];
            $order = $this->getDoctrine()
                ->getRepository(Order::class)
                ->findOneBy(array('number' => $orderNumber));

            if (!$order) {
                return $this->errorRedirect($request, 'madcoders_rma.ui.return.order_number_not_valid');
            }

            if ($order->getState() !== OrderInterface::STATE_FULFILLED) {
                return $this->errorRedirect($request, 'madcoders_rma.ui.return.order_not_fullfiled_yet');
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

            $this->authCodeEmailManager->sendAuthCodeEmail($authCodeData, $channel, $hash, $customerEmail);

            $entityManager->flush();

            $successMessage = $this->getSyliusAttribute(
                $request,
                'success_flash',
                'madcoders_rma.order.sending_auth_code_success'
            );

            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('success', $successMessage);

            $redirectRoute = $this->getSyliusAttribute($request, 'redirect', '');

            if ($redirectRoute) {
                return new RedirectResponse($this->router->generate($redirectRoute, ['code' => $hash]));
            }

            return $this->errorRedirect($request, 'madcoders_rma.ui.return.order_number_not_valid');
        }

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine->render($templateWithAttribute, ['form' => $form->createView()]));
    }

    private function errorRedirect(Request $request, $errorMessage): Response
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $errorMessage);

        $redirectRoute = $this->getSyliusAttribute($request, 'error_redirect', '');
        if ($redirectRoute) {
            return new RedirectResponse($this->router->generate($redirectRoute));
        }
        return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
    }

    public function verification(Request $request, string $template, string $code): Response
    {
        $formType = $this->getSyliusAttribute($request, 'form', ReturnAuthVerificationType::class);
        $form = $this->formFactory->create($formType);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $data = $form->getData();
            $authCode = $data['authCode'];
            $authData = $this->getDoctrine()
                ->getRepository(AuthCode::class)
                ->findOneBy(array('hash' => $code));

            $orderNumber = $authData->getOrderNumber();
            $authDataCode = $authData->getAuthCode();

            if ($authDataCode === $authCode) {
                $redirectRoute = $this->getSyliusAttribute($request, 'redirect', '');

                if ($redirectRoute) {
                    $this->session->set('madcoders_rma_allowed_order', $orderNumber);
                    return new RedirectResponse($this->router->generate($redirectRoute));
                }

                return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
            }

            $errorMessage = $this->getSyliusAttribute(
                $request,
                'error_flash',
                'madcoders_rma.ui.return.code_not_valid'
            );

            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('error', $errorMessage);

            $redirectRoute = $this->getSyliusAttribute($request, 'error_redirect', '');
            $redirectCode = $this->getSyliusAttribute($request, 'code', '');

            if ($redirectRoute) {
                return new RedirectResponse($this->router->generate($redirectRoute, [ 'code' => $redirectCode]));
            }

            return new RedirectResponse($this->router->generate('sylius_shop_homepage'));        }

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine->render($templateWithAttribute, ['code' => $code, 'form' => $form->createView()]));
    }

    private function getSyliusAttribute(Request $request, string $attributeName, ?string $default): ?string
    {
        $attributes = $request->attributes->get('_sylius');

        return $attributes[$attributeName] ?? $default;
    }
}
