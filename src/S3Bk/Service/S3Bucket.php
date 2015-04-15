<?php

namespace S3Bk\Service;

use Aws\S3\Model\ClearBucket;
use Aws\S3\S3Client;
use Guzzle\Common\Exception\ExceptionCollection;

/**
 * S3Bucket.
 */
class S3Bucket
{

    /** @var S3Client */
    private $s3;

    /** @var ClearBucket */
    private $clearBucket;

    /**
     * Set the S3 client to use
     *
     * @param S3Client $s3
     */
    public function __construct(S3Client $s3)
    {
        $this->s3 = $s3;
    }

    /**
     * Create a bucket in the specified region
     *
     * @param string $name
     * @param string $region
     */
    public function create($name, $region)
    {
        $config = ['Bucket' => $name, 'LocationConstraint' => $region];
        $this->s3->createBucket(
            $config
        );
        $this->s3->waitUntilBucketExists($config);
    }

    /**
     * Check if a bucket already exists on S3
     *
     * @param string $name
     *
     * @return bool
     */
    public function bucketDoesntExist($name)
    {
        $result = $this->s3->listBuckets();
        foreach ($result['Buckets'] as $bucket) {
            if ($bucket['Name'] === $name) {
                return false;
            }
        }

        return true;
    }

    /**
     * Upload a local directory to an S3 bucket
     *
     * @param string $name
     * @param string $path
     */
    public function uploadDirectory($name, $path)
    {
        $this->s3->uploadDirectory(
            $path,
            $name,
            null,
            ['debug' => true, 'multipart_upload_size' => 31457280]
        );
        $iterator     = $this->s3->getIterator(
            'ListObjects',
            ['Bucket' => $name]
        );
        $deleteParams = [
            'Bucket' => $name,
            'Objects' => []
        ];
        foreach ($iterator as $object) {
            $key       = $object['Key'];
            $localPath = $path.'/'.$key;
            if (!file_exists($localPath)) {
                $deleteParams['Objects'][] = ['Key' => $key];
            }
        }
        if (count($deleteParams['Objects']) > 0) {
            $this->s3->deleteObjects($deleteParams);
        }
    }

    /**
     * Download an S3 bucket to a local directory
     *
     * @param string $name
     * @param string $path
     */
    public function downloadToDirectory($name, $path)
    {
        $this->s3->downloadBucket($path, $name, null, ['debug' => true]);
    }

    /**
     * Deletes a bucket from S3
     *
     * @param string $name
     *
     * @throws ExceptionCollection
     */
    public function deleteBucket($name)
    {
        // todo How do I unit test this sensibly?
        $clear = $this->getClearBucket($name);
        $clear->clear();
        $this->s3->deleteBucket(['Bucket' => $name]);
    }

    /**
     * @param $name
     *
     * @return ClearBucket
     */
    protected function getClearBucket($name)
    {
        if (is_object($this->clearBucket)) {
            return $this->clearBucket;
        }

        // @codeCoverageIgnoreStart
        return new ClearBucket($this->s3, $name);
        // @codeCoverageIgnoreEnd
    }


    /**
     * For unit testing only
     *
     * @param ClearBucket $clearBucket
     */
    public function setClearBucket($clearBucket)
    {
        $this->clearBucket = $clearBucket;
    }
}
