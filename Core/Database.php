<?php
/**
 * Database.php
 *
 * User: mikey
 * Date: 23/12/2014
 * Time: 19:21
 */

namespace Core;

/**
 * Class Database
 *
 * @package Core
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
    private $ddl = <<<DDL
BEGIN;
    CREATE TABLE checksums (mount TEXT, file_key TEXT, checksum TEXT);
    CREATE INDEX chk_mount ON checksums (mount);
    CREATE INDEX chk_key ON checksums (file_key);
COMMIT;
DDL;


    /**
     * @var string
     */
    private $mount;

    public function __construct($mount)
    {
        $this->dbh = new \PDO(
            'sqlite:' . __DIR__ . '/../conf/checksums.db', null, null
        );

        try {
            $res = $this->dbh->query("SELECT 1 FROM checksums");
            if (!$res) {
                $this->dbh->exec($this->ddl);
            }
        } catch (\Exception $e) {
            $this->dbh->exec($this->ddl);
        }

        $this->mount = $mount;
    }

    /**
     * @param string $key
     * @param string $sum
     */
    public function setChecksumFor($key, $sum)
    {
        $res = $this->getChecksumFor($key);
        if (empty($res)) {
            $sql = "INSERT INTO checksums VALUES (:mount, :path, :sum)";
        } else {
            $sql = "UPDATE checksums SET checksum = :sum WHERE mount = :mount AND file_key = :path";
        }
        $sth = $this->dbh->prepare($sql);
        if (!is_object($sth)) {
            var_dump($this->dbh->errorInfo());
            die('why?');
        }
        $sth->bindValue(':mount', $this->mount);
        $sth->bindValue(':path', $key);
        $sth->bindValue(':sum', $sum);
        $sth->execute();
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function getChecksumFor($key)
    {
        $sql = "SELECT checksum FROM checksums WHERE mount = :mount AND file_key = :key";
        $sth = $this->dbh->prepare($sql);
        $sth->bindValue(':key', $key);
        $sth->execute();
        $res = $sth->fetchAll();

        if (empty($res)) {
            return '';
        } else {
            return $res[0]['checksum'];
        }
    }
}
