<?php

namespace Command;

class Help extends \Core\Command
{

    private $_commands
        = [
            'mount'    => [
                'short' => 'Maps a local drive to S3',
                'full'  => "The first parameter needs to be the path to the local directory that you want to mount,\nand the second parameter should be the name you want to give it."
            ],
            'list'     => [
                'short' => 'List mapped directories',
                'full'  => "Will show a list of currently mounted directories and their type."
            ],
            'remove'   => [
                'short' => 'Remove a local drive mapping',
                'full'  => "You should specify the mount point that you wish to remove as the first parameter.\nSpecify a second parameter of 'force' if you wish to force the removal of remaining files."
            ],
            'backup'   => [
                'short' => 'Synchronise files with S3',
                'full'  => "You should specify the mount point that you wish to synchronise with S3 as the first\nparameter."
            ],
            'restore'  => [
                'short' => 'Restore files to the mount point from S3',
                'full'  => "You should specify the mount point that you wish to restore from as the first parameter."
            ],
            'interval' => [
                'short' => 'Schedule backups at intervals',
                'full'  => "Specify the mount point name and then the interval between backups, either in minutes (30 - less than 60)\nor in hours (2h - less than 24). To stop scheduled backups, use the interval 'clear'"
            ],
        ];

    public function run()
    {
        $command = $this->getKey('command');
        if ('all' === $command || !isset($this->_commands[$command])) {
            echo "Commands available:\n\n";
            foreach ($this->_commands as $name => $help) {
                echo "\t$name - {$help['short']}\n";
            }
            echo "\nFor help with a specific command use 'help command'\n";
        } else {
            echo "Help for '{$command}':\n";
            echo $this->_commands[$command]['full'] . PHP_EOL;
        }
    }
}
