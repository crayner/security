<?php
namespace Hillrange\Security\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class HillrangeSecurityExtension extends Extension
{
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config        = $this->processConfiguration($configuration, $configs);

		$locator = new FileLocator(__DIR__ . '/../Resources/config');
		$loader  = new YamlFileLoader(
			$container,
			$locator
		);
		$loader->load('services.yaml');

		$projectDir = $container->getParameterBag()->get('kernel.project_dir');

		$twigLoader = new \Twig_Loader_Filesystem( realpath($projectDir . '/vendor/hillrange/security/Resources/views/'));

		$twigLoader->addPath(realpath($projectDir . '/vendor/hillrange/security/Resources/views/'), 'HillrangeSecurity');
	}
}