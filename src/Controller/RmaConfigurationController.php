<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Controller;

use Madcoders\SyliusRmaPlugin\Entity\RmaConfiguration;
use Madcoders\SyliusRmaPlugin\Entity\RmaConfigurationInterface;
use Madcoders\SyliusRmaPlugin\Form\Type\ConfigAddressToChannelFormType;
use Madcoders\SyliusRmaPlugin\Form\Type\ConfigChannelSelectFormType;
use Madcoders\SyliusRmaPlugin\Services\RmaChangesLogger;
use Sylius\Component\Core\Model\ChannelInterface;
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
use Exception;

/**
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS Team (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */
final class RmaConfigurationController extends AbstractController
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface|Environment */
    private $templatingEngine;

    /** @var RouterInterface */
    private $router;

    /** @var SessionInterface */
    private $session;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var RmaChangesLogger */
    private $changesLogger;

    /** @var RepositoryInterface */
    private $channelsRepository;

    /** @var RepositoryInterface */
    private $configurationRepository;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * RmaConfigurationController constructor.
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface|Environment $templatingEngine
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param RmaChangesLogger $changesLogger
     * @param RepositoryInterface $channelsRepository
     * @param RepositoryInterface $configurationRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        $templatingEngine,
        RouterInterface $router,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        RmaChangesLogger $changesLogger,
        RepositoryInterface $channelsRepository,
        RepositoryInterface $configurationRepository,
        TranslatorInterface $translator
    )
    {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->router = $router;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->changesLogger = $changesLogger;
        $this->channelsRepository = $channelsRepository;
        $this->configurationRepository = $configurationRepository;
        $this->translator = $translator;
    }

    public function viewIndex(Request $request, string $template, ?string $channelId = null): Response
    {
        if (!$channelFormType = $this->getSyliusAttribute($request, 'channelForm', ConfigChannelSelectFormType::class)) {
            throw new Exception('Channel form not defined');
        }

        if (!$addressFormTypeToSelectedChannel = $this->getSyliusAttribute($request, 'addressForm', ConfigAddressToChannelFormType::class)) {
            throw new Exception('Address form not defined');
        }

        $channel = $this->getSelectedChannel($channelId);
        $addressByChannel = [];

        /** @var RmaConfigurationInterface|null $addressConfigByChannel */
        $addressConfigByChannel = $this->configurationRepository->findOneBy(['channel' => $channel, 'parameter' => 'address']);

        if ($addressConfigByChannel instanceof RmaConfigurationInterface) {
            $addressByChannel =  json_decode($addressConfigByChannel->getValue());
        }

        $addressFormToSelectedChannel = $this->createForm($addressFormTypeToSelectedChannel,$addressByChannel);
        $channelForm = $this->createForm($channelFormType, $channelId ? ['channelChoice' => $channelId ] : null);

        if (!$templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template)) {
            throw new Exception('Template not defined');
        }

        return new Response($this->templatingEngine
            ->render($templateWithAttribute, [
                    'channelForm' => $channelForm->createView(),
                    'addressFormToSelectedChannel' => $addressFormToSelectedChannel->createView(),
                    'channel' => $channel,
            ]
        ));
    }

    public function changeChannel(Request $request): Response
    {
        if (!$channelFormType = $this->getSyliusAttribute($request, 'channelForm', ConfigChannelSelectFormType::class)) {
            throw new Exception('Channel form not defined');
        }

        if (!$redirectRoute = $this->getSyliusAttribute($request, 'redirect', 'madcoders_rma_admin_order_return_index')) {
            throw new Exception('Redirect url not defined');
        }

        $channelForm = $this->createForm($channelFormType);
        if ($request->isMethod('POST') && $channelForm->handleRequest($request)->isValid()) {

            /** @var array $data */
            $data = $channelForm->getData();

            return new RedirectResponse($this->router->generate($redirectRoute, ['channelId' => $data['channelChoice']]));
        }

        return $this->errorRedirect(
            $request,
            'madcoders_rma.admin.form.error.channel_not_exist'
        );
    }

    public function saveAddressToSelectedChannel(Request $request, string $channelId, string $template): Response
    {
        if (!$addressFormTypeToSelectedChannel = $this->getSyliusAttribute($request, 'addressForm', ConfigAddressToChannelFormType::class)) {
            throw new Exception('Address form not defined');
        }

        if (!$channelFormType = $this->getSyliusAttribute($request, 'channelForm', ConfigChannelSelectFormType::class)) {
            throw new Exception('Channel form not defined');
        }

        if (!$redirectRoute = $this->getSyliusAttribute($request, 'redirect', 'madcoders_rma_admin_order_return_index')) {
            throw new Exception('Redirect url not defined');
        }

        $addressFormToSelectedChannel = $this->createForm($addressFormTypeToSelectedChannel);
        $channelForm = $this->createForm($channelFormType, $channelId ? ['channelChoice' => $channelId ] : null);

        if ($request->isMethod('POST')) {

            if (!$templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template)) {
                throw new Exception('Template not defined');
            }

            $channel = $this->getSelectedChannel($channelId);

            if (!$addressFormToSelectedChannel->handleRequest($request)->isValid()){
                return new Response($this->templatingEngine
                    ->render($templateWithAttribute, [
                            'channelForm' => $channelForm->createView(),
                            'addressFormToSelectedChannel' => $addressFormToSelectedChannel->createView(),
                            'channel' => $channel,
                        ]
                    ));
            }

            /** @var array $data */
            if (!$data = $addressFormToSelectedChannel->getData()) {
                throw new Exception('Address form not have data');
            }

            /** @var RmaConfigurationInterface|null $addressConfigByChannel */
            if ($addressConfigByChannel = $this->configurationRepository->findOneBy([
                'channel' => $channel,
                'parameter' => 'address'
            ])) {
                $addressConfigByChannel->setValue(json_encode($data));
            } else {
                $addressConfigByChannel = new RmaConfiguration();
                $addressConfigByChannel->setParameter('address');
                $addressConfigByChannel->setValue(json_encode($data));
                $addressConfigByChannel->setChannel($channel);
            }

            $this->configurationRepository->add($addressConfigByChannel);

            return new RedirectResponse($this->router->generate($redirectRoute, ['channelId' => $channelId]));
        }

        return $this->errorRedirect(
            $request,
            'madcoders_rma.admin.form.error.form_not_save'
        );
    }

    private function errorRedirect(Request $request, string $errorMessage, array $context = []): Response
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $this->translator->trans($errorMessage, $context));
        $redirectRoute = $this->getSyliusAttribute(
            $request,
            'error_redirect',
            'madcoders_rma_admin_order_return_config_edit');

        if ($redirectRoute) {
            return new RedirectResponse($this->router->generate($redirectRoute));
        }
        return new RedirectResponse($this->router->generate('sylius_admin_dashboard'));
    }

    private function getSelectedChannel(?string $channelId = null): ChannelInterface
    {
        if ($channelId) {
            $channel = $this->channelsRepository->findOneBy(['id' => $channelId]);
            if (!$channel instanceof ChannelInterface) {
                throw new \InvalidArgumentException(sprintf('Channel must implement %s', ChannelInterface::class));
            }

            return $channel;
        } else {
            return $this->getDefaultChannel();
        }
    }

    private function getDefaultChannel(): ChannelInterface
    {
        $channel = $this->channelsRepository->findOneBy([]);
        if (!$channel instanceof ChannelInterface) {
            throw new \InvalidArgumentException(sprintf('Channel must implement %s', ChannelInterface::class));
        }

        return $channel;
    }

    private function getSyliusAttribute(Request $request, string $attributeName, ?string $default): ?string
    {
        $attributes = $request->attributes->get('_sylius');

        return $attributes[$attributeName] ?? $default;
    }
}
