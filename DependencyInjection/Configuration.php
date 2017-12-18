<?php
namespace HillRange\Security\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('hill_range_security');

		$rootNode
			->children()
			->arrayNode('groups')->ignoreExtraKeys()->end()
			->arrayNode('roles')->ignoreExtraKeys()->end()
			->end()
		;

		return $treeBuilder;
	}
}