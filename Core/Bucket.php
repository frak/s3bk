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
}