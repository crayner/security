<?php
namespace Hillrange\Security;

use Hillrange\CKEditor\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HillrangeSecurityBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);

		$dest = realpath('../config/routes/') . '/hillrange_security.yaml';

		if (! file_exists($dest))
		    throw new Exception('You must copy the routes.yaml file in the bundle Resource/config directory to the app config/routes directory as hillrange_security.yaml.');
    }
}
