<?php

namespace Command;

class Remove extends \Core\Command
{
    public function run()
    {
        $res  = \Core\Bucket::delete(
            $this->s3, $this->getBucketName(), $this->getKey('force')
        );
        $name = $this->getKey('name');
        if ($res) {
            \Core\Mount::delete($name);
            echo "Mount point '{$name}' removed\n";
        } else {
            echo "Mount point '{$name}' not removed\n";
        }
    }
}
