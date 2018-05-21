<?php
namespace Hillrange\Security\Util;

use Psr\Container\ContainerInterface;

class ParameterInjector
{
	/**
	 * @var ContainerInterface
	 */
	private static $container;

	/**
	 * ParameterInjector constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		self::$container = $container;
 	}

	/**
	 * Get parameter
	 *
	 * @param   string $name
	 * @param   mixed  $default
	 *
	 * @return  mixed
	 */
	public static function getParameter($name, $default = null)
	{
		if (self::hasParameter($name))
			return self::$container->get($name);

		if (false === strpos($name, '.'))
			return $default;

		$pName = explode('.', $name);

		$key = array_pop($pName);

		$name = implode('.', $pName);

		$value = self::getParameter($name, $default);

		if (is_array($value) && isset($value[$key]))
			return $value[$key];

		return $default;
	}

	/**
	 * Has parameter
	 *
	 * @param   string $name
	 * @param   mixed  $default
	 *
	 * @return  bool
	 */
	public static function hasParameter($name): bool
	{
	    if (self::$container)
		    return self::$container->has($name);
	    return false;
	}
}