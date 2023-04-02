<?php

namespace App\Services;

use App\Interfaces\CommissionInterface;

class PrivateWithdrawCalculationService implements CommissionInterface
{
    public function calculate(string $amount): float
    {
        if ($amount <= 0) {
            return 0.00;
        }
        return ($amount * config('commission.private_withdraw_charge') / 100);
    }
}