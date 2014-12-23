<?php

namespace Core;

abstract class Command
{
    /**
     * @var \AmazonS3 The S3 instance
     */
    protected $s3;

    /**
     * @var array Key value store for commands
     */
    private $params;

    /**
     * Default constructor
     *
     * Sets up Amazon S3 instance
     */
    public function __construct()
    {
        $this->s3 = new \AmazonS3();
    }

    /**
     * Sets a value for a key
     * @param string $name The name of the key
     * @param mixed $value The value to set
     * @throws \InvalidArgumentException If the key name is empty
     */
    public function setKey($name, $value)
    {
        if(empty($name)) {
            throw new \InvalidArgumentException("The key must have a name");
        }
        $this->params[$name] = $value;
    }

    /**
     * Sets a reference value for a key
     * @param string $name The name of the key
     * @param mixed $value The value to set
     * @throws \InvalidArgumentException If the key name is empty
     */
    public function setRefKey($name, &$value)
    {
        if(empty($name)) {
            throw new \InvalidArgumentException("The key must have a name");
        }
        $this->params[$name] = $value;
    }

    /**
     * Retrieves a value for a key
     * @param string $name The key to retrieve
     * @return mixed The value for the key
     * @throws \InvalidArgumentException If the key is not set
     */
    protected function getKey($name)
    {
        if(!isset($this->params[$name])) {
            throw new \InvalidArgumentException("The key '{$name}' has not been set");
        }
        return $this->params[$name];
    }

    protected function getBucketName()
    {
        $name = $this->getKey('name');
        $user = $this->getKey('user');
        /**
         * @todo Fix this hardcoding evil
         */
        $key  = 'akiaikjuiqufgxtyzvla';
        return "{$key}-{$user}-{$name}";
    }

    /**
     * Run method to be called be the bootstrap
     */
    abstract public function run();

}
