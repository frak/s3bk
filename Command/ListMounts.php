<?php

namespace Command;

class ListMounts extends \Core\Command
{
    public function run()
    {
        $mounts = \Core\Mount::all();
        if (count($mounts) > 0) {
            $maxPath = $maxName = $maxType = 0;
            foreach ($mounts as $name => $mount) {
                $len = strlen($name);
                if ($len > $maxName) {
                    $maxName = $len;
                }
                $len = strlen($mount->path);
                if ($len > $maxPath) {
                    $maxPath = $len;
                }
                $len = strlen($mount->type);
                if ($len > $maxType) {
                    $maxType = $len;
                }
            }
            $maxPath += 5;
            $maxName += 5;
            $maxType += 5;
            echo str_pad('Name:', $maxName) . str_pad('Path:', $maxPath)
                . str_pad('Type:', $maxType) . PHP_EOL;
            echo str_pad('', $maxName, '=') . str_pad('', $maxPath, '=')
                . str_pad('', $maxType, '=') . PHP_EOL;
            foreach ($mounts as $name => $mount) {
                echo str_pad($name, $maxName) . str_pad($mount->path, $maxPath)
                    . str_pad($mount->type, $maxType) . PHP_EOL;
            }
        } else {
            echo "No mounts defined!" . PHP_EOL;
        }
    }
}
