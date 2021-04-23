<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Controller;

use Madcoders\SyliusRmaPlugin\Generator\OrderReturnFormPdfFileGeneratorInterface;
use Madcoders\SyliusRmaPlugin\Repository\OrderReturnRepository;
use Madcoders\SyliusRmaPlugin\Services\RmaVerificationPossibilityOfReturn;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class ShopManagementController extends AbstractController
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface|Environment */
    private $templatingEngine;

    /** @var ChannelContextInterface */
    private $channelContext;

    /** @var RouterInterface */
    private $router;

    /** @var SessionInterface */
    private $session;

    /** @var OrderReturnRepository */
    private $orderReturnRepository;

    /** @var RepositoryInterface */
    private $changeLogRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var OrderReturnFormPdfFileGeneratorInterface */
    private $orderReturnFormPdfFileGenerator;

    /** @var OrderRepository */
    private $orderRepository;

    /** @var TranslatorInterface */
    private $translator;

    /** @var RmaVerificationPossibilityOfReturn */
    private $verificationPossibilityOfReturn;

    /**
     * ShopManagementController constructor.
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface|Environment $templatingEngine
     * @param ChannelContextInterface $channelContext
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param OrderReturnRepository $orderReturnRepository
     * @param RepositoryInterface $changeLogRepository
     * @param TokenStorageInterface $tokenStorage
     * @param OrderReturnFormPdfFileGeneratorInterface $orderReturnFormPdfFileGenerator
     * @param OrderRepository $orderRepository
     * @param TranslatorInterface $translator
     * @param RmaVerificationPossibilityOfReturn $verificationPossibilityOfReturn
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        $templatingEngine,
        ChannelContextInterface $channelContext,
        RouterInterface $router,
        SessionInterface $session,
        OrderReturnRepository $orderReturnRepository,
        RepositoryInterface $changeLogRepository,
        TokenStorageInterface $tokenStorage,
        OrderReturnFormPdfFileGeneratorInterface $orderReturnFormPdfFileGenerator,
        OrderRepository $orderRepository,
        TranslatorInterface $translator,
        RmaVerificationPossibilityOfReturn $verificationPossibilityOfReturn
    )
    {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->channelContext = $channelContext;
        $this->router = $router;
        $this->session = $session;
        $this->orderReturnRepository = $orderReturnRepository;
        $this->changeLogRepository = $changeLogRepository;
        $this->tokenStorage = $tokenStorage;
        $this->orderReturnFormPdfFileGenerator = $orderReturnFormPdfFileGenerator;
        $this->orderRepository = $orderRepository;
        $this->translator = $translator;
        $this->verificationPossibilityOfReturn = $verificationPossibilityOfReturn;
    }

    /**
     * @param Request $request
     * @param string $orderNumber
     * @return Response
     * @throws \Exception
     */
    public function createAction(Request $request, string $orderNumber): Response
    {
        if (!$token = $this->tokenStorage->getToken()) {
            return $this->createMissingUserResponse($request);
        }

        /** @var ShopUserInterface $customer */
        if (!$user = $token->getUser()) {
            return $this->createMissingUserResponse($request);
        }

        /** @var CustomerInterface */
        if (!$customer = $user->getCustomer()) {
            return $this->createMissingUserResponse($request);
        }

        if (!$order = $this->orderRepository
            ->findOneByNumberAndCustomer($orderNumber, $customer)) {
            return $this->createMissingPrivilegesResponse($request);
        }

        if ($order->getState() !== OrderInterface::STATE_FULFILLED) {
            return $this->errorRedirect(
                $request,
                'madcoders_rma.ui.first_step.error.order_not_fullfiled_yet',
                [ '%orderNumber%' => $orderNumber ]
            );
        }

        if (!$possibleToReturn = $this->verificationPossibilityOfReturn->verificationForButtonRender($order)) {
            return $this->errorRedirect(
                $request,
                'madcoders_rma.ui.first_step.error.order_already_returned',
                [ '%orderNumber%' => $orderNumber ]
            );
        }

        $this->session->set('madcoders_rma_allowed_order', $orderNumber);

        return new RedirectResponse($this->router->generate('madcoders_rma_return_form', ['orderNumber' => $orderNumber]));
    }

    public function printAction(Request $request, string $returnNumber): Response
    {
        /** @var ShopUserInterface $customer */
        if (!$customer = $this->tokenStorage->getToken()->getUser()) {

            return $this->createMissingUserResponse($request);
        }

        if (!$customerEmail = $customer->getEmail()) {

            return $this->createMissingUserResponse($request);
        }

        if (!$orderReturn = $this->orderReturnRepository
            ->findOneByReturnNumberAndCustomerEmail($returnNumber, $customerEmail)) {

            return $this->createMissingPrivilegesResponse($request);
        };

        $orderReturnPdf = $this->orderReturnFormPdfFileGenerator->generate($orderReturn);

        $response = new Response($orderReturnPdf->content(), Response::HTTP_OK, ['Content-Type' => 'application/pdf']);
        $response->headers->add([
            'Content-Disposition' => $response->headers->makeDisposition('attachment', $orderReturnPdf->filename()),
        ]);

        return $response;
    }

    private function createMissingPrivilegesResponse(Request $request): RedirectResponse
    {
        $errorMessage = $this->getSyliusAttribute(
            $request,
            'error_flash',
            'madcoders_rma.ui.return.user_not_privileges_to_this_order'
        );

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $errorMessage);

        return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
    }

    private function createMissingUserResponse(Request $request): RedirectResponse
    {
        $errorMessage = $this->getSyliusAttribute(
            $request,
            'error_flash',
            'madcoders_rma.ui.return.user_not_login'
        );

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $errorMessage);

        return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
    }

    private function getSyliusAttribute(Request $request, string $attributeName, ?string $default): ?string
    {
        $attributes = $request->attributes->get('_sylius');

        return $attributes[$attributeName] ?? $default;
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
}
