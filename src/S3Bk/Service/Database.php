<?php

namespace S3Bk\Service;

use S3Bk\Model\Mount;

/**
 * Database.
 */
class Database
{

    /**
     * @var \PDO The database connection
     */
    private $dbh;

    /**
     * @var string
     */
    private $ddl
        = <<<DDL
BEGIN;
    CREATE TABLE mounts (mount TEXT, path TEXT, interval TEXT, last_backup TEXT);
COMMIT;
DDL;

    public function __construct()
    {
        $dsn       = 'sqlite:'.$_SERVER['HOME'].'/.s3bk/s3bk.db';
        $this->dbh = new \PDO($dsn, null, null);
        $res       = $this->dbh->query('SELECT 1 FROM mounts');
        if (!$res) {
            $this->dbh->exec($this->ddl);
        }
    }

    /**
     * Persist a Mount.
     *
     * @param Mount $mount
     *
     * @return bool
     */
    public function persistMount(Mount $mount)
    {
        $sql = 'INSERT INTO mounts VALUES (:mount, :path, :interval, NULL)';
        $sth = $this->dbh->prepare($sql);
        $sth->bindValue(':mount', $mount->getName());
        $sth->bindValue(':path', $mount->getPath());
        $sth->bindValue(':interval', (string)$mount->getInterval());
        $res = $sth->execute();

        return $res;
    }

    public function updateMount(Mount $mount)
    {
        $sql = 'UPDATE mounts SET path=:path, interval=:interval, '.
            'last_backup=:lastBackup WHERE mount=:mount';
        $sth = $this->dbh->prepare($sql);
        $sth->bindValue(':mount', $mount->getName());
        $sth->bindValue(':path', $mount->getPath());
        $sth->bindValue(':interval', (string)$mount->getInterval());
        $sth->bindValue(':lastBackup', $mount->getLastBackup()->format('c'));
        $res = $sth->execute();

        return $res;
    }

    /**
     * Fetch an array of Mounts.
     *
     * @return Mount[]
     */
    public function fetchMounts()
    {
        $sth = $this->dbh->prepare('SELECT * FROM mounts');
        $sth->execute();

        $out = [];
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            $out[] = Mount::createFromRow($row);
        }

        return $out;
    }

    /**
     * Fetch a Mount by it's name.
     *
     * @param string $name
     *
     * @return Mount|null
     */
    public function fetchMountByName($name)
    {
        $sth = $this->dbh->prepare(
            'SELECT COUNT(*) AS count FROM mounts WHERE mount = :mount'
        );
        $sth->bindValue(':mount', $name);
        $sth->execute();
        $result = $sth->fetchAll();
        if ($result[0]['count'] == 1) {
            $sth = $this->dbh->prepare(
                'SELECT * FROM mounts WHERE mount = :mount'
            );
            $sth->bindValue(':mount', $name);
            $sth->execute();

            return Mount::createFromRow($sth->fetch(\PDO::FETCH_ASSOC));
        } else {
            return null;
        }
    }

    /**
     * Remove a mount from the store
     *
     * @param Mount $mount
     *
     * @return bool
     */
    public function deleteMount(Mount $mount)
    {
        $sth = $this->dbh->prepare('DELETE FROM mounts WHERE mount = :mount');
        $sth->bindValue(':mount', $mount->getName());

        return $sth->execute();
    }
}
