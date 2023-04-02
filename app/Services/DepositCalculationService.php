<?php

namespace App\Services;

use App\Interfaces\CommissionInterface;

use function PHPUnit\Framework\returnArgument;

class DepositCalculationService implements CommissionInterface
{
    public function calculate(string $amount): float
    {
        if($amount<=0){
            return 0.00;
        }
        return ($amount * config('commission.deposit_charge') / 100);
    }
}