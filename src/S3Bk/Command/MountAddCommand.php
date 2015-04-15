<?php

namespace S3Bk\Command;

use S3Bk\Model\Mount;
use S3Bk\Service\Database;
use S3Bk\Type\StringableInterval;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * MountAddCommand.
 */
class MountAddCommand extends Command
{
    protected function configure()
    {
        $this->setName('mount:add')
            ->setDescription('Add a new mount point');
        $this->addArgument(
            'mount',
            InputArgument::REQUIRED,
            'The name of the mount'
        );
        $this->addArgument(
            'path',
            InputArgument::REQUIRED,
            'The path to the mount'
        );
        $this->addArgument(
            'interval',
            InputArgument::REQUIRED,
            'How often to backup the mount'
        );
        $this->setHelp(<<<HELP
<comment>Add a new S3 backup mount point</comment>

There are three arguments to this command:

mount:      The name of the mount, because of S3 bucket naming rules this must
            be lower case

path:       The path to the mount, this can point to a removable disk

interval:   How often backups should be performed, this is an ISO 8901 formatted
            interval string http://en.wikipedia.org/wiki/ISO_8601#Durations

This will add the mount and (if setup has been run) will synchronise the files
automatically. If not, you will need to run <comment>mount:backup</comment>
manually to push the files to S3.
HELP
);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mount    = $input->getArgument('mount');
        $path     = $input->getArgument('path');
        $interval = $input->getArgument('interval');

        if ((bool)preg_match('/[A-Z]/', $mount)) {
            $helper   = $this->getHelper('question');
            $newMount = strtolower($mount);
            $question = new ConfirmationQuestion(
                '<question>Your mount contains upper case letters and will be '.
                'converted to "'.$newMount.
                '". Do you want to continue?</question> ',
                false
            );
            if (!$helper->ask($input, $output, $question)) {
                return;
            } else {
                $mount = $newMount;
            }
        }

        $db     = new Database();
        $mounts = $db->fetchMounts();
        foreach ($mounts as $existing) {
            if ($existing->getName() === $mount) {
                $output->writeln('<error>'.$mount.' already exists</error>');

                return;
            }
        }

        if (!is_dir($path)) {
            $output->writeln('<error>'.$path.' is not a directory</error>');

            return;
        } else {
            $path = realpath($path);
        }

        try {
            new StringableInterval($interval);
        } catch (\Exception $e) {
            $output->writeln(
                '<error>'.$interval.' is not a parseable interval</error>'
            );

            return;
        }

        $mountPoint = Mount::createFromRow(
            compact('mount', 'path', 'interval')
        );
        $db->persistMount($mountPoint);

        $output->writeln('<info>Mount "'.$mount.'" was added</info>');
    }
}
