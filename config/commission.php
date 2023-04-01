<?php

return [
    "deposit_charge" => 0.03,
    "business_withdraw_charge" => 0.5,
    "private_withdraw_charge" => 0.3,
    "discount_amount_for_withdraw" => 1000,
    "currency_exchange_rates_api" => "https://developers.paysera.com/tasks/api/currency-exchange-rates",
    "currency" => [
        "EUR" => [
            "fraction_mode" => "fraction"
        ],
        "USD" => [
            "fraction_mode" => "fraction"
        ],
        "JPY" => [
            "fraction_mode" => "whole"
        ]
    ]
];