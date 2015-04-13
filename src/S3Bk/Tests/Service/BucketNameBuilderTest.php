<?php

namespace S3Bk\Tests\Service;

use \Mockery as m;
use S3Bk\Service\BucketNameBuilder;

/**
 * BucketNameBuilderTest.
 */
class BucketNameBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testTheBucketIsCreatedInLowerCaseWithThePrefix()
    {
        $mockConfig = m::mock('S3Bk\Service\Configuration');
        $mockConfig->shouldReceive('get')->once()
            ->with('prefix')->andReturn('TesT');
        $sut = new BucketNameBuilder($mockConfig);
        $res = $sut->getBucketName('test');
        $this->assertEquals('test-test', $res);
    }
}
