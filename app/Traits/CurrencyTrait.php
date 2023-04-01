<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait CurrencyTrait
{
    public function fetchConversionRates()
    {
        $api_url = config('commission.currency_exchange_rates_api');
        $response = file_get_contents($api_url);
        Log::info("CURRENCY EXCHANGE API RESPONSE:" . $response);
        if ( ! empty($response)) {
            $response = json_decode($response, true);
            return $response['rates'] ?? [];
        }
        return [];
    }

}