<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Controller;

use http\Exception\RuntimeException;
use Madcoders\SyliusRmaPlugin\Entity\RmaConfigurationInterface;
use Madcoders\SyliusRmaPlugin\Form\Type\ConfigAddressToChannelFormType;
use Madcoders\SyliusRmaPlugin\Form\Type\ConfigChannelSelectFormType;
use Madcoders\SyliusRmaPlugin\Services\RmaChangesLogger;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;
use Exception;

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
     */
    public function __construct(FormFactoryInterface $formFactory, $templatingEngine, RouterInterface $router, SessionInterface $session, TokenStorageInterface $tokenStorage, RmaChangesLogger $changesLogger, RepositoryInterface $channelsRepository, RepositoryInterface $configurationRepository)
    {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->router = $router;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->changesLogger = $changesLogger;
        $this->channelsRepository = $channelsRepository;
        $this->configurationRepository = $configurationRepository;
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
            $addressByChannel = $addressConfigByChannel->getValue();
        }

        $addressFormToSelectedChannel = $this->createForm($addressFormTypeToSelectedChannel, $addressByChannel);
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
