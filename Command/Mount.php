<?php

namespace Command;

class Mount extends \Core\Command
{
    public function run()
    {
        \Core\Bucket::create($this->_s3, $this->_getBucketName());
        $name = $this->_getKey('name');
        \Core\Mounts::add($name, $this->_getKey('path'), $this->_getKey('type'), $this->_getKey('versioned'));
        echo "Mount point '{$name}' created\n";
    }
}