<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform Fee Configuration
    |--------------------------------------------------------------------------
    |
    | Percentage deducted from gross ticket sales for platform maintenance,
    | hosting, payment processing overhead, and ticketing infrastructure.
    | Default is 5.0%.
    |
    */
    'platform_fee_percentage' => (float) env('PLATFORM_FEE_PERCENTAGE', 5.0),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('DEFAULT_CURRENCY', 'USD'),
];
