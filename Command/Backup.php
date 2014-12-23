<?php

namespace Command;

use Core\Database;

class Backup extends \Core\Command
{

    /**
     * @var array
     */
    private $systemFiles = array('.DS_Store', 'Thumbs.db');

    /**
     * @var \Console_Progressbar
     */
    private $progressBar;

    /**
     * @var int
     */
    private $amountDone = 0;

    /**
     * @var Database
     */
    private $db;

    public function run()
    {
        $mount                      = \Core\Mounts::get($this->getKey('name'));
        $this->progressBar          = $this->getKey('pbar');
        $this->progressBar->UPDATED = false;
        $this->db                   = $this->getKey('db');

        if (!$this->s3->if_bucket_exists($this->getBucketName())) {
            \Core\Bucket::create($this->s3, $this->getBucketName());
        }

        $this->s3->register_streaming_read_callback(
            array($this, 'readCallback')
        );

        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($mount->path)
        );
        while ($iter->valid()) {
            $key = $iter->key();
            if (!$iter->isDot() && !$this->isSystemFile($key)) {
                $res = $this->processFile($mount->path, $key);
                if (!$res) {
                    echo "Problems with AmazonS3 service - please try again in a few minutes\n";

                    return;
                }
                $this->progressBar->UPDATED = false;
            }
            $iter->next();
        }
        $this->prune($mount->path);
    }

    /**
     * @param $fileName
     *
     * @return bool
     */
    private function isSystemFile($fileName)
    {
        $fileName = explode(DIRECTORY_SEPARATOR, $fileName);
        $fileName = array_pop($fileName);

        return in_array($fileName, $this->systemFiles);
    }

    public function readCallback($curlHandle, $fileHandle, $length)
    {
        if (!$this->progressBar->UPDATED) {
            $this->amountDone = curl_getinfo(
                $curlHandle, CURLINFO_CONTENT_LENGTH_UPLOAD
            );
            $this->progressBar->reset(
                '%fraction% KB [%bar%] %percent%', '=>', ' ', 100,
                $this->amountDone
            );
            $this->progressBar->UPDATED = true;
        }
        $this->progressBar->update(
            curl_getinfo($curlHandle, CURLINFO_SIZE_UPLOAD)
        );
    }

    private function processFile($base, $name)
    {
        try {
            $bucket   = $this->getBucketName();
            $fileName = str_replace($base, '', $name);
            $sourceFile = $name;
            if ($this->s3->if_object_exists($bucket, $fileName)) {
                $remoteMd5 = $this->db->getChecksumFor($fileName);
                if (empty($remoteMd5)) {
                    do {
                        $res = $this->s3->get_object_headers(
                            $bucket, $fileName
                        );
                    } while (!$res->isOK());
                    $remoteMd5 = str_replace('"', '', $res->header['etag']);
                    $this->db->setChecksumFor($fileName, $remoteMd5);
                }
                $localMd5 = md5_file($sourceFile);
                if ($remoteMd5 !== $localMd5) {
                    echo "{$fileName}... modified" . PHP_EOL;
                    $this->s3->delete_object($bucket, $fileName);
                    $res = $this->s3->create_object(
                        $bucket, $fileName, array(
                            'fileUpload' => $sourceFile,
                        )
                    );
                    $this->db->setChecksumFor($fileName, md5_file($sourceFile));
                    $this->progressBar->update($this->amountDone);
                    echo PHP_EOL;
                }
            } else {
                echo "{$fileName}... created" . PHP_EOL;
                $res = $this->s3->create_object(
                    $bucket, $fileName, array(
                        'fileUpload' => $sourceFile,
                    )
                );
                $localMd5 = md5_file($sourceFile);
                $this->db->setChecksumFor($fileName, $localMd5);
                $this->progressBar->update($this->amountDone);
                echo PHP_EOL;
            }

            return true;
        } catch (\RequestCore_Exception $e) {
            return false;
        }
    }

    private function prune($base)
    {
        $bucket   = $this->getBucketName();
        $list     = $this->s3->get_object_list($bucket);
        $delCount = 0;
        foreach ($list as $file) {
            if (!file_exists($base . $file)) {
                $this->s3->batch()->delete_object($bucket, $file);
                ++$delCount;
            }
        }
        if ($delCount > 0) {
            $this->s3->batch()->send();
        }
        echo "Deleted {$delCount} file(s)" . PHP_EOL;
    }
}
