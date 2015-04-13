<?php

namespace S3Bk\Traits;

use S3Bk\Exception\MountPointDoesntExistException;
use S3Bk\Model\Mount;

trait CheckMountPointTrait
{

    /**
     * Assert that the mount point is ready to work with
     *
     * @param string $givenName
     * @param Mount  $mount
     *
     * @return string
     */
    protected function checkMount($givenName, Mount $mount = null)
    {
        if (is_null($mount)) {
            throw new MountPointDoesntExistException(
                $givenName.' does not exist'
            );
        }

        $path = $mount->getPath();
        if (!is_dir($path)) {
            throw new MountPointDoesntExistException(
                $path.' is not mounted'
            );
        }

        return $path;
    }
}
