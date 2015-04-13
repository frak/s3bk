<?php

namespace S3Bk\Service;

use S3Bk\Exception\ConfigFileDoesNotExistException;
use S3Bk\Exception\ConfigHasIncorrectPermissionsException;
use Symfony\Component\Yaml\Yaml;

/**
 * Configuration.
 */
class Configuration
{

    /** @var array Loaded configuration */
    private $config;

    public function __construct()
    {
        $configPath = $_SERVER['HOME'].'/.s3bk/config.yml';
        if (!file_exists($configPath)) {
            throw new ConfigFileDoesNotExistException(
                'Checked for file: '.$configPath
            );
        }
        $perms = decoct(fileperms($configPath) & 0777);
        if ($perms !== '600') {
            throw new ConfigHasIncorrectPermissionsException(
                'Expected 600 got: '.$perms
            );
        }
        $data = file_get_contents($configPath);
        $this->config = Yaml::parse($data);
    }

    /**
     * Get a configuration key
     *
     * @param $key
     *
     * @return null
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        return null;
    }
}
