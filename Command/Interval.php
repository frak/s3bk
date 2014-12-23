<?php

namespace Command;

class Interval extends \Core\Command
{
    public function run()
    {
        $name     = $this->getKey('name');
        $interval = $this->getKey('interval');

        $crons = \Core\Cron::get();
        $index = -1;
        $count = count($crons);
        for ($i = 0; $i < $count; ++$i) {
            $pattern = "/{$name}/";
            $matches = array();
            if (preg_match($pattern, $crons[$i])) {
                $index = $i;
                break;
            }
        }

        if ($interval !== 'clear') {
            $interval = $this->parseInterval($interval);
            $command  = "{$interval} " . dirname(dirname(__FILE__))
                . "/s3bk backup {$name} > /dev/null 2>&1";
            if ($index > -1) {
                $crons[$index] = $command;
            } else {
                $crons[] = $command;
            }
            $verb = 'set';
        } else {
            if ($index > -1) {
                unset($crons[$index]);
            }
            $verb = 'cleared';
        }
        $crontab = implode("\n", $crons);
        \Core\Cron::put($crontab);
        echo "Interval for '{$name}' was {$verb}" . PHP_EOL;
    }

    private function parseInterval($interval)
    {
        if (is_numeric($interval)) {
            return "*/{$interval} * * * *";
        } else {
            $matches = array();
            if (preg_match('/^(\d+)d$/', $interval, $matches)) {
                return "0 */{$matches[1]} * * *";
            } else {
                return "*/30 * * * *";
            }
        }
    }
}
