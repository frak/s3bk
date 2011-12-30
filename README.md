# s3bk

A command line tool to manage off-site backups onto the Amazon S3 service.  Currently performs backups and restores to defined mount points.

## Installation

### System Requirements

* A working PHP environment with the cURL extension installed
* The Console_Progressbar PEAR library: `sudo pear install Console_Progressbar`

* An Amazon AWS account, that is signed up to use S3 (For details on how to sign up for an S3 account please visit [aws.amazon.com/products](http://aws.amazon.com/products))

### Installation
*TODO*

## Usage

Once installed, you will need to configure your account details by creating the following file: ~/.aws/sdk/config.inc.php

    <?php
    if (!class_exists('CFRuntime')) die('No direct access allowed.');
    CFCredentials::set(array(
        '@default' => array(
            'key' => 'your-aws-key',
            'secret' => 'your-aws-secret',
            'default_cache_config' =>  '/tmp/cache' ,
            'certificate_authority' =>  false
        )
    ));

**Do not share your AWS secret with anyone**

## Planned development

* Synchronised folders, a la Dropbox