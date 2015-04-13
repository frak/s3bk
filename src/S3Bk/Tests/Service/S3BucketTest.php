<?php

namespace S3Bk\Tests\Service;

use \Mockery as m;
use org\bovigo\vfs\vfsStream;
use S3Bk\Service\S3Bucket;

/**
 * S3BucketTest.
 */
class S3BucketTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateBucketCreatesTheBucket()
    {
        $mockS3 = m::mock('Aws\S3\S3Client');
        $config = ['Bucket' => 'test', 'LocationConstraint' => 'test'];
        $mockS3->shouldReceive('createBucket')->once()->with($config);
        $mockS3->shouldReceive('waitUntilBucketExists')->once()->with($config);
        $sut = new S3Bucket($mockS3);
        $sut->create('test', 'test');
    }

    public function testBucketDoestExistReturnsTheCorrectResult()
    {
        $retval = [
            'Buckets' => [
                ['Name' => 'first'],
                ['Name' => 'second'],
            ]
        ];
        $mockS3 = m::mock('Aws\S3\S3Client');
        $mockS3->shouldReceive('listBuckets')->times(3)->andReturn($retval);
        $sut = new S3Bucket($mockS3);
        $this->assertFalse($sut->bucketDoesntExist('first'));
        $this->assertFalse($sut->bucketDoesntExist('second'));
        $this->assertTrue($sut->bucketDoesntExist('third'));
    }

    public function testUploadDirectoryUploadsAndPrunesTheDirectory()
    {
        vfsStream::setup('test-mount');
        $fileExists = vfsStream::url('test-mount/file');
        touch($fileExists);
        $mockS3 = m::mock('Aws\S3\S3Client');
        $mockS3->shouldReceive('uploadDirectory')->once()->with(
            'vfs://test-mount',
            'test',
            null,
            ['debug' => true]
        );
        $retval = [['Key' => 'file'], ['Key' => 'unknown-file']];
        $mockS3->shouldReceive('getIterator')
            ->once()->with('ListObjects', ['Bucket' => 'test'])
            ->andReturn($retval);
        $delval = [
            'Bucket' => 'test',
            'Objects' => [['Key' => 'unknown-file']]
        ];
        $mockS3->shouldReceive('deleteObjects')->once()->with($delval);
        $sut = new S3Bucket($mockS3);
        $sut->uploadDirectory('test', vfsStream::url('test-mount'));
    }

    public function testDownloadToDirectoryCallsS3ToDownloadTheBucket()
    {
        $mockS3 = m::mock('Aws\S3\S3Client');
        $mockS3->shouldReceive('downloadBucket')
            ->once()->with('test/path', 'test', null, ['debug' => true]);
        $sut = new S3Bucket($mockS3);
        $sut->downloadToDirectory('test', 'test/path');
    }

    public function testDeleteBucketClearsTheBucket()
    {
        $mockS3 = m::mock('Aws\S3\S3Client');
        $mockS3->shouldReceive('deleteBucket')
            ->once()->with(['Bucket' => 'test']);
        $mockClear = m::mock('Aws\S3\Model\ClearBucket');
        $mockClear->shouldReceive('clear')->once();
        $sut = new S3Bucket($mockS3);
        $sut->setClearBucket($mockClear);
        $sut->deleteBucket('test');
    }
}
