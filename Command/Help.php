<?php

namespace Command;

class Help extends \Core\Command
{
    private $_commands = array(
        'mount' => array(
            'short' => 'Maps a local drive to S3',
            'full' => "\nThe first parameter needs to be the path to the local directory that you want to mount,\nand the second parameter should be the name you want to give it.\n"
        ),
        'list' => array(
            'short' => 'List mapped directories',
            'full' => "\nWill show a list of currently mounted directories and their type.\n"


        ),
        'remove' => array(
            'short' => 'Remove a local drive mapping',
            'full' => "\nYou should specify the mount point that you wish to remove as the first parameter.\nSpecify a second parameter of 'force' if you wish to force the removal of remaining files."
        ),
        'backup' => array(
            'short' => 'Synchronise files with S3',
            'full' => "\nYou should specify the mount point that you wish to synchronise with S3 as the first\nparameter.\n"
        ),
        'restore' => array(
            'short' => 'Restore files to the mount point from S3',
            'full' => "\nYou should specify the mount point that you wish to restore from as the first parameter.\n"
        ),
    );

    public function run()
    {
        $command = $this->_getKey('command');
        if ('all' === $command || !isset($this->_commands[$command])) {
            echo "Commands available:\n\n";
            foreach($this->_commands as $name => $help) {
                echo "\t$name - {$help['short']}\n";
            }
            echo "\nFor help with a specific command use 'help command'\n";
        } else {
            echo "Help for '{$command}':\n";
            echo $this->_commands[$command]['full'];
        }
    }
}