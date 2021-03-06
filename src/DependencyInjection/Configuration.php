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

namespace Madcoders\SyliusRmaPlugin\DependencyInjection;

use Madcoders\SyliusRmaPlugin\Controller\RmaConfigurationController;
use Madcoders\SyliusRmaPlugin\Entity\AuthCode;
use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLog;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthor;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthorInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsent;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentTranslation;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentTranslationInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItem;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItemInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReason;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonTranslation;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonTranslationInterface;
use Madcoders\SyliusRmaPlugin\Entity\RmaConfiguration;
use Madcoders\SyliusRmaPlugin\Entity\RmaConfigurationInterface;
use Madcoders\SyliusRmaPlugin\Form\Type\OrderReturnConsentFormType;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnReasonFormType;
use Madcoders\SyliusRmaPlugin\Repository\OrderReturnRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\Factory\Factory;
use Sylius\Component\Resource\Factory\TranslatableFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('madcoders_rma');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('authcode')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(AuthCode::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(AuthCodeInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_return')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(OrderReturn::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(OrderReturnInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(OrderReturnRepository::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_return_item')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(OrderReturnItem::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(OrderReturnItemInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_return_change_log')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(OrderReturnChangeLog::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(OrderReturnChangeLogInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_return_reason')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(OrderReturnReason::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(OrderReturnReasonInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(TranslatableFactory::class)->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(ReturnReasonFormType::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('translation')
                                ->addDefaultsIfNotSet()
                                    ->children()
                                    ->variableNode('options')->end()
                                    ->arrayNode('classes')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('model')->defaultValue(OrderReturnReasonTranslation::class)->cannotBeEmpty()->end()
                                            ->scalarNode('interface')->defaultValue(OrderReturnReasonTranslationInterface::class)->cannotBeEmpty()->end()
                                            ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                            ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                            ->scalarNode('repository')->cannotBeEmpty()->end()
                                        ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_return_consent')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                    ->scalarNode('model')->defaultValue(OrderReturnConsent::class)->cannotBeEmpty()->end()
                                    ->scalarNode('interface')->defaultValue(OrderReturnConsentInterface::class)->cannotBeEmpty()->end()
                                    ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                    ->scalarNode('factory')->defaultValue(TranslatableFactory::class)->end()
                                    ->scalarNode('repository')->cannotBeEmpty()->end()
                                    ->scalarNode('form')->defaultValue(OrderReturnConsentFormType::class)->cannotBeEmpty()->end()
                                ->end()
                                ->end()
                                ->arrayNode('translation')
                                ->addDefaultsIfNotSet()
                                    ->children()
                                    ->variableNode('options')->end()
                                    ->arrayNode('classes')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                        ->scalarNode('model')->defaultValue(OrderReturnConsentTranslation::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(OrderReturnConsentTranslationInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                    ->end()
                                    ->end()
                                ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('madcoders_rma_order_return_change_log_author')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                    ->scalarNode('model')->defaultValue(OrderReturnChangeLogAuthor::class)->cannotBeEmpty()->end()
                                    ->scalarNode('interface')->defaultValue(OrderReturnChangeLogAuthorInterface::class)->cannotBeEmpty()->end()
                                    ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                    ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                    ->scalarNode('repository')->cannotBeEmpty()->end()
                                ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('madcoders_rma_configuration')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(RmaConfiguration::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(RmaConfigurationInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(RmaConfigurationController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                ->end()
                                ->end()
                            ->end()
                        ->end()

                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
