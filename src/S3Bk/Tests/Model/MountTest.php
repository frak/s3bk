<?php

namespace S3Bk\Tests\Model;

use S3Bk\Model\Mount;

/**
 * MountTest.
 */
class MountTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFromRowAssignsCorrectly()
    {
        $row = [
            'mount' => 'test',
            'path' => 'test/path',
            'interval' => 'PT5M',
            'last_backup' => '2015-04-10T12:00:00+00:00'
        ];
        $res = Mount::createFromRow($row);
        $this->assertEquals('test', $res->getName());
        $this->assertEquals('test/path', $res->getPath());
        $this->assertEquals('PT5M', (string)$res->getInterval());
        $this->assertEquals(
            '2015-04-10T12:00:00+00:00',
            $res->getLastBackup()->format('c')
        );
    }
}
