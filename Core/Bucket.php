<?php

namespace Core;

/**
 * Service object for managing buckets
 *
 * @author Michael Davey frak.off@gmail.com
 * @package Core
 */
class Bucket
{
    /**
     * Creates a new bucket, if it doesn't already exist
     *
     * @param AmazonS3 $s3 The AWS class for interacting with S3
     * @param string $name The name of the bucket to create
     * @param int $region The region to create the bucket in (default is AmazonS3::REGION_EU_W1)
     * @param bool $versioning Whether or not to enable versioning (default is false)
     * @return bool true if the bucket was created, false if it already exists
     * @throws InvalidArgumentException If the bucket name is empty
     */
    public static function create(\AmazonS3 $s3, $name, $region = \AmazonS3::REGION_EU_W1, $versioning = false)
    {
        if(empty($name)) {
            throw new \InvalidArgumentException("The bucket must have a name");
        }
        if(!$s3->if_bucket_exists($name)) {
            $res = $s3->create_bucket($name, $region);
            if($res->isOK()) {
                $exists = $s3->if_bucket_exists($name);
                while (!$exists) {
                    sleep(1);
                    $exists = $s3->if_bucket_exists($name);
                }
                if(!$versioning) {
                    do {
                        $s3->disable_versioning($name);
                        $res = $s3->get_versioning_status($name);
                    }
                    while ((string)$res->body->Status !== 'Suspended');
                }
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Removes a bucket from S3
     *
     * @param AmazonS3 $s3 The AWS class for interacting with S3
     * @param string $name The name of the bucket to delete
     * @param bool $force Whether to remove any contents from the bucket before deleting
     * @return bool Whether or not the bucket was deleted
     * @throws InvalidArgumentException If the bucket name is empty
     */
    public static function delete(\AmazonS3 $s3, $name, $force = false)
    {
        if(empty($name) || !$s3->if_bucket_exists($name)) {
            throw new \InvalidArgumentException("The bucket must have a name and must exist on S3");
        }
        if($force) {
            $res = $s3->delete_all_object_versions($name);
        }
        $res = $s3->delete_bucket($name);
        return $res->status == 204;
    }

    /**
     * Returns all of the files in the bucket as an array
     *
     * @param AmazonS3 $s3 The AWS class for interacting with S3
     * @param string $name The name of the bucket to delete
     * @return array Array of remote files
     * @throws InvalidArgumentException If the bucket name is empty
     */
    public static function getFiles(\AmazonS3 $s3, $name)
    {
        if(empty($name) || !$s3->if_bucket_exists($name)) {
            throw new \InvalidArgumentException("The bucket must have a name and must exist on S3 ($name)");
        }
        return $s3->get_object_list($name);
    }
}