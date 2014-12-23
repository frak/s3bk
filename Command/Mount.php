<?php

namespace Command;

class Mount extends \Core\Command
{
    public function run()
    {
        \Core\Bucket::create($this->s3, $this->getBucketName());
        $name = $this->getKey('name');
        \Core\Mounts::add(
            $name, $this->getKey('path'), $this->getKey('type'),
            $this->getKey('versioned')
        );
        echo "Mount point '{$name}' created\n";
    }
}
