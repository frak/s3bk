<?php

namespace S3Bk\Tests\Service;

use Mockery as m;
use S3Bk\Model\Mount;
use S3Bk\Service\Schedule;

/**
 * ScheduleTest.
 */
class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    public function testTheCorrectMountsAreScheduledForBackup()
    {
        $lastRunTime = new \DateTime('-5 minutes');
        $data        = [
            Mount::createFromRow(
                [
                    'mount'    => 'not-run',
                    'path'     => 'not-run',
                    'interval' => 'PT1M',
                ]
            ),
            Mount::createFromRow(
                [
                    'mount'       => 'needs-run',
                    'path'        => 'needs-run',
                    'interval'    => 'PT1M',
                    'last_backup' => $lastRunTime->format('c'),
                ]
            ),
            Mount::createFromRow(
                [
                    'mount'       => 'has-run',
                    'path'        => 'has-run',
                    'interval'    => 'PT1H',
                    'last_backup' => $lastRunTime->format('c'),
                ]
            ),
        ];

        $mockDb = m::mock('S3Bk\Service\Database');
        $mockDb->shouldReceive('fetchMounts')
            ->once()
            ->andReturn($data);
        $sut = new Schedule($mockDb);
        $res = $sut->getMountsToBackup();
        $this->assertCount(2, $res);
        $this->assertEquals('not-run', $res[0]->getName());
        $this->assertEquals('needs-run', $res[1]->getName());
    }
}
