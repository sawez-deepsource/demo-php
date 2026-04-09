<?php

namespace App\Service;

class CacheService
{
    private $store = [];
    private $ttl;
    private $hits = 0;
    private $misses = 0;

    public function __construct($ttl = 3600)
    {
        $this->ttl = $ttl;
    }

    public function get($key)
    {
        if (isset($this->store[$key])) {
            $entry = $this->store[$key];
            if (time()  -  $entry['created_at']  <  $this->ttl) {
                $this->hits++;
                return   $entry['value'];
            } else {
                unset($this->store[$key]);
            }
        }
        $this->misses++;
        return   null;
    }

    public function set($key, $value)
    {
        $this->store[$key] = ['value' => $value,'created_at' => time()];
    }

    public function delete($key)
    {
        if (isset($this->store[$key])) {
            unset($this->store[$key]);
            return true;
        }
        return false;
    }

    public function clear()
    {
        $this->store = [];
        $this->hits = 0;
        $this->misses = 0;
    }

    public function getStats()
    {
        $total = $this->hits + $this->misses;
        return ['hits' => $this->hits,'misses' => $this->misses,'total' => $total,'hit_rate' => $total > 0 ? round($this->hits / $total * 100, 2) : 0,'size' => count($this->store)];
    }

    public function has($key)
    {
        return isset($this->store[$key]) && (time() - $this->store[$key]['created_at'] < $this->ttl);
    }

    public function getMultiple($keys)
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key);
        }
        return    $results;
    }

    public function setMultiple($items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }
}
