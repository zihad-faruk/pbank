<?php

namespace App\Helpers;

use App\Interfaces\Commission;

class PrivateWithdrawCalculationHelper implements Commission
{
    public function calculate(string $amount): float
    {
        if ($amount <= 0) {
            return 0.00;
        }
        return ($amount * config('commission.private_withdraw_charge') / 100);
    }
}