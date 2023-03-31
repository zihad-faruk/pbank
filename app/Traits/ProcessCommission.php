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
        if ( ! file_exists($file_path)) {
            echo "File does not exist";
        } else {
            $path_info_extension = pathinfo($file_path, PATHINFO_EXTENSION);
            if ($path_info_extension == 'csv') {
                //First get each to data array and then process each row
                $csv_helper = new CsvHelper();
                $datas = $csv_helper->fileDataToArray($file_path);
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
        $date = $row[0];
        $user_id = $row[1] ?? '';
        $code_for_user_interaction = $user_id . '_' . intval(date("Wo", strtotime($date)));
        $user_type = $row[2] ?? '';
        $operation_type = $row[3] ?? '';
        $amount = $row[4] ?? 0;
        $currency = $row[5] ?? 0;
        $result = 0.00;

        if ($operation_type == 'deposit') {
            $deposit_helper = new DepositCalculationHelper();
            $result = $deposit_helper->calculate($amount);
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
                    $temp_amount = $amount;
                    $amount = $amount - $converted_discount;
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
        if ($currency == 'JPY') {
            echo $this->customRound($result, 'whole') . "\n";
        } else {
            echo $this->customRoundAndFormatNumber($result, 'fraction') . "\n";
        }
    }

    /**
     * Round the number according to custom rule.
     *
     * @param float $number
     * @param string $mode
     * @return float
     */
    public function customRound(float $number, string $mode = 'fraction'): float
    {
        //For Special Cases make the whole number round
        if ($mode == "whole") {
            return ceil($number);
        }
        //If number=0, return the rounded number
        if (empty($number)) {
            return $number;
        }
        $decimal_array = explode('.', $number);
        $whole_part = $decimal_array[0] ?? 0;
        $fractional_part = $decimal_array[1] ?? 0;


        if (isset($fractional_part) && ! empty($fractional_part)) {
            $result = $this->calculateFractionDigits($whole_part, $fractional_part);
        } else {
            $result = $whole_part;
        }
        return $result;
    }

    /**
     * Round the number according to custom rule and format it to 2 decimal point.
     *
     * @param float $number
     * @param string $mode
     * @return string
     */
    public function customRoundAndFormatNumber(float $number, string $mode = 'fraction'): string
    {
        return number_format($this->customRound($number, $mode), 2);
    }

    /**
     * The custom logic to calculate fractional part.
     *
     * @param float $whole_part
     * @param float $fractional_part
     * @return float
     */
    public function calculateFractionDigits(float $whole_part, float $fractional_part): float
    {
        //So that minimum 3 elements are available for calculation
        $min_required_elements = 3;
        $defElements = array_fill(0, $min_required_elements, null);
        list($first_digit, $second_digit, $third_digit) = array_replace($defElements, str_split($fractional_part));
        $carry = 0;
        if (isset($third_digit) && ! empty($third_digit)) {
            $carry++;
        }
        if (isset($second_digit) && ! empty($second_digit)) {
            if ($carry != 0) {
                $second_digit++;
                if ($second_digit > 9) {
                    $second_digit = 0;
                }
            }
            $carry++;
        }
        if (isset($first_digit) && ! empty($first_digit)) {
            if ($carry != 0) {
                $first_digit++;
            }
            if ($first_digit > 9) {
                $first_digit = 0;
                $whole_part++;
            }
        }
        return (float)$whole_part . '.' . $first_digit . $second_digit;
    }
}
