# s3bk

A command line tool to manage off-site backups onto the Amazon S3 service.  Currently performs backups and restores to defined mount points.

## Installation

### System Requirements

* A working PHP environment with the cURL extension installed
* The Console_Progressbar PEAR library: `sudo pear install Console_Progressbar`
* An Amazon AWS account, that is signed up to use S3 (For details on how to sign up for an S3 account please visit [aws.amazon.com/products](http://aws.amazon.com/products))

### Installation

Clone this repository and run it from there at the moment - this is something I will work on once I have the feature set complete for v1.

I have not tested this on any other environments other than my own, YMMV, especially if you run Windows.

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

Once you have done this, you are able to run the following commands:

`./s3bk list` Shows you a list of currently defined mount points

`./s3bk mount /path/to/mount name` Creates a mount point called 'name' at /path/to/mount

`./s3bk remove name [force]` Deletes a mount point from Amazon, you can optionally specify 'force' as the last argument to force deletion of a mount point that has not been emptied.

`./s3bk backup name` Uploads any new or changed files from the mount point to Amazon.

`./s3bk restore name` Restores the mount point from Amazon. **This will overwrite any local changes**


## Planned development

* Synchronised folders, a la Dropbox.
* Unit tests - this was a scratch-an-itch project to save me Dropbox fees for what was just essentially an offsite backup.