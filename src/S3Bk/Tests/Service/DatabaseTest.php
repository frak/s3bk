<?php

namespace S3Bk\Tests\Service;

use S3Bk\Model\Mount;
use S3Bk\Service\Database;

/**
 * DatabaseTest.
 */
class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HOME'] = '/tmp';
        mkdir('/tmp/.s3bk');
    }

    public function tearDown()
    {
        unlink('/tmp/.s3bk/s3bk.db');
        rmdir('/tmp/.s3bk');
    }

    public function testMountsPersistListAndFetchCorrectly()
    {
        $one   = Mount::createFromRow(
            [
                'mount' => 'test',
                'path' => 'test/path',
                'interval' => 'PT5M',
                'lastBackup' => '2015-04-10T12:00:00+00:00'
            ]
        );
        $two = Mount::createFromRow(
            [
                'mount' => 'another',
                'path' => 'test/path',
                'interval' => 'PT5M',
                'lastBackup' => '2015-04-10T12:00:00+00:00'
            ]
        );
        $sut   = new Database();
        $this->assertTrue($sut->persistMount($one));
        $mounts = $sut->fetchMounts();
        $this->assertCount(1, $mounts);
        $this->assertTrue($sut->persistMount($two));
        $mounts = $sut->fetchMounts();
        $this->assertCount(2, $mounts);
        $res = $sut->fetchMountByName('test');
        $this->assertInstanceOf('S3Bk\Model\Mount', $res);
        $this->assertNull($sut->fetchMountByName('not-known'));
    }
}
