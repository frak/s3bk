#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

$application = new Symfony\Component\Console\Application();
$application->setName('s3bk');
$application->setVersion('@version@');
$application->add(new S3Bk\Command\MountAddCommand());
$application->add(new S3Bk\Command\MountBackupCommand());
$application->add(new S3Bk\Command\MountDeleteCommand());
$application->add(new S3Bk\Command\MountListCommand());
$application->add(new S3Bk\Command\MountRestoreCommand());
$application->add(new S3Bk\Command\SetupCommand());
$application->add(new S3Bk\Command\RunCommand());
$application->run();
