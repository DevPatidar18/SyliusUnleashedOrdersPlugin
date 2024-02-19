<?php

declare(strict_types=1);

namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('forge_labs_uk_sylius_unleashed_orders_plugin');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
