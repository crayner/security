<?php
namespace Hillrange\Security\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class HillRangeSecurityExtension extends Extension
{
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config        = $this->processConfiguration($configuration, $configs);

		$locator = new FileLocator(__DIR__ . '/../Resources/config');
		$loader = new YamlFileLoader(
			$container,
			$locator
		);
		$loader->load('services.yaml');

		dump($container->getParameter('hillrange_security_groups'));
		dump($config);
	}
}