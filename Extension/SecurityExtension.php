<?php
namespace Hillrange\Security\Extension;

use Hillrange\Security\Util\ParameterInjector;
use Twig\Extension\AbstractExtension;

class SecurityExtension extends AbstractExtension
{
	/**
	 * @var array
	 */
	private $routes;

	/**
	 * SecurityExtension constructor.
	 *
	 * @param ParameterInjector $parameterInjector
	 */
	public function __construct(ParameterInjector $parameterInjector)
	{
		$this->routes = $parameterInjector->getParameter('security.routes');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('get_SecurityRoute', [$this, 'getSecurityRoute']),
		];
	}

	/**
	 * @param string $route
	 *
	 * @return string
	 */
	public function getSecurityRoute(string $route)
	{
		return trim($this->routes[$route]);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'hillrange_security_extension';
	}
}