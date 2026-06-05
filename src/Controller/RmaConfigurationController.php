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

use Exception;
use Madcoders\SyliusRmaPlugin\Entity\RmaConfiguration;
use Madcoders\SyliusRmaPlugin\Entity\RmaConfigurationInterface;
use Madcoders\SyliusRmaPlugin\Form\Type\ConfigAddressToChannelFormType;
use Madcoders\SyliusRmaPlugin\Form\Type\ConfigChannelSelectFormType;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class RmaConfigurationController extends AbstractController
{
    /**
     * RmaConfigurationController constructor.
     *
     * @param EngineInterface|Environment $templatingEngine
     */
    public function __construct(private $templatingEngine, private readonly RouterInterface $router, private readonly RepositoryInterface $channelsRepository, private readonly RepositoryInterface $configurationRepository, private readonly TranslatorInterface $translator)
    {
    }

    public function viewIndex(Request $request, string $template, ?string $channelId = null): Response
    {
        $channelFormType = $this->getSyliusAttribute($request, 'channelForm', ConfigChannelSelectFormType::class);
        if ('' === $channelFormType) {
            throw new Exception('Channel form not defined');
        }

        $addressFormTypeToSelectedChannel = $this->getSyliusAttribute($request, 'addressForm', ConfigAddressToChannelFormType::class);
        if ('' === $addressFormTypeToSelectedChannel) {
            throw new Exception('Address form not defined');
        }

        $channel = $this->getSelectedChannel($channelId);
        $addressByChannel = [];

        /** @var RmaConfigurationInterface|null $addressConfigByChannel */
        $addressConfigByChannel = $this->configurationRepository->findOneBy(['channel' => $channel, 'parameter' => 'address']);

        if ($addressConfigByChannel instanceof RmaConfigurationInterface) {
            $addressByChannel = json_decode((string) $addressConfigByChannel->getValue());
        }

        $addressFormToSelectedChannel = $this->createForm($addressFormTypeToSelectedChannel, $addressByChannel);
        $channelForm = $this->createForm($channelFormType, $channelId ? ['channelChoice' => $channelId] : null);

        $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);
        if ('' === $templateWithAttribute) {
            throw new Exception('Template not defined');
        }

        return new Response($this->templatingEngine
            ->render(
                $templateWithAttribute,
                [
                    'channelForm' => $channelForm->createView(),
                    'addressFormToSelectedChannel' => $addressFormToSelectedChannel->createView(),
                    'channel' => $channel,
            ],
            ));
    }

    public function changeChannel(Request $request): RedirectResponse
    {
        $channelFormType = $this->getSyliusAttribute($request, 'channelForm', ConfigChannelSelectFormType::class);
        if ('' === $channelFormType) {
            throw new Exception('Channel form not defined');
        }

        $redirectRoute = $this->getSyliusAttribute($request, 'redirect', 'madcoders_rma_admin_order_return_index');
        if ('' === $redirectRoute) {
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
            'madcoders_rma.admin.form.error.channel_not_exist',
        );
    }

    public function saveAddressToSelectedChannel(Request $request, string $channelId, string $template): Response
    {
        $addressFormTypeToSelectedChannel = $this->getSyliusAttribute($request, 'addressForm', ConfigAddressToChannelFormType::class);
        if ('' === $addressFormTypeToSelectedChannel) {
            throw new Exception('Address form not defined');
        }

        $channelFormType = $this->getSyliusAttribute($request, 'channelForm', ConfigChannelSelectFormType::class);
        if ('' === $channelFormType) {
            throw new Exception('Channel form not defined');
        }

        $redirectRoute = $this->getSyliusAttribute($request, 'redirect', 'madcoders_rma_admin_order_return_index');
        if ('' === $redirectRoute) {
            throw new Exception('Redirect url not defined');
        }

        $addressFormToSelectedChannel = $this->createForm($addressFormTypeToSelectedChannel);
        $channelForm = $this->createForm($channelFormType, $channelId ? ['channelChoice' => $channelId] : null);

        if ($request->isMethod('POST')) {
            $templateWithAttribute = $this->getSyliusAttribute($request, 'template', $template);
            if ('' === $templateWithAttribute) {
                throw new Exception('Template not defined');
            }

            $channel = $this->getSelectedChannel($channelId);

            if (!$addressFormToSelectedChannel->handleRequest($request)->isValid()) {
                return new Response($this->templatingEngine
                    ->render(
                        $templateWithAttribute,
                        [
                            'channelForm' => $channelForm->createView(),
                            'addressFormToSelectedChannel' => $addressFormToSelectedChannel->createView(),
                            'channel' => $channel,
                        ],
                    ));
            }

            /** @var array $data */
            $data = $addressFormToSelectedChannel->getData();
            if (!$data) {
                throw new Exception('Address form not have data');
            }

            /** @var RmaConfigurationInterface|null $addressConfigByChannel */
            $addressConfigByChannel = $this->configurationRepository->findOneBy([
                'channel' => $channel,
                'parameter' => 'address',
            ]);
            if ($addressConfigByChannel) {
                $addressConfigByChannel->setValue(json_encode($data));
            } else {
                $addressConfigByChannel = new RmaConfiguration();
                $addressConfigByChannel->setParameter('address');
                $addressConfigByChannel->setValue(json_encode($data));
                $addressConfigByChannel->setChannel($channel);
            }

            $this->configurationRepository->add($addressConfigByChannel);

            $this->addSuccessMessageAboutConfigurationChanged($request);

            return new RedirectResponse($this->router->generate($redirectRoute, ['channelId' => $channelId]));
        }

        return $this->errorRedirect(
            $request,
            'madcoders_rma.admin.form.error.form_not_save',
        );
    }

    private function errorRedirect(Request $request, string $errorMessage, array $context = []): RedirectResponse
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $this->translator->trans($errorMessage, $context));
        $redirectRoute = $this->getSyliusAttribute(
            $request,
            'error_redirect',
            'madcoders_rma_admin_order_return_config_edit',
        );

        if ('' !== $redirectRoute) {
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
        }

        return $this->getDefaultChannel();
    }

    private function getDefaultChannel(): ChannelInterface
    {
        $channel = $this->channelsRepository->findOneBy([]);
        if (!$channel instanceof ChannelInterface) {
            throw new \InvalidArgumentException(sprintf('Channel must implement %s', ChannelInterface::class));
        }

        return $channel;
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

    private function addSuccessMessageAboutConfigurationChanged(Request $request, array $context = []): void
    {
        $infoMessage = 'madcoders_rma.ui.success.configuration_updated';
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('success', $this->translator->trans($infoMessage, $context));
    }
}
