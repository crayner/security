<?php
namespace Hillrange\Security\Util;

class VersionManager
{
    const VERSION = '0.0.03';

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return VersionManager::VERSION;
    }

    public static function copyRouteConfig($event)
    {
        $source = 'Resources/config/routes.yaml';
        $dest = 'config/routes/hillrange_security.yaml';
        if (! is_file($source))
            throw new \Exception('Did not find ' . $source);

        if (! is_file($dest))
            throw new \Exception('Did not find ' . $dest);

        copy($source,$dest);
    }
}