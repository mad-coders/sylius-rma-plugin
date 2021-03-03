<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\DependencyInjection;

use Madcoders\SyliusRmaPlugin\Entity\AuthCode;
use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\Factory\Factory;
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
//                                        ->scalarNode('repository')->defaultValue(AuthCodeRepository::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
//                        ->arrayNode('order_returm')
//                            ->addDefaultsIfNotSet()
//                            ->children()
//                                ->variableNode('options')->end()
//                                ->arrayNode('classes')
//                                    ->addDefaultsIfNotSet()
//                                    ->children()
//                                        ->scalarNode('model')->defaultValue(AuthCode::class)->cannotBeEmpty()->end()
//                                        ->scalarNode('interface')->defaultValue(AuthCodeInterface::class)->cannotBeEmpty()->end()
//                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
//                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
//                                        ->scalarNode('repository')->defaultValue(AuthCodeRepository::class)->cannotBeEmpty()->end()
//                                    ->end()
//                                ->end()
//                            ->end()
//                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
