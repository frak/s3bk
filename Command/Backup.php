<?php

namespace Command;

class Backup extends \Core\Command
{
    public function run()
    {
        if(!$this->_s3->if_bucket_exists($this->_getBucketName())) {
            \Core\Bucket::create($this->_s3, $this->_getBucketName());
        }
        $base = $this->_getKey('path');
        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base));
        while($iter->valid()) {
            $key = $iter->key();
//            if(!$iter->isDot() && !isFileIgnored($key) && !isPatternIgnored($key)) {
            if(!$iter->isDot()) {
                $this->_processFile($base, $key);
//                $progressBar->UPDATED = false;
            }
            $iter->next();
        }
    }

    private function _processFile($base, $name)
    {
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
                // $progressBar->update($allDone);
                echo PHP_EOL;
            }
        } else {
            echo "uploading" . PHP_EOL;
            $res = $this->_s3->create_object($bucket, $fileName, array(
                'fileUpload' => $sourceFile,
            ));
            // $progressBar->update($allDone);
            echo PHP_EOL;
        }
    }
}