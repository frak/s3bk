<?php

namespace Core;

class Autoloader
{
    static public function load($className)
    {
        $baseDir = dirname(dirname(realpath(__FILE__))) . DIRECTORY_SEPARATOR;
        if ($className == 'AmazonS3') {
            require_once "$baseDir/Aws/sdk.class.php";
        }
        if ($className == 'Console_ProgressBar') {
            require_once 'Console/ProgressBar.php';
        }
        $classPath
            = implode(DIRECTORY_SEPARATOR, explode('\\', $className)) . '.php';
        if (file_exists("{$baseDir}{$classPath}")) {
            require_once "{$baseDir}{$classPath}";
        }
    }
}

spl_autoload_register(__NAMESPACE__ . '\Autoloader::load');

$confDir = __DIR__ . '/../conf';

if (!is_dir($confDir)) {
    mkdir($confDir);
}
