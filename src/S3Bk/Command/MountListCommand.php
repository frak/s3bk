<?php

namespace S3Bk\Command;

use S3Bk\Service\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MountListCommand.
 */
class MountListCommand extends Command
{
    protected function configure()
    {
        $this->setName('mount:list')
            ->setDescription('Lists the available mount points');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = new Database();
        $mounts = $db->fetchMounts();
        if (count($mounts) === 0) {
            $output->writeln(
                '<comment>There are no mount points to list</comment>'
            );

            return;
        }

        $table = new Table($output);
        $table->setHeaders(['Name', 'Path', 'Backup Interval']);
        foreach ($mounts as $mount) {
            $table->addRow(
                [
                    $mount->getName(),
                    $mount->getPath(),
                    (string)$mount->getInterval()
                ]
            );
        }
        $table->render();
    }
}
