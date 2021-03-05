<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Controller;

use Madcoders\SyliusRmaPlugin\Form\Type\ReturnFormType;
use Madcoders\SyliusRmaPlugin\Services\ReturnRequestBuilder;
use Sylius\Component\Channel\Context\ChannelContextInterface;
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

    /**
     * ReturnController constructor.
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface|Environment $templatingEngine
     * @param ChannelContextInterface $channelContext
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param ReturnRequestBuilder $returnRequestBuilder
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        $templatingEngine,
        ChannelContextInterface $channelContext,
        RouterInterface $router,
        SessionInterface $session,
        ReturnRequestBuilder $returnRequestBuilder)
    {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->channelContext = $channelContext;
        $this->router = $router;
        $this->session = $session;
        $this->returnRequestBuilder = $returnRequestBuilder;
    }

    public function viewIndex(Request $request, string $template): Response
    {
        $formType = $this->getSyliusAttribute($request, 'form', ReturnFormType::class);
        if (!$orderNumber = (string) $this->session->get('madcoders_rma_allowed_order')) {
            return $this->createMissingOrderNumberResponse($request);
        }

        $orderReturnRequest = $this->returnRequestBuilder->build($orderNumber);
        $form = $this->formFactory->create($formType, $orderReturnRequest);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            var_dump($form->getData());
        }

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine->render($templateWithAttribute, ['orderNumber' => $orderNumber, 'form' => $form->createView()]));
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

        $redirectRoute = $this->getSyliusAttribute($request, 'error_redirect', 'referer');
        $redirectCode = $this->getSyliusAttribute($request, 'code', 'referer');

        if ($redirectRoute) {
            return new RedirectResponse($this->router->generate($redirectRoute, [ 'code' => $redirectCode]));
        }

        return new RedirectResponse($this->router->generate('madcoders_rma_start'));
    }

    private function getSyliusAttribute(Request $request, string $attributeName, ?string $default): ?string
    {
        $attributes = $request->attributes->get('_sylius');

        return $attributes[$attributeName] ?? $default;
    }
}
