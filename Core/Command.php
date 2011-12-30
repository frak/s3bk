<?php

namespace Core;

abstract class Command
{
    /**
     * @var AmazonS3 The S3 instance
     */
    protected $_s3;

    /**
     * @var array Key value store for commands
     */
    private $_params;

    /**
     * Default constructor
     *
     * Sets up Amazon S3 instance
     */
    public function __construct()
    {
        $this->_s3 = new \AmazonS3();
    }

    /**
     * Sets a value for a key
     * @param string $name The name of the key
     * @param mixed $value The value to set
     * @throws InvalidArgumentException If the key name is empty
     */
    public function setKey($name, $value)
    {
        if(empty($name)) {
            throw new \InvalidArgumentException("The key must have a name");
        }
        $this->_params[$name] = $value;
    }

    /**
     * Sets a reference value for a key
     * @param string $name The name of the key
     * @param mixed $value The value to set
     * @throws InvalidArgumentException If the key name is empty
     */
    public function setRefKey($name, &$value)
    {
        if(empty($name)) {
            throw new \InvalidArgumentException("The key must have a name");
        }
        $this->_params[$name] = $value;
    }

    /**
     * Retrieves a value for a key
     * @param string $name The key to retrieve
     * @return mixed The value for the key
     * @throws InvalidArgumentException If the key is not set
     */
    protected function _getKey($name)
    {
        if(!isset($this->_params[$name])) {
            throw new \InvalidArgumentException("The key '{$name}' has not been set");
        }
        return $this->_params[$name];
    }

    protected function _getBucketName()
    {
        $name = $this->_getKey('name');
        $user = $this->_getKey('user');
        $key  = strtolower($this->_s3->key);
        return "{$key}-{$user}-{$name}";
    }

    /**
     * Run method to be called be the bootstrap
     */
    abstract public function run();

}