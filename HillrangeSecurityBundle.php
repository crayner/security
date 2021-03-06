<?php
namespace Hillrange\Security;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HillrangeSecurityBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
        $path = 'config/routes';
        while (false === realpath($path))
            $path = '../' . $path;
        $dest = realpath($path) . '/hillrange_security.yaml';

        if (! file_exists($dest))
            throw new \Exception('You must copy the routes.yaml file in the bundle Resource/config directory to the app config/routes directory as hillrange_security.yaml.');

        parent::build($container);
    }
}
