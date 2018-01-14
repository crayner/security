<?php
namespace Hillrange\Security\Util;

use Psr\Container\ContainerInterface;

class ParameterInjector
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * ParameterInjector constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
 	}

	/**
	 * Get parameter
	 *
	 * @param   string $name
	 * @param   mixed  $default
	 *
	 * @return  mixed
	 */
	public function getParameter($name, $default = null)
	{
		if ($this->hasParameter($name))
			return $this->container->getParameter($name);

		if (false === strpos($name, '.'))
			return $default;

		$pName = explode('.', $name);

		$key = array_pop($pName);

		$name = implode('.', $pName);

		$value = $this->getParameter($name, $default);

		if (is_array($value) && isset($value[$key]))
			return $value[$key];

		throw new \InvalidArgumentException(sprintf('The value %s is not a valid array parameter.', $name));
	}

	/**
	 * Has parameter
	 *
	 * @param   string $name
	 * @param   mixed  $default
	 *
	 * @return  mixed
	 */
	public function hasParameter($name)
	{
		return $this->container->hasParameter($name);
	}
}