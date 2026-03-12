<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Privacy / Confidentiality Settings
    |--------------------------------------------------------------------------
    |
    | - anonymization_salt: Used to create stable anonymous identifiers for
    |   analytics logs (never expose primary keys to shared logs).
    | - force_https: Recommended for production to ensure data-in-transit is
    |   encrypted (TLS). Keep off for local dev.
    |
    */

    'anonymization_salt' => env('ANONYMIZATION_SALT', env('APP_KEY')),

    'force_https' => (bool) env('FORCE_HTTPS', false),
];

