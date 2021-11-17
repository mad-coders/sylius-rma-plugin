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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLog;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthor;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnNotesType;
use Madcoders\SyliusRmaPlugin\Services\RmaChangesLogger;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
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

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var RmaChangesLogger */
    private $changesLogger;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * AdminManagementController constructor
     *
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface|Environment $templatingEngine
     * @param ChannelContextInterface $channelContext
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param RepositoryInterface $orderReturnRepository
     * @param RepositoryInterface $changeLogRepository
     * @param TokenStorageInterface $tokenStorage
     * @param RmaChangesLogger $changesLogger
     * @param TranslatorInterface $translator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        $templatingEngine,
        ChannelContextInterface $channelContext,
        RouterInterface $router,
        SessionInterface $session,
        RepositoryInterface $orderReturnRepository,
        RepositoryInterface $changeLogRepository,
        TokenStorageInterface $tokenStorage,
        RmaChangesLogger $changesLogger,
        TranslatorInterface $translator
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
        $this->changesLogger = $changesLogger;
        $this->translator = $translator;
    }

    public function viewIndex(Request $request, string $template): Response
    {
        $orderReturnId = $request->attributes->get('id');

        if (!$orderReturn = $this->getDoctrine()->getRepository(OrderReturn::class)
            ->findOneBy(array('id' => $orderReturnId))) {

            return new RedirectResponse($this->router->generate('madcoders_rma_admin_order_return_index'));
        }

        if (!$returnNumber = (string) $orderReturn->getReturnNumber()){

            return new RedirectResponse($this->router
                ->generate('madcoders_rma_admin_order_return_show', ['id' => $orderReturnId]));
        };

        $changeLog = $this->getDoctrine()->getRepository(OrderReturnChangeLog::class)
            ->findBy(array('returnNumber' => $returnNumber), array('createdAt' => 'DESC'));

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

            /** @var AdminUserInterface $user */
            $user = $this->tokenStorage->getToken()->getUser();

            $newChangeLogAuthor = new OrderReturnChangeLogAuthor();
            $newChangeLogAuthor->setType('admin');

            if ($userFirstName = $user->getFirstName()) {
                $newChangeLogAuthor->setFirstName($userFirstName);
            } else {
                $newChangeLogAuthor->setFirstName($user->getEmail());
            }

            if ( $userLastName = $user->getLastName()) {
                $newChangeLogAuthor->setLastName($userLastName);
            } else {
                $newChangeLogAuthor->setLastName('');
            }

            $this->changesLogger->add($returnNumber, 'added_note', $note, $newChangeLogAuthor);
            $this->successFlashBag($request);

            return new RedirectResponse($this->router->generate('madcoders_rma_admin_order_return_show', ['id' => $orderReturnId]));
        }

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);

        return new Response($this->templatingEngine
            ->render($templateWithAttribute, ['order_return' => $orderReturn, 'form' => $form->createView(), 'changeLog' => $changeLog ]));
    }

    private function successFlashBag(Request $request): void
    {
        $successMessage = $this->getSyliusAttribute(
            $request,
            'success_flash',
            $this->translator->trans('madcoders_rma.admin.flashes.success_added_note')
        );

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('success', $successMessage);
    }

    private function getSyliusAttribute(Request $request, string $attributeName, ?string $default): ?string
    {
        $attributes = $request->attributes->get('_sylius');

        return $attributes[$attributeName] ?? $default;
    }
}
