<?php

namespace S3Bk\Tests\Service;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use S3Bk\Service\Configuration;

/**
 * ConfigurationTest.
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        vfsStream::setup('test-home');
        $_SERVER['HOME'] = vfsStream::url('test-home');
    }

    /**
     * @expectedException \S3Bk\Exception\ConfigFileDoesNotExistException
     */
    public function testExceptionIsThrownIfTheConfigFileDoesNotExist()
    {
        new Configuration();
    }

    /**
     * @expectedException \S3Bk\Exception\ConfigHasIncorrectPermissionsException
     */
    public function testExceptionIsThrownIfTheConfigFileIsNotLockedDown()
    {
        mkdir(vfsStream::url('test-home/.s3bk'));
        touch(vfsStream::url('test-home/.s3bk/config.yml'));
        new Configuration();
    }

    public function testConfigValuesAreParsedCorrectly()
    {
        mkdir(vfsStream::url('test-home/.s3bk'));
        file_put_contents(
            vfsStream::url('test-home/.s3bk/config.yml'),
            $this->configFile
        );
        chmod(vfsStream::url('test-home/.s3bk/config.yml'), 0600);
        $sut = new Configuration();
        $this->assertEquals('value', $sut->get('scalar'));
        $this->assertCount(2, $sut->get('array'));
        $hash = $sut->get('hash');
        $this->assertEquals(1, $hash['first']);
        $this->assertEquals(2, $hash['second']);
        $this->assertNull($sut->get('made-up-key'));
    }

    private $configFile
        = <<<CONFIG
scalar: value
array:
    - first
    - second
hash: { first: 1, second: 2}
CONFIG;

}
