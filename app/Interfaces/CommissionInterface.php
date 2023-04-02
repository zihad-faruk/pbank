<?php

namespace App\Interfaces;

interface CommissionInterface
{
    public function calculate(string $amount): float;
}
