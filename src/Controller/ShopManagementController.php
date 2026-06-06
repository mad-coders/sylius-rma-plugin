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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Generator\OrderReturnFormPdfFileGeneratorInterface;
use Madcoders\SyliusRmaPlugin\Repository\OrderReturnRepository;
use Madcoders\SyliusRmaPlugin\Services\RmaVerificationPossibilityOfReturn;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ShopManagementController extends AbstractController
{
    /**
     * ShopManagementController constructor.
     */
    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly OrderReturnRepository $orderReturnRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly OrderReturnFormPdfFileGeneratorInterface $orderReturnFormPdfFileGenerator,
        private readonly OrderRepository $orderRepository,
        private readonly TranslatorInterface $translator,
        private readonly RmaVerificationPossibilityOfReturn $verificationPossibilityOfReturn,
        private readonly bool $returnFormPdfEnabled = false,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function createAction(Request $request, string $orderNumber): RedirectResponse
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return $this->createMissingUserResponse($request);
        }

        $user = $token->getUser();
        if (!$user instanceof ShopUserInterface) {
            return $this->createMissingUserResponse($request);
        }

        $customer = $user->getCustomer();
        if (!$customer instanceof CustomerInterface) {
            return $this->createMissingUserResponse($request);
        }

        $order = $this->orderRepository->findOneByNumberAndCustomer($orderNumber, $customer);
        if (!$order instanceof OrderInterface) {
            return $this->createMissingPrivilegesResponse($request);
        }

        if ($order->getState() !== OrderInterface::STATE_FULFILLED) {
            return $this->errorRedirect(
                $request,
                'madcoders_rma.ui.first_step.error.order_not_fullfiled_yet',
                ['%orderNumber%' => $orderNumber],
            );
        }

        if (!$possibleToReturn = $this->verificationPossibilityOfReturn->verificationForButtonRender($order)) {
            return $this->errorRedirect(
                $request,
                'madcoders_rma.ui.first_step.error.order_already_returned',
                ['%orderNumber%' => $orderNumber],
            );
        }

        $this->requestStack->getSession()->set('madcoders_rma_allowed_order', $orderNumber);

        return new RedirectResponse($this->router->generate('madcoders_rma_return_form', ['orderNumber' => str_replace('#', '', $orderNumber)]));
    }

    public function printAction(Request $request, string $returnNumber): Response
    {
        if (!$this->returnFormPdfEnabled) {
            return $this->errorRedirect($request, 'madcoders_rma.ui.return.pdf_disabled');
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return $this->createMissingUserResponse($request);
        }

        $customer = $token->getUser();
        if (!$customer instanceof ShopUserInterface) {
            return $this->createMissingUserResponse($request);
        }

        $customerEmail = $customer->getEmail();
        if (null === $customerEmail || '' === $customerEmail) {
            return $this->createMissingUserResponse($request);
        }

        $orderReturn = $this->orderReturnRepository->findOneByReturnNumberAndCustomerEmail($returnNumber, $customerEmail);
        if (!$orderReturn instanceof OrderReturnInterface) {
            return $this->createMissingPrivilegesResponse($request);
        }

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
            'madcoders_rma.ui.return.user_not_privileges_to_this_order',
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
            'madcoders_rma.ui.return.user_not_login',
        );

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $errorMessage);

        return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
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

    private function errorRedirect(Request $request, string $errorMessage, array $context = []): RedirectResponse
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $this->translator->trans($errorMessage, $context));

        $redirectRoute = $this->getSyliusAttribute($request, 'error_redirect', '');
        if ('' !== $redirectRoute) {
            return new RedirectResponse($this->router->generate($redirectRoute));
        }

        return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
    }
}
