<?php

namespace App\Helpers;

use App\Interfaces\Commission;

use function PHPUnit\Framework\returnArgument;

class DepositCalculationHelper implements Commission
{
    public function calculate(string $amount): float
    {
        if($amount<=0){
            return 0.00;
        }
        return ($amount * config('commission.deposit_charge') / 100);
    }
}