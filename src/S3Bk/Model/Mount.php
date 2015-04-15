<?php

namespace S3Bk\Model;

use S3Bk\Type\StringableInterval;

/**
 * Mount.
 */
class Mount
{

    /** @var string The mount name */
    private $name;

    /** @var string The path to the mount */
    private $path;

    /** @var StringableInterval How often to backup */
    private $interval;

    /** @var \DateTime Last time a backup was run */
    private $lastBackup;

    public static function createFromRow(array $row)
    {
        $out           = new Mount();
        $out->name     = $row['mount'];
        $out->path     = $row['path'];
        $out->interval = new StringableInterval($row['interval']);

        if (array_key_exists('last_backup', $row)) {
            $out->lastBackup = new \DateTime($row['last_backup']);
        }

        return $out;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return StringableInterval
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param StringableInterval $interval
     *
     * @return $this
     */
    public function setInterval(StringableInterval $interval)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastBackup()
    {
        return $this->lastBackup;
    }

    /**
     * @param \DateTime $lastBackup
     *
     * @return $this
     */
    public function setLastBackup($lastBackup)
    {
        $this->lastBackup = $lastBackup;

        return $this;
    }
}
