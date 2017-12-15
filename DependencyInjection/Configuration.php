<?php
namespace HillRange\Security\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('hillrange_security');
/*
		$rootNode
			->children()
				->arrayNode('groups')->end()
				->arrayNode('roles')->end()
		;
*/

		return $treeBuilder;
	}
}