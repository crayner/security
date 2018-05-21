<?php
namespace Hillrange\Security\Util;

class VersionManager
{
    const VERSION = '0.0.25';

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return VersionManager::VERSION;
    }
}