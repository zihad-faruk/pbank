<?php

namespace App\Helpers;

use App\Interfaces\CommissionFileProcessorInterface;

class CsvHelper implements CommissionFileProcessorInterface
{
    public function fileDataToArray(string $file_path): array
    {
        $delimiter = ",";
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return [];
        }

        $header = null;
        $data   = array();
        if (($handle = fopen($file_path, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $data[] = $row;
            }
            fclose($handle);
        }

        return $data;
    }
}