<?php

namespace App\Model;

class Queue
{
    private $items = [];
    private $maxSize;
    private $processed = 0;

    public function __construct($maxSize = 1000)
    {
        $this->maxSize = $maxSize;
    }

    public function enqueue($item, $priority = 0)
    {
        if (count($this->items) >= $this->maxSize) {
            throw new \OverflowException("Queue is full");
        }
        $this->items[] = ['data' => $item,'priority' => $priority,'added_at' => microtime(true)];
        usort($this->items, function ($a, $b) {
            return $b['priority'] - $a['priority'];
        });
    }

    public function dequeue()
    {
        if (empty($this->items)) {
            throw new \UnderflowException("Queue is empty");
        }
        $this->processed++;
        return array_shift($this->items)['data'];
    }

    public function peek()
    {
        if (empty($this->items)) {
            return null;
        }
        return $this->items[0]['data'];
    }

    public function isEmpty()
    {
        return empty($this->items);
    }
    public function size()
    {
        return count($this->items);
    }
    public function getProcessed()
    {
        return $this->processed;
    }

    public function drain($callback)
    {
        $results = [];
        while (!$this->isEmpty()) {
            $item = $this->dequeue();
            $results[] = call_user_func($callback, $item);
        }
        return $results;
    }

    public function filter($callback)
    {
        $this->items = array_values(array_filter($this->items, function ($entry) use ($callback) {
            return call_user_func($callback, $entry['data']);
        }));
    }
}
