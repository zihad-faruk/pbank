<?php

namespace Tests\Feature;

use App\Helpers\CsvHelper;
use App\Http\Controllers\ProcessCommissionController;
use App\Traits\CalculateCommissionTrait;
use Tests\TestCase;

class CommissionTest extends TestCase
{
    use CalculateCommissionTrait;

    public function test_console_command_for_calculation(): void
    {
        $this->artisan('calculate:commission input.csv -P')
            ->assertExitCode(0);
    }

    public function test_commission_calculation(): void
    {
        $test_data_file_url = public_path("Test/test_data.csv");
        $output_data_file_url = public_path("Test/test_output_data.csv");
        $csv_helper = new CsvHelper();
        $test_datas = $csv_helper->fileDataToArray($test_data_file_url);
        $output_datas = $csv_helper->fileDataToArray($output_data_file_url);
        $data_counter = 0;

        //Setting conversion test conversion rates
        $this->setupTestConversionRates();
        foreach ($test_datas as $test_data) {
            $this->assertEquals($output_datas[$data_counter][0], $this->processEachRow($test_data));
            $data_counter++;
        }
    }
}
