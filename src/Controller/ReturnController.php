<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Controller;

use Madcoders\SyliusRmaPlugin\Email\ReturnFormEmailSenderInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthor;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsent;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnConsentFormType;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnFormType;
use Madcoders\SyliusRmaPlugin\Generator\OrderReturnFormPdfFileGeneratorInterface;
use Madcoders\SyliusRmaPlugin\Security\Voter\OrderReturnVoter;
use Madcoders\SyliusRmaPlugin\Services\ReturnRequestBuilder;
use Madcoders\SyliusRmaPlugin\Services\RmaChangesLogger;
use Madcoders\SyliusRmaPlugin\Services\RmaVerificationPossibilityOfReturn;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Resource\Repository\RepositoryInterface;
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
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Webmozart\Assert\Assert;

final class ReturnController extends AbstractController
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

    /** @var ReturnRequestBuilder */
    private $returnRequestBuilder;

    /** @var RepositoryInterface */
    private $orderReturnRepository;

    /** @var StateMachineFactoryInterface */
    private $stateMachineFactory;

    /** @var OrderReturnFormPdfFileGeneratorInterface */
    private $orderReturnFormPdfFileGenerator;

    /** @var ReturnFormEmailSenderInterface */
    private $orderReturnFormPdfEmailSender;

    /** @var RmaChangesLogger */
    private $changesLogger;

    /** @var RmaVerificationPossibilityOfReturn */
    private $verificationPossibilityOfReturn;

    /** @var OrderRepository */
    private $orderRepository;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * ReturnController constructor.
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface|Environment $templatingEngine
     * @param ChannelContextInterface $channelContext
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param ReturnRequestBuilder $returnRequestBuilder
     * @param RepositoryInterface $orderReturnRepository
     * @param StateMachineFactoryInterface $stateMachineFactory
     * @param OrderReturnFormPdfFileGeneratorInterface $orderReturnFormPdfFileGenerator
     * @param ReturnFormEmailSenderInterface $orderReturnFormPdfEmailSender
     * @param RmaChangesLogger $changesLogger
     * @param RmaVerificationPossibilityOfReturn $verificationPossibilityOfReturn
     * @param OrderRepository $orderRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        $templatingEngine,
        ChannelContextInterface $channelContext,
        RouterInterface $router, SessionInterface $session,
        ReturnRequestBuilder $returnRequestBuilder,
        RepositoryInterface $orderReturnRepository,
        StateMachineFactoryInterface $stateMachineFactory,
        OrderReturnFormPdfFileGeneratorInterface $orderReturnFormPdfFileGenerator,
        ReturnFormEmailSenderInterface $orderReturnFormPdfEmailSender,
        RmaChangesLogger $changesLogger,
        RmaVerificationPossibilityOfReturn $verificationPossibilityOfReturn,
        OrderRepository $orderRepository,
        TranslatorInterface $translator
    )
    {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->channelContext = $channelContext;
        $this->router = $router;
        $this->session = $session;
        $this->returnRequestBuilder = $returnRequestBuilder;
        $this->orderReturnRepository = $orderReturnRepository;
        $this->stateMachineFactory = $stateMachineFactory;
        $this->orderReturnFormPdfFileGenerator = $orderReturnFormPdfFileGenerator;
        $this->orderReturnFormPdfEmailSender = $orderReturnFormPdfEmailSender;
        $this->changesLogger = $changesLogger;
        $this->verificationPossibilityOfReturn = $verificationPossibilityOfReturn;
        $this->orderRepository = $orderRepository;
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     * @param string $orderNumber
     * @param string $template
     * @return Response
     * @throws \Exception
     */
    public function viewIndex(Request $request, string $orderNumber, string $template): Response
    {
        $formType = $this->getSyliusAttribute($request, 'form', ReturnFormType::class);

        // TODO: inject repository instead
        // load order

        $order = $this->orderRepository->findOneByNumber($orderNumber);

//        $order = $this->getDoctrine()
//            ->getRepository(Order::class)
//            ->findOneBy(array('number' => $orderNumber));

        // redirect forward if access is already granted
        if (!$this->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order)) {
            return $this->createMissingOrderNumberResponse($request);
        }

        if (!$possibleToReturn = $this->verificationPossibilityOfReturn->verificationForButtonRender($order)) {
            return $this->errorRedirect(
                $request,
                'madcoders_rma.ui.first_step.error.order_already_returned_or_cannot_be_returned',
                [ '%orderNumber%' => $orderNumber ]
            );
        }

        $orderReturn = $this->returnRequestBuilder->build($orderNumber);
        $returnNumber = $orderReturn->getReturnNumber();
        $form = $this->formFactory->create($formType, $orderReturn);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->orderReturnRepository->add($orderReturn);

            return new RedirectResponse($this->router->generate('madcoders_rma_return_form_accept', ['returnNumber' => $returnNumber ]));
        }
        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine->render($templateWithAttribute, ['orderNumber' => $orderNumber,'form' => $form->createView()]));
    }

    public function acceptIndex(Request $request, string $template): Response
    {
        $returnNumber = (string) $request->attributes->get('returnNumber');

        if (!$orderReturn = $this->getDoctrine()
            ->getRepository(OrderReturn::class)
            ->findOneBy(array('returnNumber' => $returnNumber))) {
            return $this->createMissingOrderNumberResponse($request);
        }

        // TODO: inject repository instead
        // load order
        $order = $this->getDoctrine()
            ->getRepository(Order::class)
            ->findOneBy(array('number' => $orderReturn->getOrderNumber()));

        // redirect forward if access is already granted
        if (!$this->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order)) {
            return $this->createMissingOrderNumberResponse($request);
        }

        $consentData = [ 'consents' => [] ];
        /** @var OrderReturnConsent $consent */
        foreach($this->getDoctrine()->getRepository(OrderReturnConsent::class)->findBy([ 'enabled' => true], ['position' => 'asc']) as $consent) {

            $consentData['consents'][] = [
                'code' => $consent->getCode(),
                'label' => $consent->getTranslation()->getName(),
            ];
        }

        $formType = $this->getSyliusAttribute($request, 'form', ReturnConsentFormType::class);
        $form = $this->formFactory->create($formType, $consentData);

        // Customer accepted returnOrder, orderOrder has new status
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            /** @var array $data */
            $data = $form->getData();
            $orderReturn->setOrderReturnConsents((array) $data['consents']);

            $orderReturnStateMachine = $this->stateMachineFactory->get($orderReturn, OrderReturnInterface::GRAPH);
            if (!$orderReturnStateMachine->can(OrderReturnInterface::STATUS_NEW)) {
                return $this->createInvalidStateResponse($request);
            }

            $orderReturnStateMachine->apply(OrderReturnInterface::STATUS_NEW);

            // TODO align entity instead
            if (!$userFirstName = $orderReturn->getFirstName()) {
                $userFirstName = 'no Name';
            }

            if (!$userLastName = $orderReturn->getLastName()) {
                $userLastName = 'no Last Name';
            }

            if (!$customerEmail = $orderReturn->getCustomerEmail()) {
                $customerEmail = 'no email address';
            }

            $newChangeLogAuthor = new OrderReturnChangeLogAuthor();
            $newChangeLogAuthor->setFirstName($userFirstName);
            $newChangeLogAuthor->setLastName($userLastName);
            $newChangeLogAuthor->setType('customer');

            $this->changesLogger->add($returnNumber, 'customer_accepted', '', $newChangeLogAuthor);

            $this->orderReturnRepository->add($orderReturn);

            /** @var ChannelInterface $channelVariable */
            $channel = $this->channelContext->getChannel();
            $this->orderReturnFormPdfEmailSender->sendReturnOrderFormEmail($orderReturn, $channel, $customerEmail);

            return new RedirectResponse($this->router->generate('madcoders_rma_return_form_success', ['returnNumber' => $returnNumber]));
        }

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine->render($templateWithAttribute, ['orderNumber' => $orderReturn->getOrderNumber(), 'returnOrder'=> $orderReturn, 'form' => $form->createView()]));
    }

    public function successIndex(Request $request, string $template): Response
    {
        $returnNumber = (string) $request->attributes->get('returnNumber');

        // TODO: inject repository instead
        if (!$orderReturn = $this->getDoctrine()
            ->getRepository(OrderReturn::class)
            ->findOneBy(array('returnNumber' => $returnNumber))) {
            return $this->createMissingOrderNumberResponse($request);
        }

        // TODO: inject repository instead
        // load order
        $order = $this->getDoctrine()
            ->getRepository(Order::class)
            ->findOneBy(array('number' => $orderReturn->getOrderNumber()));

        if (!$this->isGranted(OrderReturnVoter::ATTRIBUTE_RETURN, $order)) {
            return $this->createMissingOrderNumberResponse($request);
        }

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        //@TODO: Its used?
        $this->session->remove('madcoders_rma_allowed_order');
        $this->session->set('madcoders_rma_allowed_order_return', $returnNumber);

        return new Response($this->templatingEngine->render($templateWithAttribute, ['returnNumber' => $returnNumber]));
    }

    public function printIndex(Request $request): Response
    {
        if (!$returnNumber = (string) $this->session->get('madcoders_rma_allowed_order_return')) {
            return $this->createMissingOrderNumberResponse($request);
        }

        /** @var OrderReturnInterface|null $orderReturn */
        $orderReturn = $this->orderReturnRepository->findOneBy(array('returnNumber' => $returnNumber));
        Assert::notNull($orderReturn);

        $orderReturnPdf = $this->orderReturnFormPdfFileGenerator->generate($orderReturn);

        $response = new Response($orderReturnPdf->content(), Response::HTTP_OK, ['Content-Type' => 'application/pdf']);
        $response->headers->add([
            'Content-Disposition' => $response->headers->makeDisposition('attachment', $orderReturnPdf->filename()),
        ]);

        return $response;
    }

    private function createInvalidStateResponse(Request $request): RedirectResponse
    {
        $errorMessage = $this->getSyliusAttribute(
            $request,
            'error_flash',
            'madcoders_rma.ui.return.invalid_state'
        );

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $errorMessage);

        return new RedirectResponse($this->router->generate('madcoders_rma_start'));
    }

    private function createMissingOrderNumberResponse(Request $request): RedirectResponse
    {
        $errorMessage = $this->getSyliusAttribute(
            $request,
            'error_flash',
            'madcoders_rma.ui.return.session_not_valid'
        );

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $errorMessage);

        return new RedirectResponse($this->router->generate('madcoders_rma_start'));
    }

    private function getSyliusAttribute(Request $request, string $attributeName, ?string $default): ?string
    {
        $attributes = $request->attributes->get('_sylius');

        return $attributes[$attributeName] ?? $default;
    }

    private function errorRedirect(Request $request, string $errorMessage, array $context = [], string $code = null): Response
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $this->translator->trans($errorMessage, $context));

        $redirectRoute = $this->getSyliusAttribute($request, 'error_redirect', '');
        if ($redirectRoute) {
            return new RedirectResponse($this->router->generate($redirectRoute, [ 'code' => $code]));
        }

        return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
    }
}
