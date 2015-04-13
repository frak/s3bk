<?php

namespace S3Bk\Command;

use Aws\S3\S3Client;
use S3Bk\Service\Configuration;
use S3Bk\Service\Database;
use S3Bk\Service\S3Bucket;
use S3Bk\Service\BucketNameBuilder;
use S3Bk\Traits\CheckMountPointTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MountBackupCommand.
 */
class MountBackupCommand extends Command
{
    use CheckMountPointTrait;

    protected function configure()
    {
        $this->setName('mount:backup')
            ->setDescription('Push a mount point to S3');
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The name of the mount point to use'
        );
        $this->setHelp(<<<HELP
<comment>Backup files to S3 from a named mount point</comment>

The only argument to this command is the name of a previously defined mount.
The S3 bucket will be created if it doesn't exist and then files will be
incrementally pushed to S3. WARNING: Deleted files are also pruned
automatically.
HELP
);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name          = $input->getArgument('name');
        $db            = new Database();
        $mount         = $db->fetchMountByName($name);
        $path          = $this->checkMount($name, $mount);
        $configuration = new Configuration();
        $builder       = new BucketNameBuilder($configuration);
        $bucketName    = $builder->getBucketName($name);
        $client        = new S3Bucket(
            S3Client::factory($configuration->get('aws'))
        );
        if ($client->bucketDoesntExist($bucketName)) {
            $client->create($bucketName, $configuration->get('aws')['region']);
        }
        $client->uploadDirectory($bucketName, $path);
    }
}
