<?php

namespace S3Bk\Command;

use Aws\S3\S3Client;
use S3Bk\Exception\BucketDoesntExistException;
use S3Bk\Service\Configuration;
use S3Bk\Service\Database;
use S3Bk\Service\S3Bucket;
use S3Bk\Traits\CheckMountPointTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name       = $input->getArgument('name');
        $db         = new Database();
        $mount      = $db->fetchMountByName($name);
        $path       = $this->checkMount($name, $mount);
        $config     = new Configuration();
        $bucketName = $config->get('prefix').'-'.get_current_user().'-'.$name;
        $client     = new S3Bucket(S3Client::factory($config->get('aws')));
        if ($client->bucketDoesntExist($bucketName)) {
            throw new BucketDoesntExistException(
                'Has this mount point been backed up before?'
            );
        }
        $client->downloadToDirectory($bucketName, $path);
    }
}
