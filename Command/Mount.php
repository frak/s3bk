<?php

namespace Command;

class Mount extends \Core\Command
{
    public function run()
    {
        \Core\Bucket::create($this->_s3, $this->_getBucketName());
        echo "Mount point '{$name}' created\n";
    }
}