<?php

namespace App\Config;

use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    public static function load(string $configFile): array
    {
        if (!file_exists($configFile)) {
            throw new \RuntimeException('Config file not found: ' . $configFile);
        }

        $config = Yaml::parseFile($configFile);

        if (!isset($config['copy']) || !isset($config['paste']) || !isset($config['reload'])) {
            throw new \InvalidArgumentException('Missing required endpoints configuration');
        }

        return $config;
    }
}
