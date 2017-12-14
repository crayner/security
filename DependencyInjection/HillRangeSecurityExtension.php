<?php
namespace HillRange\Security\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\RouteCollection;

class HillRangeSecurityExtension extends Extension
{
	public function load(array $configs, ContainerBuilder $container)
	{
		$locator = new FileLocator(__DIR__ . '/../Resources/config');
		$loader = new YamlFileLoader(
			$container,
			$locator
		);
		$loader->load('services.yaml');
	}
}