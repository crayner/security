<?php
namespace Hillrange\Security\Extension;

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
	 * @param array $routes
	 */
	public function __construct(array $routes)
	{
		$this->routes = $routes;
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
		return $this->routes[$route];
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'hillrange_security_extension';
	}
}