<?php

namespace App\Traits;

use App\Helpers\CsvHelper;
use App\Helpers\DepositCalculationHelper;
use App\Helpers\WithdrawCalculationHelper;
use App\Helpers\PrivateWithdrawCalculationHelper;


trait ProcessCommission
{
    protected $withdraw_record_array = [];
    protected $discount_record_array = [];

    public function processCommission(string $file_path)
    {
        if (!file_exists($file_path)) {
            echo "File does not exist";
        } else {
            $path_info_extension = pathinfo($file_path, PATHINFO_EXTENSION);
            if ($path_info_extension == 'csv') {
                //First get each to data array and then process each row
                $csv_helper = new CsvHelper();
                $datas      = $csv_helper->fileDataToArray($file_path);
                foreach ($datas as $data) {
                    $this->processEachRow($data);
                }
            } else {
                echo "Not Supported file type";
            }
        }
    }

    private function processEachRow($row)
    {
        $date                      = $row[0];
        $user_id                   = $row[1] ?? '';
        $code_for_user_interaction = $user_id . '_' . intval(date("Wo", strtotime($date)));
        $user_type                 = $row[2] ?? '';
        $operation_type            = $row[3] ?? '';
        $amount                    = $row[4] ?? 0;
        $currency                  = $row[5] ?? 0;
        $result                    = 0.00;

        if ($operation_type == 'deposit') {
            $deposit_helper = new DepositCalculationHelper();
            $result         = $deposit_helper->calculate($amount);
        } elseif ($operation_type == 'withdraw') {
            if (isset($this->withdraw_record_array[$code_for_user_interaction])) {
                $this->withdraw_record_array[$code_for_user_interaction]++;
            } else {
                $this->withdraw_record_array[$code_for_user_interaction] = 1;
            }

            if ($user_type == 'business') {
                $helper = new WithdrawCalculationHelper();
                $result = $helper->calculate($amount);
            } elseif ($user_type == 'private') {
                $discount_amount = config(
                    'commission.discount_amount_for_withdraw'
                );
                if (isset($this->discount_record_array[$code_for_user_interaction])) {
                    $discount_amount = $this->discount_record_array[$code_for_user_interaction];
                }


                if ($this->withdraw_record_array[$code_for_user_interaction] <= 3) {
                    $converted_discount = ($discount_amount * config(
                            'commission.currency_conversion_array'
                        )[$currency]);
                    $temp_amount        = $amount;
                    $amount             = $amount - $converted_discount;
                    if ($amount >= 0) {
                        $remaining_discount = 0;
                    } else {
                        $remaining_discount = $converted_discount - $temp_amount;
                    }

                    $this->discount_record_array[$code_for_user_interaction] =
                        ($remaining_discount / config(
                                'commission.currency_conversion_array'
                            )[$currency]);
                }
                $helper = new PrivateWithdrawCalculationHelper();
                $result = $helper->calculate($amount);
            }
        } else {
        }
        echo $result . '_' . $this->customRound($result) . "\n";
    }

    public function customRound(float $number): float
    {
        [$num, $decimals] = explode('.', $number);
        [$first, $second] = str_split($decimals);
        if (isset($second) && !empty($second)) {
            if ($first != 0) {
                $first++;
            }
        }
        return (float)($num . '.' . $first.$second);
    }
}
