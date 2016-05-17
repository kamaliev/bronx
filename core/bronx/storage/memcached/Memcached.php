<?php

namespace core\bronx\storage\memcached;

use core\Singleton;

class Memcached
{

    use Singleton;
    private $cache;

    public function __construct()
    {
        $this->cache = new \Memcached();
        $this->cache->addserver('127.0.0.1', 11211);
    }

    public function set( string $key, $value, int $lifetime = 0 ) {
        $this->cache->set($key, $value, $lifetime);
    }

    public function get( string $key ) {
        $value = $this->cache->get($key);
        if(!@empty($value)) {
            return $value;
        } else {
            return null;
        }
    }

    public function unset( string $key ) {
        $this->cache->delete($key);
    }
}