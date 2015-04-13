<?php

namespace S3Bk\Type;

/**
 * StringableInterval
 */
class StringableInterval extends \DateInterval
{
    public function __toString()
    {
        $date = array_filter(
            [
                'Y' => $this->y,
                'M' => $this->m,
                'D' => $this->d
            ]
        );
        $time = array_filter(
            [
                'H' => $this->h,
                'M' => $this->i,
                'S' => $this->s
            ]
        );

        $specString = 'P';
        foreach ($date as $key => $value) {
            $specString .= $value.$key;
        }
        if (count($time) > 0) {
            $specString .= 'T';
            foreach ($time as $key => $value) {
                $specString .= $value.$key;
            }
        }

        return $specString;
    }
}
