<?php
namespace Hillrange\Security\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class HillrangeSecurityExtension extends Extension
{
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$this->processConfiguration($configuration, $configs);

		$locator = new FileLocator(__DIR__ . '/../Resources/config');
		$loader  = new YamlFileLoader(
			$container,
			$locator
		);
		$loader->load('services.yaml');


		$securityConfigFile = $container->getParameterBag()->get('kernel.project_dir').'/config/packages/security.yaml';

		$config = Yaml::parse(file_get_contents($securityConfigFile));

		$container->setParameter('security.config', $config['security']);
	}
}