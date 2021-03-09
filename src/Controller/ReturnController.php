<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Controller;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnConsentFormType;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnFormType;
use Madcoders\SyliusRmaPlugin\Services\ReturnRequestBuilder;
use Sylius\Component\Channel\Context\ChannelContextInterface;
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
use Twig\Environment;

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

    /** @var RepositoryInterface  */
    private $orderReturnRepository;

    /**
     * ReturnController constructor.
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface|Environment $templatingEngine
     * @param ChannelContextInterface $channelContext
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param RepositoryInterface $orderReturnRepository
     * @param ReturnRequestBuilder $returnRequestBuilder
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        $templatingEngine,
        ChannelContextInterface $channelContext,
        RouterInterface $router,
        SessionInterface $session,
        RepositoryInterface $orderReturnRepository,
        ReturnRequestBuilder $returnRequestBuilder)
    {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->channelContext = $channelContext;
        $this->router = $router;
        $this->session = $session;
        $this->returnRequestBuilder = $returnRequestBuilder;
        $this->orderReturnRepository = $orderReturnRepository;
    }

    public function viewIndex(Request $request, string $template): Response
    {
        $formType = $this->getSyliusAttribute($request, 'form', ReturnFormType::class, );
        if (!$orderNumber = (string) $this->session->get('madcoders_rma_allowed_order')) {
            return $this->createMissingOrderNumberResponse($request);
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
        if (!$orderNumber = (string) $this->session->get('madcoders_rma_allowed_order')) {
            return $this->createMissingOrderNumberResponse($request);
        }

        $returnOrder = $this->getDoctrine()
            ->getRepository(OrderReturn::class)
            ->findOneBy(array('returnNumber' => $request->attributes->get('returnNumber')));

        $formType = $this->getSyliusAttribute($request, 'form', ReturnConsentFormType::class, );
        $form = $this->formFactory->create($formType, $returnOrder);

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine->render($templateWithAttribute, ['orderNumber' => $orderNumber, 'returnOrder'=> $returnOrder, 'form' => $form->createView()]));
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
}
