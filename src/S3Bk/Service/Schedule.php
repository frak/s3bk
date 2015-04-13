<?php

namespace S3Bk\Service;

use S3Bk\Model\Mount;

/**
 * Schedule.
 */
class Schedule
{

    /** @var Database */
    private $db;

    /** @param Database $db */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Fetch a list of mounts that need to backup
     *
     * @return Mount[]
     */
    public function getMountsToBackup()
    {
        $out = [];
        $mounts = $this->db->fetchMounts();
        $now = new \DateTime();
        foreach ($mounts as $mount) {
            $interval = $mount->getInterval();
            $lastRun  = $mount->getLastBackup();
            if (is_null($lastRun)) {
                $out[] = $mount;
            } else {
                $lastRun->add($interval);
                if ($lastRun < $now) {
                    $out[] = $mount;
                }
            }
        }

        return $out;
    }
}
