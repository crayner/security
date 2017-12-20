<?php
namespace Hillrange\Security;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HillrangeSecurityBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		dump($this->getName());
		dump($this->getNamespace());
		parent::build($container);
	}
}
