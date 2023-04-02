<?php

namespace App\Http\Controllers;

use App\Services\CsvService;
use App\Traits\CalculateCommissionTrait;


class ProcessCommissionController extends Controller
{
    use CalculateCommissionTrait;

    public function processCommission(string $file_path)
    {
        if ( ! file_exists($file_path)) {
            echo "File does not exist\n";
        } else {
            $path_info_extension = pathinfo($file_path, PATHINFO_EXTENSION);
            if ($path_info_extension == 'csv') {
                $this->processFromCsv($file_path);
            } else {
                echo "Not Supported file type\n";
            }
        }
    }

    public function processFromCsv(string $file_path)
    {
        //First get each to data array and then process each row
        $csv_helper = new CsvService();
        $datas = $csv_helper->fileDataToArray($file_path);
        if ( ! empty($datas)) {
            foreach ($datas as $data) {
                echo $this->processEachRow($data) . "\n";
            }
        } else {
            echo "Empty File Data";
        }
    }


}
