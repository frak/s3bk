<?php

namespace Command;

class Remove extends \Core\Command
{
    public function run()
    {
        $res = \Core\Bucket::delete($this->_s3, $this->_getBucketName(), $this->_getKey('force'));
        $name = $this->_getKey('name');
        if($res) {
            \Core\Mounts::delete($name);
            echo "Mount point '{$name}' removed\n";
        } else {
            echo "Mount point '{$name}' not removed\n";
        }
    }
}