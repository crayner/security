<?php
namespace Hillrange\Security\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Hillrange\Security\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * getConfigTreeBuilder
     *
     * @return TreeBuilder
     */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$treeBuilder->root('hillrange_security')
            ->children()
            ->booleanNode('keep_page_count')->end()
        ;

        return $treeBuilder;
	}
}