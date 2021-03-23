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
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLog;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnNotesType;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

final class AdminManagementController extends AbstractController
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

    /** @var RepositoryInterface */
    private $orderReturnRepository;

    /** @var RepositoryInterface */
    private $changeLogRepository;

    /**
     * AdminManagementController constructor.
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface|Environment $templatingEngine
     * @param ChannelContextInterface $channelContext
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param RepositoryInterface $orderReturnRepository
     * @param RepositoryInterface $changeLogRepository
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        $templatingEngine,
        ChannelContextInterface $channelContext,
        RouterInterface $router,
        SessionInterface $session,
        RepositoryInterface $orderReturnRepository,
        RepositoryInterface $changeLogRepository
    )
    {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->channelContext = $channelContext;
        $this->router = $router;
        $this->session = $session;
        $this->orderReturnRepository = $orderReturnRepository;
        $this->changeLogRepository = $changeLogRepository;
    }

    public function viewIndex(Request $request, string $template): Response
    {
        $orderReturnId = $request->attributes->get('id');

        if (!$orderReturn = $this->getDoctrine()->getRepository(OrderReturn::class)
            ->findOneBy(array('id' => $orderReturnId))) {
            return new RedirectResponse($this->router->generate('madcoders_rma_admin_order_return_index'));
        }

        $formType = $this->getSyliusAttribute($request, 'form', ReturnNotesType::class);
        $form = $this->formFactory->create($formType);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $newChangeLog = $form->getData();

            if (!$newChangeLog instanceof OrderReturnChangeLog){

                return new RedirectResponse($this->router
                    ->generate('madcoders_rma_admin_order_return_show', ['id' => $orderReturnId]));
            }

            if (!$note = $newChangeLog->getNote()) {

                return new RedirectResponse($this->router
                    ->generate('madcoders_rma_admin_order_return_show', ['id' => $orderReturnId]));
            }

            if (!$returnNumber = (string) $orderReturn->getReturnNumber()){

                return new RedirectResponse($this->router
                    ->generate('madcoders_rma_admin_order_return_show', ['id' => $orderReturnId]));
            };

            $newChangeLog = new OrderReturnChangeLog();
            $newChangeLog->setReturnNumber($returnNumber);
            $newChangeLog->setType('Manager Comments');
            $newChangeLog->setNote($note);
            $this->changeLogRepository->add($newChangeLog);

            return new RedirectResponse($this->router->generate('madcoders_rma_admin_order_return_show', ['id' => $orderReturnId]));
        }

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine
            ->render($templateWithAttribute, ['order_return' => $orderReturn,'form' => $form->createView()]));
    }

    private function getSyliusAttribute(Request $request, string $attributeName, ?string $default): ?string
    {
        $attributes = $request->attributes->get('_sylius');

        return $attributes[$attributeName] ?? $default;
    }
}
