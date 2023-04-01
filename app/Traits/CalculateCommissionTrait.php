<?php

namespace App\Traits;

use App\Helpers\CsvHelper;
use App\Helpers\DepositCalculationHelper;
use App\Helpers\WithdrawCalculationHelper;
use App\Helpers\PrivateWithdrawCalculationHelper;
use App\Traits\CurrencyTrait;
use Illuminate\Support\Facades\Log;


trait CalculateCommissionTrait
{
    use CustomRoundTrait, CurrencyTrait;

    protected $withdraw_record_array = [];
    protected $discount_record_array = [];
    protected $currency_conversion_rates = [];

    public function setupConversionRates()
    {
        $conversion_rate_array = $this->fetchConversionRates();
        $this->currency_conversion_rates = $conversion_rate_array;
    }

    public function setupTestConversionRates()
    {
        $conversion_rate_array = [
            'EUR' => 1,
            'USD' => 1.1497,
            'JPY' => 129.53,
        ];
        $this->currency_conversion_rates = $conversion_rate_array;
    }

    private function processEachRow($row)
    {
        list($date, $user_id, $user_type, $operation_type, $amount, $currency) = $row;
        $code_for_user_interaction = $user_id . '_' . intval(date("Wo", strtotime($date)));
        /*$date = $row[0];
        $user_id = $row[1] ?? '';
        $code_for_user_interaction = $user_id . '_' . intval(date("Wo", strtotime($date)));
        $user_type = $row[2] ?? '';
        $operation_type = $row[3] ?? '';
        $amount = $row[4] ?? 0;
        $currency = $row[5] ?? 0;*/
        $result = 0.00;
        $currency_info = config('commission.currency')[$currency] ?? [];
        if (empty($currency_info)) {
            return "Invalid currency";
        }
        $fraction_mode = $currency_info['fraction_mode'] ?? '';

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
                //initialize conversion rate
                if (empty($this->currency_conversion_rates)) {
                    $this->setupConversionRates();
                }
                if ( ! isset($this->currency_conversion_rates[$currency])) {
                    return "Conversion Rate is not available";
                }
                $conversion_rate = $this->currency_conversion_rates[$currency];
                $discount_amount = config(
                    'commission.discount_amount_for_withdraw'
                );
                if (isset($this->discount_record_array[$code_for_user_interaction])) {
                    $discount_amount = $this->discount_record_array[$code_for_user_interaction];
                }
                if ($this->withdraw_record_array[$code_for_user_interaction] <= 3) {
                    $converted_discount = ($discount_amount * $conversion_rate);
                    $temp_amount = $amount;
                    $amount = $amount - $converted_discount;
                    if ($amount >= 0) {
                        $remaining_discount = 0;
                    } else {
                        $remaining_discount = $converted_discount - $temp_amount;
                    }

                    $this->discount_record_array[$code_for_user_interaction] =
                        ($remaining_discount / $conversion_rate);
                }
                $helper = new PrivateWithdrawCalculationHelper();
                $result = $helper->calculate($amount);
            }
        }

        if ($fraction_mode == 'whole') {
            return $this->customRound($result, 'whole');
        } else {
            return $this->customRoundAndFormatNumber($result, 'fraction');
        }
    }


}
