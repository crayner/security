<?php
namespace Hillrange\Security\DependencyInjection;

use Hillrange\Security\Listener\PageListener;
use Hillrange\Security\Util\PageManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * Class HillrangeSecurityExtension
 * @package Hillrange\Security\DependencyInjection
 */
class HillrangeSecurityExtension extends Extension
{
    /**
     * load
     *
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		$locator = new FileLocator(__DIR__ . '/../Resources/config');
		$loader  = new YamlFileLoader(
			$container,
			$locator
		);
		$loader->load('services.yaml');

        $container
            ->getDefinition(PageManager::class)
            ->addMethodCall('setKeepPageCount', [$config['keep_page_count']]);

        $container
            ->getDefinition(PageListener::class)
            ->addMethodCall('setKeepPageCount', [$config['keep_page_count']]);

        $securityConfigFile = $container->getParameterBag()->get('kernel.project_dir').'/config/packages/security.yaml';

		$config = Yaml::parse(file_get_contents($securityConfigFile));

		$container->setParameter('security.config', $config['security']);
	}
}