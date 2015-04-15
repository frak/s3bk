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
        $this->setHelp(<<<HELP
<comment>List defined mount points</comment>

Shows a list of mount points
HELP
);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db     = new Database();
        $mounts = $db->fetchMounts();
        if (count($mounts) === 0) {
            $output->writeln(
                '<comment>There are no mount points to list</comment>'
            );

            return;
        }

        $table = new Table($output);
        $table->setHeaders(['Name', 'Path', 'Backup Interval', 'Last Backup']);
        foreach ($mounts as $mount) {
            if (is_null($mount->getLastBackup())) {
                $lastBackup = 'not run';
            } else {
                $lastBackup = $mount->getLastBackup()->format('c');
            }
            $table->addRow(
                [
                    $mount->getName(),
                    $mount->getPath(),
                    (string)$mount->getInterval(),
                    $lastBackup,
                ]
            );
        }
        $table->render();
    }
}
