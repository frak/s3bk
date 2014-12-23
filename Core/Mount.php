<?php

namespace Core;

class Mount
{
    static private function _get()
    {
        $path   = dirname(dirname(__FILE__)) . '/conf/mounts';
        $mounts = array();
        if (file_exists($path)) {
            $mounts = unserialize(file_get_contents($path));
        }

        return $mounts;
    }

    static private function _put($mounts)
    {
        $path = dirname(dirname(__FILE__)) . '/conf/mounts';
        file_put_contents($path, serialize($mounts));
    }

    static public function add($name, $path, $type, $versioned)
    {
        $mount            = new \stdClass;
        $mount->name      = $name;
        $mount->path      = $path;
        $mount->type      = $type;
        $mount->versioned = $versioned;
        $mounts           = self::_get();
        $mounts[$name]    = $mount;
        self::_put($mounts);
    }

    static public function delete($name)
    {
        $mounts = self::_get();
        if (isset($mounts[$name])) {
            unset($mounts[$name]);
            self::_put($mounts);
        }
    }

    static public function get($name)
    {
        $mounts = self::_get();
        if (isset($mounts[$name])) {
            return $mounts[$name];
        } else {
            return false;
        }
    }

    static public function all()
    {
        return self::_get();
    }

    static public function exists($name)
    {
        $mounts = self::_get();

        return isset($mounts[$name]);
    }
}
