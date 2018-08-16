<?php
namespace Hillrange\Security\Extension;

use Hillrange\Security\Util\FailureManager;
use Hillrange\Security\Util\ParameterInjector;
use Twig\Extension\AbstractExtension;

class SecurityExtension extends AbstractExtension
{
	/**
	 * @var array
	 */
	private $routes;

    /**
     * @var FailureManager
     */
	private $failureManager;

	/**
	 * SecurityExtension constructor.
	 *
	 * @param ParameterInjector $parameterInjector
	 */
	public function __construct(ParameterInjector $parameterInjector, FailureManager $failureManager)
	{
		$this->routes = $parameterInjector->getParameter('security.routes');
		$this->failureManager = $failureManager;
	}

    /**
     * getFunctions
     *
     * @return array|\Twig_Function[]
     */
	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('getSecurityRoute', [$this, 'getSecurityRoute']),
			new \Twig_SimpleFunction('isIPBlocked', [$this->failureManager, 'isIPBlocked']),
		];
	}

    /**
     * getSecurityRoute
     *
     * @param string $route
     * @return string
     */
	public function getSecurityRoute(string $route)
	{
		return trim($this->routes[$route]);
	}

    /**
     * getName
     *
     * @return string
     */
	public function getName()
	{
		return 'hillrange_security_extension';
	}
}