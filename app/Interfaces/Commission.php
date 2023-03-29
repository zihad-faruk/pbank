<?php

namespace App\Interfaces;

interface Commission
{
    public function calculate(string $amount): float;
}
