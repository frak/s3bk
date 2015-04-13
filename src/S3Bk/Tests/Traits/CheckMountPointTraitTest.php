<?php

namespace S3Bk\Tests\Traits;

use org\bovigo\vfs\vfsStream;
use S3Bk\Model\Mount;
use S3Bk\Traits\CheckMountPointTrait;

/**
 * CheckMountPointTraitTest.
 */
class CheckMountPointTraitTest extends \PHPUnit_Framework_TestCase
{
    use CheckMountPointTrait;

    public function setUp()
    {
        vfsStream::setup('test');
    }

    /**
     * @expectedException \S3Bk\Exception\MountPointDoesntExistException
     */
    public function testNullMountPointThrowsException()
    {
        $this->checkMount('test', null);
    }

    /**
     * @expectedException \S3Bk\Exception\MountPointDoesntExistException
     */
    public function testUnmountedMountPointThrowsException()
    {
        $mount = new Mount();
        $mount->setName('test')->setPath(vfsStream::url('does-not-exist'));
        $this->checkMount('test', $mount);
    }

    public function testMountedMountPointReturnsThePath()
    {
        $mount = new Mount();
        $mount->setName('test')->setPath(vfsStream::url('test'));
        $res = $this->checkMount('test', $mount);
        $this->assertEquals(vfsStream::url('test'), $res);
    }
}
