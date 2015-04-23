<?php

namespace S3Bk\Command;

use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RestoreCommand.
 */
class RestoreCommand extends Command
{
    protected function configure()
    {
        $this->setName('restore')
            ->setDescription('Restores the previous version of s3bk.phar');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = new Updater();
        $confDir = $_SERVER['HOME'].'/.s3bk/';
        if (is_dir($confDir)) {
            $updater->setRestorePath($confDir.'old_version.phar');
        }
        $res = $updater->rollback();
        if ($res) {
            $output->writeln('<info>Reverted successfully</info>');
        } else {
            $output->writeln('<comment>No version to revert to</comment>');
        }
    }
}
