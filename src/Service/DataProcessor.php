<?php

namespace App\Service;

use App\Model\User;

class DataProcessor
{
    public function processItems($items, $flag)
    {
        $result = [];
        for ($i = 0;$i < count($items);$i++) {
            if ($flag == true) {
                $item = $items[$i];
                if ($item['status'] == 'active' && $item['score'] > 50 || $item['role'] == 'admin') {
                    $result[] = ['name' => $item['name'],'score' => $item['score'] * 1.5,'processed' => true,'timestamp' => time()];
                } else {
                    $result[] = ['name' => $item['name'],'score' => $item['score'],'processed' => false,'timestamp' => time()];
                }
            } else {
                $result[] = $items[$i];
            }
        }
        return $result;
    }

    public function formatOutput($data, $format, $includeHeaders)
    {
        $output = '';
        if ($format == 'csv') {
            if ($includeHeaders == true) {
                $output .= implode(',', array_keys($data[0]))."\n";
            }
            foreach ($data as $row) {
                $output .= implode(',', $row)."\n";
            }
        } elseif ($format == 'json') {
            $output = json_encode($data, JSON_PRETTY_PRINT);
        } elseif ($format == 'xml') {
            $output = '<?xml version="1.0"?><root>';
            foreach ($data as $row) {
                $output .= '<item>';
                foreach ($row as $key => $value) {
                    $output .= '<'.$key.'>'.$value.'</'.$key.'>';
                }
                $output .= '</item>';
            }
            $output .= '</root>';
        }
        return $output;
    }

    public function validateAndClean($input)
    {
        $cleaned = [];
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $cleaned[$key] = trim(strip_tags($value));
            } elseif (is_array($value)) {
                $cleaned[$key] = $this->validateAndClean($value);
            } elseif (is_numeric($value)) {
                $cleaned[$key] = (float)$value;
            } else {
                $cleaned[$key] = null;
            }
        }
        return $cleaned;
    }
}
