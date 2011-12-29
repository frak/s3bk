<?php

namespace Command;

class ListMounts extends \Core\Command
{
    public function run()
    {
        $mounts = $this->_getKey('mounts');
        $maxPath = $maxName = $maxType = 0;
        foreach($mounts as $name => $mount) {
            $len = strlen($name);
            if($len > $maxName) {
                $maxName = $len;
            }
            $len = strlen($mount['path']);
            if($len > $maxPath) {
                $maxPath = $len;
            }
            $len = strlen($mount['type']);
            if($len > $maxType) {
                $maxType = $len;
            }
        }
        $maxPath += 4;
        $maxName += 4;
        $maxType += 4;
        echo str_pad('Name:', $maxName) . str_pad('Path:', $maxPath) . str_pad('Type:', $maxType) . PHP_EOL;
        echo str_pad('', $maxName, '=') . str_pad('', $maxPath, '=') . str_pad('', $maxType, '=') . PHP_EOL;
        foreach($mounts as $name => $mount) {
            echo str_pad($name, $maxName) . str_pad($mount['path'], $maxPath) . str_pad($mount['type'], $maxType) . PHP_EOL;
        }
    }
}