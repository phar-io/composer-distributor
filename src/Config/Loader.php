<?php

namespace PharIo\ComposerDistributor\Config;

class Loader
{
    /**
     * Config json example:
     *
     * "keyDirectory" and "signature" are optional
     *
     * {
     *   "packageName": "phar-io/phive",
     *   "keyDirectory: "keys",
     *   "phars": [
     *     {
     *       "name": "phive",
     *       "file:" "https://github.com/phar-io/phive/releases/%version%/phive.phar",
     *       "signature:" "https://github.com/phar-io/phive/releases/%version%/phive.phar.asc"
     *     }
     *   ]
     * }
     *
     */
    public static function loadFile(string $configFile): Config
    {
        if (!is_file($configFile)) {
            throw new \RuntimeException('Config file is missing');
        }
        $jsonData = json_decode(file_get_contents($configFile), true, 512, JSON_THROW_ON_ERROR);
        $mapper   = new Mapper();
        return $mapper->createConfig($jsonData);
    }
}
