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
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
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

    /**
     * AuthController constructor.
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface|Environment $templatingEngine
     * @param ChannelContextInterface $channelContext
     * @param RouterInterface $router
     * @param AuthCodeEmailSenderInterface $authCodeEmailManager
     */
    public function __construct(FormFactoryInterface $formFactory, $templatingEngine, ChannelContextInterface $channelContext, RouterInterface $router, AuthCodeEmailSenderInterface $authCodeEmailManager)
    {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->channelContext = $channelContext;
        $this->router = $router;
        $this->authCodeEmailManager = $authCodeEmailManager;
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
            if ($order) {
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
                $this->authCodeEmailManager->sendAuthCodeEmail($authCodeData, $customerEmail);

            }
            $entityManager->flush();

            $successMessage = $this->getSyliusAttribute(
                $request,
                'success_flash',
                'madcoders_rma.order.sending_auth_code_success'
            );

            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('success', $successMessage);

            $redirectRoute = $this->getSyliusAttribute($request, 'redirect', 'referer');

            return new RedirectResponse($this->router->generate($redirectRoute));

        }

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine->render($templateWithAttribute, ['form' => $form->createView()]));
    }

    private function getSyliusAttribute(Request $request, string $attributeName, ?string $default): ?string
    {
        $attributes = $request->attributes->get('_sylius');

        return $attributes[$attributeName] ?? $default;
    }
}