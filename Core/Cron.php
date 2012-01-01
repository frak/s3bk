<?php

namespace Core;

class Cron
{
    public static function get()
    {
        $out = explode("\n", `crontab -l 2>/dev/null`);
        array_pop($out);
        return $out;
    }

    public static function put($crontab)
    {
        `echo '$crontab' | crontab -`;
    }
}
