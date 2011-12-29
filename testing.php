<?php

error_reporting(-1);
header("Content-type: text/plain; charset=utf-8");
require_once 'aws/sdk.class.php';
require_once 'Console/ProgressBar.php';

$user = "mikey";
$s3 = new AmazonS3();
$bucket = strtolower($s3->key) . "-{$user}-backup";
$source = "/Users/mikey/test_upload/";

function makeBucket() {
    global $s3, $bucket;
    if(!$s3->if_bucket_exists($bucket)) {
        $res = $s3->create_bucket($bucket, AmazonS3::REGION_EU_W1);
        if($res->isOK()) {
            $exists = $s3->if_bucket_exists($bucket);
            while (!$exists) {
                sleep(1);
                $exists = $s3->if_bucket_exists($bucket);
            }
            do {
                $s3->disable_versioning($bucket);
                $res = $s3->get_versioning_status($bucket);
                sleep(1);
            }
            while ((string)$res->body->Status !== 'Suspended');
            echo "Bucket {$bucket} has been created" . PHP_EOL;
        }
    }
}

$ignoreFiles = array(
    '.DS_Store',
    'Thumbs.db',
);

function isFileIgnored($fileName) {
    global $ignoreFiles;
    $fileName = explode(DIRECTORY_SEPARATOR, $fileName);
    $fileName = array_pop($fileName);
    return in_array($fileName, $ignoreFiles);
}

$ignorePatterns = array(
    '/\.git/',
);

function isPatternIgnored($fileName) {
    global $ignorePatterns;
    foreach($ignorePatterns as $pattern) {
        $count = preg_match($pattern, $fileName);
        if ($count > 0) {
            return true;
        }
    }
    return false;
}

function read_callback($curlHandle, $fileHandle, $length) {
    global $progressBar, $allDone;
    if(!$progressBar->UPDATED) {
        $allDone = curl_getinfo($curlHandle, CURLINFO_CONTENT_LENGTH_UPLOAD);
        $progressBar->reset('%fraction% KB [%bar%] %percent%', '=>', ' ', 100, $allDone);
        $progressBar->UPDATED = true;
    }
    $progressBar->update(curl_getinfo($curlHandle, CURLINFO_SIZE_UPLOAD));
}

function processfile($fileName) {
    global $source, $bucket, $s3, $progressBar, $allDone;
    $sourceFile = $fileName;
    $fileName = str_replace($source, '', $fileName);
    echo "File: {$fileName}... ";
    if($s3->if_object_exists($bucket, $fileName)) {
        do {
            $res = $s3->get_object_headers($bucket, $fileName);
        } while(!$res->isOK());
        $remoteMd5 = str_replace('"', '', $res->header['etag']);
        $localMd5 = md5_file($sourceFile);
        if($remoteMd5 === $localMd5) {
            echo "skipping" . PHP_EOL;
        } else {
            echo "modified" . PHP_EOL;
            $s3->delete_object($bucket, $fileName);
            $res = $s3->create_object($bucket, $fileName, array(
                'fileUpload' => $sourceFile,
            ));
            $progressBar->update($allDone);
            echo PHP_EOL;
        }
    } else {
        echo "uploading" . PHP_EOL;
        $res = $s3->create_object($bucket, $fileName, array(
            'fileUpload' => $sourceFile,
        ));
        $progressBar->update($allDone);
        echo PHP_EOL;
    }
}

function pruneDeleted() {
    echo "Checking for files to remove..." . PHP_EOL;
    global $source, $bucket, $s3;
    $list = $s3->get_object_list($bucket);
    $toDelete = false;
    foreach($list as $file) {
        if(!file_exists($source . DIRECTORY_SEPARATOR . $file)) {
            $s3->batch()->delete_object($bucket, $file);
            echo "Preparing to remove {$file}" . PHP_EOL;
            $toDelete = true;
        }
    }
    if($toDelete) {
        $s3->batch()->send();
        echo "Deleted files removed" . PHP_EOL;
    } else {
        echo "Nothing to delete" . PHP_EOL;
    }
}

/**
 * Work starts here...
 */
echo "Starting..." . PHP_EOL;
$progressBar = new Console_ProgressBar('* %fraction% KB [%bar%] %percent%', '=>', ' ', 100, 1);
$progressBar->UPDATED = false;
$allDone = 0;
makeBucket();
$s3->register_streaming_read_callback('read_callback');
$iter   = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source));
while($iter->valid()) {
    $key = $iter->key();
    if(!$iter->isDot() && !isFileIgnored($key) && !isPatternIgnored($key)) {
        processFile($key);
        $progressBar->UPDATED = false;
    }
    $iter->next();
}
pruneDeleted();