<?php

namespace S3Bk\Command;

use S3Bk\Model\Mount;
use S3Bk\Service\Database;
use S3Bk\Service\Schedule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RunCommand.
 */
class RunCommand extends Command
{
    protected function configure()
    {
        $this->setName('run')
            ->setDescription(
                'The worker command that triggers the scheduled backup'
            );
        $this->setHelp(<<<HELP
<comment>Backup any stale mounts</comment>
<error>You should probably run setup and have this taken care of automatically</error>

This command will run a backup for any defined mount point which is stale.
HELP
);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db       = new Database();
        $schedule = new Schedule($db);
        $mounts   = $schedule->getMountsToBackup();
        foreach ($mounts as $mount) {
            $this->executeCommand($mount, $output);
        }
    }

    /**
     * Runs the command for a mount point
     *
     * @param Mount           $mount
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function executeCommand(Mount $mount, OutputInterface $output)
    {
        $command = $this->getApplication()->find('mount:backup');
        $args    = [
            'command' => 'mount:backup',
            'name' => $mount->getName()
        ];
        $input   = new ArrayInput($args);
        $command->run($input, $output);
    }
}
