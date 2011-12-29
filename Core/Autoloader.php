<?php

namespace Core;

class Autoloader
{
    static public function load($className)
    {
        $baseDir = dirname(dirname(realpath(__FILE__)));
        $classPath = implode('/', explode('\\', $className)) . '.php';
        require_once "{$baseDir}/{$classPath}";
    }
}

spl_autoload_register(__NAMESPACE__ . '\Autoloader::load');