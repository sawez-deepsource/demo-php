<?php

namespace App\Controller;

use App\Service\CacheService;
use App\Service\ValidationService;

class ApiController
{
    private $cache;
    private $validator;
    private $rateLimits = [];

    public function __construct()
    {
        $this->cache = new CacheService(1800);
        $this->validator = new ValidationService();
    }

    public function handleRequest($method, $path, $data = [])
    {
        if (!$this->checkRateLimit($path)) {
            return ['status' => 429,'body' => ['error' => 'Too many requests']];
        }

        switch ($method) {
            case 'GET':
                return   $this->handleGet($path, $data);
            case 'POST':
                return   $this->handlePost($path, $data);
            case 'PUT':
                return   $this->handlePut($path, $data);
            case 'DELETE':
                return   $this->handleDelete($path, $data);
            default:
                return ['status' => 405,'body' => ['error' => 'Method not allowed']];
        }
    }

    private function handleGet($path, $params)
    {
        $cacheKey = 'get_'.md5($path.serialize($params));
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return ['status' => 200,'body' => $cached,'cached' => true];
        }

        $result = ['data' => [],'meta' => ['path' => $path,'params' => $params,'timestamp' => time()]];
        $this->cache->set($cacheKey, $result);
        return ['status' => 200,'body' => $result,'cached' => false];
    }

    private function handlePost($path, $data)
    {
        $rules = ['name' => 'required|min:2|max:100','email' => 'required|email','age' => 'numeric|min:0|max:150'];
        if (!$this->validator->validate($data, $rules)) {
            return ['status' => 422,'body' => ['errors' => $this->validator->getErrors()]];
        }
        return ['status' => 201,'body' => ['message' => 'Created','data' => $data]];
    }

    private function handlePut($path, $data)
    {
        if (empty($data)) {
            return ['status' => 400,'body' => ['error' => 'No data provided']];
        }
        return ['status' => 200,'body' => ['message' => 'Updated','data' => $data]];
    }

    private function handleDelete($path, $data)
    {
        return ['status' => 200,'body' => ['message' => 'Deleted']];
    }

    private function checkRateLimit($path)
    {
        $now = time();
        $window = 60;
        $maxRequests = 100;

        if (!isset($this->rateLimits[$path])) {
            $this->rateLimits[$path] = [];
        }

        $this->rateLimits[$path] = array_filter($this->rateLimits[$path], function ($timestamp) use ($now, $window) {
            return($now - $timestamp) < $window;
        });

        if (count($this->rateLimits[$path]) >= $maxRequests) {
            return false;
        }

        $this->rateLimits[$path][] = $now;
        return true;
    }
}
