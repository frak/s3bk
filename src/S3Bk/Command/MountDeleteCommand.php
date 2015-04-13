<?php

namespace S3Bk\Command;

use Aws\S3\S3Client;
use S3Bk\Exception\BucketDoesntExistException;
use S3Bk\Exception\MountPointDoesntExistException;
use S3Bk\Service\Configuration;
use S3Bk\Service\Database;
use S3Bk\Service\S3Bucket;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * MountDeleteCommand.
 */
class MountDeleteCommand extends Command
{
    protected function configure()
    {
        $this->setName('mount:delete')
            ->setDescription('Purge a mount from S3');
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The name of the mount to delete'
        );
        $this->setHelp(<<<HELP
<comment>Delete the S3 backup of a mount</comment>


HELP
);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            '<question>Are you sure you want to delete this remote '.
            'backup?</question> ',
            false
        );
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $name  = $input->getArgument('name');
        $db    = new Database();
        $mount = $db->fetchMountByName($name);
        if (is_null($mount)) {
            throw new MountPointDoesntExistException($name.' does not exist');
        }

        $config     = new Configuration();
        $bucketName = $config->get('prefix').'-'.get_current_user().'-'.$name;
        $client     = new S3Bucket(S3Client::factory($config->get('aws')));
        if ($client->bucketDoesntExist($bucketName)) {
            throw new BucketDoesntExistException(
                'Has this mount point been backed up before?'
            );
        }
        $client->deleteBucket($bucketName);
    }
}
