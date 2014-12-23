<?php

namespace Command;

class Restore extends \Core\Command
{

    private $_progressBar;

    private $_amountDone = 0;

    public function _writeCallback($curlHandle, $length)
    {
        if (!$this->_progressBar->UPDATED) {
            $this->_amountDone = curl_getinfo(
                $curlHandle, CURLINFO_CONTENT_LENGTH_DOWNLOAD
            );
            $this->_progressBar->reset(
                '%fraction% KB [%bar%] %percent%', '=>', ' ', 100,
                $this->_amountDone
            );
            $this->_progressBar->UPDATED = true;
        }
        $this->_progressBar->update(
            curl_getinfo($curlHandle, CURLINFO_SIZE_DOWNLOAD)
        );
    }

    public function run()
    {
        $bucket                      = $this->getBucketName();
        $this->_progressBar          = $this->getKey('pbar');
        $this->_progressBar->UPDATED = false;
        $mount                       = \Core\Mounts::get($this->getKey('name'));
        $files                       = \Core\Bucket::getFiles(
            $this->s3, $bucket
        );
        $this->s3->register_streaming_write_callback(
            array($this, '_writeCallback')
        );
        foreach ($files as $fileName) {
            echo "{$fileName}... ";
            $sourceFile = $mount->path . $fileName;
            if (!file_exists($sourceFile)) {
                echo "not present" . PHP_EOL;
                $sourcePath = explode('/', $sourceFile);
                array_pop($sourcePath);
                $sourcePath = implode('/', $sourcePath);
                if (!is_dir($sourcePath)) {
                    mkdir($sourcePath, 0755, true);
                }
                $this->s3->get_object(
                    $bucket, $fileName, array(
                    'fileDownload' => $sourceFile
                )
                );
                $this->_progressBar->update($this->_amountDone);
                echo PHP_EOL;
            } else {
                do {
                    $res = $this->s3->get_object_headers($bucket, $fileName);
                } while (!$res->isOK());

                $remoteMd5 = str_replace('"', '', $res->header['etag']);
                $localMd5  = md5_file($sourceFile);
                if ($remoteMd5 === $localMd5) {
                    echo "already latest version" . PHP_EOL;
                } else {
                    echo "differs from backup" . PHP_EOL;
                    $this->s3->get_object(
                        $bucket, $fileName, array(
                        'fileDownload' => $sourceFile
                    )
                    );
                    $this->_progressBar->update($this->_amountDone);
                    echo PHP_EOL;
                }
            }
            $this->_progressBar->UPDATED = false;
        }
    }
}
