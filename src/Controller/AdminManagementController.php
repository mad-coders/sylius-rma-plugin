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
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class AdminManagementController extends AbstractController
{
    /**
     * AdminManagementController constructor
     *
     * @param Environment              $templatingEngine
     * @param RepositoryInterface<OrderReturn>         $orderReturnRepository
     * @param RepositoryInterface<OrderReturnChangeLog> $changeLogRepository
     */
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private $templatingEngine,
        private readonly RouterInterface $router,
        private readonly RepositoryInterface $orderReturnRepository,
        private readonly RepositoryInterface $changeLogRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RmaChangesLogger $changesLogger,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function viewIndex(Request $request, string $template): Response
    {
        $orderReturnId = $request->attributes->get('id');

        $orderReturn = $this->orderReturnRepository->findOneBy(['id' => $orderReturnId]);
        if (!$orderReturn instanceof OrderReturn) {
            return new RedirectResponse($this->router->generate('madcoders_rma_admin_order_return_index'));
        }

        $returnNumber = $orderReturn->getReturnNumber();
        if ('' === $returnNumber) {
            return new RedirectResponse($this->router
                ->generate('madcoders_rma_admin_order_return_show', ['id' => $orderReturnId]));
        }

        $changeLog = $this->changeLogRepository
            ->findBy(['returnNumber' => $returnNumber], ['createdAt' => 'DESC']);

        $formType = $this->getSyliusAttribute($request, 'form', ReturnNotesType::class);
        $form = $this->formFactory->create($formType);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $newChangeLog = $form->getData();

            if (!$newChangeLog instanceof OrderReturnChangeLog) {
                return new RedirectResponse($this->router
                    ->generate('madcoders_rma_admin_order_return_show', ['id' => $orderReturnId]));
            }

            $note = $newChangeLog->getNote();
            if ('' === $note) {
                return new RedirectResponse($this->router
                    ->generate('madcoders_rma_admin_order_return_show', ['id' => $orderReturnId]));
            }

            $token = $this->tokenStorage->getToken();
            if (null === $token) {
                return new RedirectResponse($this->router
                    ->generate('madcoders_rma_admin_order_return_show', ['id' => $orderReturnId]));
            }

            $user = $token->getUser();
            if (!$user instanceof AdminUserInterface) {
                return new RedirectResponse($this->router
                    ->generate('madcoders_rma_admin_order_return_show', ['id' => $orderReturnId]));
            }

            $newChangeLogAuthor = new OrderReturnChangeLogAuthor();
            $newChangeLogAuthor->setType('admin');

            $userFirstName = $user->getFirstName();
            if (null !== $userFirstName && '' !== $userFirstName) {
                $newChangeLogAuthor->setFirstName($userFirstName);
            } else {
                $newChangeLogAuthor->setFirstName($user->getEmail() ?? '');
            }

            $userLastName = $user->getLastName();
            if (null !== $userLastName && '' !== $userLastName) {
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
            ->render($templateWithAttribute, ['order_return' => $orderReturn, 'form' => $form->createView(), 'changeLog' => $changeLog]));
    }

    private function successFlashBag(Request $request): void
    {
        $successMessage = $this->getSyliusAttribute(
            $request,
            'success_flash',
            $this->translator->trans('madcoders_rma.admin.flashes.success_added_note'),
        );

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('success', $successMessage);
    }

    /**
     * @return ($default is null ? string|null : string)
     */
    private function getSyliusAttribute(Request $request, string $attributeName, ?string $default): ?string
    {
        $attributes = $request->attributes->get('_sylius');

        if (!is_array($attributes) || !isset($attributes[$attributeName]) || !is_string($attributes[$attributeName]) || '' === $attributes[$attributeName]) {
            return $default;
        }

        return $attributes[$attributeName];
    }
}
