<?php

namespace App\Services;

use App\Interfaces\CommissionInterface;

class BusinessWithdrawCalculationService implements CommissionInterface
{
    public function calculate(string $amount): float
    {
        if ($amount <= 0) {
            return 0.00;
        }
        return ($amount * config('commission.business_withdraw_charge') / 100);
    }
}