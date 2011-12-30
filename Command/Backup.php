<?php

namespace Command;

class Backup extends \Core\Command
{
    private $_systemFiles = array('.DS_Store', 'Thumbs.db');

    private $_progressBar;

    private $_amountDone = 0;

    public function run()
    {
        $mount = \Core\Mounts::get($this->_getKey('name'));
        $this->_progressBar = $this->_getKey('pbar');
        $this->_progressBar->UPDATED = false;
        if(!$this->_s3->if_bucket_exists($this->_getBucketName())) {
            \Core\Bucket::create($this->_s3, $this->_getBucketName());
        }
        $this->_s3->register_streaming_read_callback(array($this, '_writeCallback'));
        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($mount->path));
        while($iter->valid()) {
            $key = $iter->key();
            if(!$iter->isDot() && !$this->_isSystemFile($key)) {
                $res = $this->_processFile($mount->path, $key);
                if(!$res) {
                    echo "Problems with AmazonS3 service - please try again in a few minutes\n";
                    return;
                }
                $this->_progressBar->UPDATED = false;
            }
            $iter->next();
        }
        $this->_prune($mount->path);
    }

    private function _isSystemFile($fileName) {
        $fileName = explode(DIRECTORY_SEPARATOR, $fileName);
        $fileName = array_pop($fileName);
        return in_array($fileName, $this->_systemFiles);
    }

    public function _writeCallback($curlHandle, $fileHandle, $length)
    {
        if(!$this->_progressBar->UPDATED) {
            $this->_amountDone = curl_getinfo($curlHandle, CURLINFO_CONTENT_LENGTH_UPLOAD);
            $this->_progressBar->reset('%fraction% KB [%bar%] %percent%', '=>', ' ', 100, $this->_amountDone);
            $this->_progressBar->UPDATED = true;
        }
        $this->_progressBar->update(curl_getinfo($curlHandle, CURLINFO_SIZE_UPLOAD));
    }

    private function _processFile($base, $name)
    {
        try {
            $bucket = $this->_getBucketName();
            $fileName = str_replace($base, '', $name);
            echo "{$fileName}... ";
            $sourceFile = $name;
            if($this->_s3->if_object_exists($bucket, $fileName)) {
                do {
                    $res = $this->_s3->get_object_headers($bucket, $fileName);
                } while(!$res->isOK());
                $remoteMd5 = str_replace('"', '', $res->header['etag']);
                $localMd5 = md5_file($sourceFile);
                if($remoteMd5 === $localMd5) {
                    echo "unchanged" . PHP_EOL;
                } else {
                    echo "modified" . PHP_EOL;
                    $this->_s3->delete_object($bucket, $fileName);
                    $res = $this->_s3->create_object($bucket, $fileName, array(
                        'fileUpload' => $sourceFile,
                    ));
                    $this->_progressBar->update($this->_amountDone);
                    echo PHP_EOL;
                }
            } else {
                echo "created" . PHP_EOL;
                $res = $this->_s3->create_object($bucket, $fileName, array(
                    'fileUpload' => $sourceFile,
                ));
                $this->_progressBar->update($this->_amountDone);
                echo PHP_EOL;
            }
            return true;
        } catch (RequestCore_Exception $e) {
            return false;
        }
    }

    private function _prune($base) {
        $bucket = $this->_getBucketName();
        $list = $this->_s3->get_object_list($bucket);
        $delCount = 0;
        foreach($list as $file) {
            if(!file_exists($base . $file)) {
                $this->_s3->batch()->delete_object($bucket, $file);
                ++$delCount;
            }
        }
        if($delCount > 0) {
            $this->_s3->batch()->send();
        }
        echo "Deleted {$delCount} file(s)" . PHP_EOL;
    }

}