<?php

namespace App\Helpers;

use App\Interfaces\Commission;

class WithdrawCalculationHelper implements Commission
{
    public function calculate(string $amount): float
    {
        if ($amount <= 0) {
            return 0.00;
        }
        return ($amount * config('commission.business_withdraw_charge') / 100);
    }
}