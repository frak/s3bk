# s3bk

A simple tool for backing your local (and removable) drives
to an Amazon S3 account. You will need to know how [AWS][aws]
works and have a valid key/secret pair that has read/write
access your S3 buckets.

## Installation

Either download the [phar file][phar] and run it, or
checkout the repo and build it yourself, using [box2][box]

## Configuration

All config lives in your home directory under an .s3bk
directory. The recommended way to set this up is to run
`./s3bk.phar setup` and edit the file that is given in the output.
Otherwise, you will need to create a config.yml file at ~/.s3bk
with the following structure:

    aws:
        credentials:
            key: your-aws-key
            secret: your-aws-secret
        region: your-preferred-aws-region
    prefix: your-unique-prefix

The prefix is needed to ensure that your buckets have a globally 
unique name within S3, please choose wisely to avoid conflicts.

Once you have run the setup command an entry will be placed in your
crontab which will automatically check for any backup points that
need to be synchronised and back them up for you.

## Adding a mount point for backup

To specify which parts of your system you would like to backup you
will need to run `./s3bk.phar mount:add name path interval` (mount:add 
can be abbreviated to m:a for the lazy typist). Here is an explanation
of the arguments you need to provide.

* name: the name of the mount point, used to refer to it in later 
  commands
* path: the full (or relative) path to the folder you wish to backup
* interval: an [ISO 8901 formatted date/time interval][iso] to specify
  how often backups should be run for this mount

## Manually backing up files to S3

If you have run the setup command as described above, you should not 
normally need to manually backup your files as the command is run 
periodically to check for mounts that need to be backed up.  However,
you may wish to manually trigger a backup if you have just added new
files, or you have not run the setup and wish to have manual control
over your backups.

To trigger the backup of a mount you need to run `./s3bk.phar mount:backup name`
(again, mount:backup can be abbreviated to m:b), giving the name of a
mount point that you have previously added.  Once new and changed files
have been sent to S3, any files in your bucket that have been deleted
locally will also be pruned.

## Restoring files from S3

**Sorry to hear you lost your files!**

But it's a good job you have them backed up :o)

To restore your files to the path specified, all you need to do is run
`./s3bk.phar mount:restore name` which of course can be abbreviated to
m:r.  If the mount point that was setup no-longer exists and you want
to restore your files elsewhere then you can specify this path using
`./s3bk.phar mount:restore name --force-path=/new/mount/path`.

## Deleting an S3 backup

To remove an S3 backup you need to run `./s3bk.phar mount:delete name`
(or just m:d) and the mount will both be removed from S3 and it will
be deleted from the local data store.  No local files will be deleted,
that responsibility lies with you!

## Listing your defined mount points

To see a list of your defined mount points and when they were last
synchronised with S3, simply run `./s3bk.phar mount:list` (m:l).

# To Do

- a way to edit defined mount points
- ability to use a versioned S3 bucket

[aws]: http://aws.amazon.com/s3/
[phar]: file://s3bk.phar
[box]: http://github.com/box-project/box2
[iso]: http://en.wikipedia.org/wiki/ISO_8601#Durations
