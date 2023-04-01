<?php

namespace App\Traits;

use App\Helpers\DepositCalculationHelper;
use App\Helpers\BusinessWithdrawCalculationHelper;
use App\Helpers\PrivateWithdrawCalculationHelper;

trait CalculateCommissionTrait
{
    use CustomRoundTrait, CurrencyTrait;

    protected $withdraw_records = [];
    protected $discount_records = [];
    protected $currency_conversion_rates = [];

    public function setupConversionRates(): void
    {
        $conversion_rate_array = $this->fetchConversionRates();
        $this->currency_conversion_rates = $conversion_rate_array;
    }

    //Setup conversion rates for testing
    public function setupTestConversionRates(): void
    {
        $conversion_rate_array = [
            'EUR' => 1,
            'USD' => 1.1497,
            'JPY' => 129.53,
        ];
        $this->currency_conversion_rates = $conversion_rate_array;
    }

    public function processEachRow(array $row): string
    {
        $date = $row[0] ?? '';
        $user_id = $row[1] ?? '';
        $user_type = $row[2] ?? '';
        $operation_type = $row[3] ?? '';
        $amount = $row[4] ?? 0;
        $currency = $row[5] ?? 0;
        if (empty($date) || $user_id == '' || empty($user_type) || empty($operation_type) || empty($amount) || empty($currency)) {
            return "necessary fields missing";
        }
        $code_for_user_interaction = $user_id . '_' . intval(date("Wo", strtotime($date)));

        $currency_info = config('commission.currency')[$currency] ?? [];
        if (empty($currency_info)) {
            return "Invalid currency";
        }
        $fraction_mode = $currency_info['fraction_mode'] ?? '';
        return $this->calculateCommissionAmount(
            $code_for_user_interaction,
            $operation_type,
            $user_type,
            $amount,
            $currency,
            $fraction_mode
        );
    }

    public function calculateCommissionAmount(
        string $code_for_user_interaction,
        string $operation_type,
        string $user_type,
        float $amount,
        string $currency,
        string $fraction_mode
    ): string {
        $result = 0.00;
        if ($operation_type == 'deposit') {
            $deposit_helper = new DepositCalculationHelper();
            $result = $deposit_helper->calculate($amount);
        } elseif ($operation_type == 'withdraw') {
            $result = $this->calculateWithdrawCommissionAmount(
                $code_for_user_interaction,
                $user_type,
                $amount,
                $currency
            );
        }

        /***
         * For JPY , no decimals are used , so fraction_mode = whole
         * For others, upto 2 decimal places , so fraction_mode = fraction
         ***/
        if ($fraction_mode == 'whole') {
            return $this->customRound($result, 'whole');
        } else {
            return $this->customRoundAndFormatNumber($result);
        }
    }

    public function calculateWithdrawCommissionAmount(
        string $code_for_user_interaction,
        string $user_type,
        float $amount,
        string $currency
    ): string {
        $result = 0.00;
        //To check and keep count of withdraw operation of the same user within the same week
        if (isset($this->withdraw_records[$code_for_user_interaction])) {
            $this->withdraw_records[$code_for_user_interaction]++;
        } else {
            $this->withdraw_records[$code_for_user_interaction] = 1;
        }

        if ($user_type == 'business') {
            $helper = new BusinessWithdrawCalculationHelper();
            $result = $helper->calculate($amount);
        } elseif ($user_type == 'private') {
            $amount = $this->calculatePrivateWithdrawCommissionAmount(
                $code_for_user_interaction,
                $amount,
                $currency
            );
            $helper = new PrivateWithdrawCalculationHelper();
            $result = $helper->calculate($amount);
        }

        return $result;
    }

    public function calculatePrivateWithdrawCommissionAmount(
        string $code_for_user_interaction,
        float $amount,
        string $currency
    ): string {
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
        /***
         * If this user already used discount the same week or not , is yes , use the remaining
         * discount
         ***/
        if (isset($this->discount_records[$code_for_user_interaction])) {
            $discount_amount = $this->discount_records[$code_for_user_interaction];
        }
        // If the user has less than 3 operations in the same week
        if ($this->withdraw_records[$code_for_user_interaction] <= 3) {
            $converted_discount = ($discount_amount * $conversion_rate);
            $temp_amount = $amount;
            $amount = $amount - $converted_discount;
            if ($amount >= 0) {
                $remaining_discount = 0;
            } else {
                $remaining_discount = $converted_discount - $temp_amount;
            }

            //Update discount records
            $this->discount_records[$code_for_user_interaction] =
                ($remaining_discount / $conversion_rate);
        }
        return $amount;
    }
}
