<?php

namespace S3Bk\Service;

/**
 * BucketNameBuilder
 */
class BucketNameBuilder
{
    /** @param Configuration $configuration */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Build the bucket name with the configured prefix
     *
     * @param string $name
     *
     * @return string
     */
    public function getBucketName($name)
    {
        return strtolower($this->configuration->get('prefix').'-'.$name);
    }
}
