<?php
namespace Hillrange\Security\Util;

class VersionManager
{
    const VERSION = '0.0.40';

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return VersionManager::VERSION;
    }
}