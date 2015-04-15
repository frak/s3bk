<?php

namespace S3Bk\Command;

use Aws\S3\S3Client;
use S3Bk\Exception\BucketDoesntExistException;
use S3Bk\Exception\MountPointDoesntExistException;
use S3Bk\Service\Configuration;
use S3Bk\Service\Database;
use S3Bk\Service\S3Bucket;
use S3Bk\Traits\CheckMountPointTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MountRestoreCommand.
 */
class MountRestoreCommand extends Command
{
    use CheckMountPointTrait;

    protected function configure()
    {
        $this->setName('mount:restore')
            ->setDescription('Restores a mount from an S3 bucket');
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The name of the mount point to restore'
        );
        $this->addOption(
            'force-path',
            null,
            InputOption::VALUE_OPTIONAL,
            'Force the restore to be at this location, not the stored path'
        );
        $this->setHelp(
            <<<HELP
            <comment>Restores a local mount point from an S3 bucket</comment>

If the mount path exists, then a restore of files from the S3 mount point is
performed.

<error>Pre-existing files will be overwritten if they are different to the copy
stored in the S3 bucket.</error>

You can optionally specify a different path to restore the files to by adding
<comment>--force-path=/the/new/path</comment> which must already exist as a
directory.
HELP
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name    = $input->getArgument('name');
        $newPath = $input->getOption('force-path');
        $db      = new Database();
        $mount   = $db->fetchMountByName($name);
        try {
            $path = $this->checkMount($name, $mount);
        } catch (MountPointDoesntExistException $e) {
            if (preg_match('/is not mounted/', $e->getMessage()) && $newPath) {
                $path = $newPath;
            } else {
                throw $e;
            }
        }
        $config     = new Configuration();
        $bucketName = $config->get('prefix').'-'.get_current_user().'-'.$name;
        $client     = new S3Bucket(S3Client::factory($config->get('aws')));
        if ($client->bucketDoesntExist($bucketName)) {
            throw new BucketDoesntExistException(
                'Has this mount point been backed up before?'
            );
        }
        $client->downloadToDirectory($bucketName, $path);
        $output->writeln('<info>Mount point restored</info>');
    }
}
