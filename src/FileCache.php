<?php

namespace ramzinex;


use Cache\Adapter\Common\CacheItem;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\SimpleCache\InvalidArgumentException;

class FileCache
{
    protected FilesystemCachePool $instance;

    public function __construct()
    {
        $filesystemAdapter = new Local(__DIR__ . '/cache/');
        $filesystem = new Filesystem($filesystemAdapter);

        $this->instance = new FilesystemCachePool($filesystem);
    }

    /**
     * @throws InvalidArgumentException
     * default on 10 minuets *
     */
    public function setItem($key, $value, $expire_time =  null): void
    {
        $this->instance->set($key, $value, $expire_time);

    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getItem($key)
    {
        return $this->instance->get($key);
    }

    /**
     * check if cache is expired *
     * @param $key
     * @return false
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function isExpired($key): bool
    {
        return !$this->instance->hasItem($key);
    }

    /**
     * clear cache *
     * @param $key
     * @return void
     * @throws InvalidArgumentException
     */
    public function clearCache($key = null): void
    {
        if ($key)
            $this->instance->delete($key);
        else
            $this->instance->clear();

    }
}