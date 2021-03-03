<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('madcoders_rma');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
