<?php
namespace Hillrange\Security\Util;

class VersionManager
{
    const VERSION = '0.0.12';

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return VersionManager::VERSION;
    }
}