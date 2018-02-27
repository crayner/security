<?php
namespace Hillrange\Security\Util;

use Composer\Script\Event;

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
        echo __DIR__;
        copy('routes.yaml','config/routes/hillrange_security.yaml');
    }
}