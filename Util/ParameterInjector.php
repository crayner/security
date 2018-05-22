<?php
namespace Hillrange\Security\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ParameterInjector
{
    /**
     * @var ParameterBag
     */
    private static $parameters;

    /**
     * ParameterInjector constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        self::$parameters = $container->getParameterBag()->all();
    }

    /**
     * Get parameter
     *
     * @param   string $name
     * @param   mixed $default
     *
     * @return  mixed
     */
    public static function getParameter($name, $default = null)
    {
        if (self::hasParameter($name))
            return self::$parameters[$name];

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
     * @param   mixed $default
     *
     * @return  bool
     */
    public static function hasParameter($name): bool
    {
        if (isset(self::$parameters[$name]))
            return true;
        return false;
    }
}