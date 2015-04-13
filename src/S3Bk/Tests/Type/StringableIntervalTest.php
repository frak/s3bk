<?php

namespace S3Bk\Tests\Type;

use S3Bk\Type\StringableInterval;

/**
 * StringableIntervalTest.
 */
class StringableIntervalTest extends \PHPUnit_Framework_TestCase
{
    public function intervalProvider()
    {
        return [
            ['P1Y1M1DT1H1M1S'],
            ['P1Y1M1D'],
            ['PT1H1M1S'],
        ];
    }

    /**
     * @dataProvider intervalProvider
     */
    public function testIntervalsAreConvertedCorrectlyBackToStrings($interval)
    {
        $sut = new StringableInterval($interval);
        $res = (string)$sut;
        $this->assertEquals($interval, $res);
    }
}
