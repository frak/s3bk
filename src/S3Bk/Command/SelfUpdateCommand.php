<?php

namespace S3Bk\Command;

use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SelfUpdateCommand.
 */
class SelfUpdateCommand extends Command
{
    protected function configure()
    {
        $this->setName('self-update')
            ->setDescription(
                'Checks and updates the command to the latest version'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = new Updater();
        $updater->setPharUrl(
            'https://github.com/frak/s3bk/blob/master/s3bk.phar'
        );
        $updater->setVersionUrl(
            'https://github.com/frak/s3bk/blob/master/s3bk.version'
        );
        $confDir = $_SERVER['HOME'].'/.s3bk/';
        if (is_dir($confDir)) {
            $updater->setBackupPath($confDir.'old_version.phar');
        }

        $res = $updater->update();
        if ($res) {
            $new = $updater->getNewVersion();
            $old = $updater->getOldVersion();
            $output->writeln('<info>Updated from '.$old.' to '.$new.'</info>');
        } else {
            $output->writeln('<comment>Alredy up to date</comment>');
        }

        return;
    }
}
