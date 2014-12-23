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

    private $ddl = 'CREATE TABLE checksums (file_key TEXT, checksum TEXT)';

    public function __construct()
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
    }

    /**
     * @param string $key
     * @param string $sum
     */
    public function setChecksumFor($key, $sum)
    {
        $res = $this->getChecksumFor($key);
        if (empty($res)) {
            $sql = "INSERT INTO checksums VALUES (:path, :sum)";
        } else {
            $sql = "UPDATE checksums SET checksum = :sum WHERE file_key = :path";
        }
        $sth = $this->dbh->prepare($sql);
        if (!is_object($sth)) {
            var_dump($this->dbh->errorInfo());
            die('why?');
        }
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
        $sql = "SELECT checksum FROM checksums WHERE file_key = :key";
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
