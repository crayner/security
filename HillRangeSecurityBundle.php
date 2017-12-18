<?php
namespace Hillrange\Security;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HillrangeSecurityBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
	}
}
