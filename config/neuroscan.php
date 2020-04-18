<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Login URL
    |--------------------------------------------------------------------------
    |
    | For make exports we need to get fresh SessionID first.
    |
    */

    'login_url'     => env('NEUROSCAN_LOGIN_URL'),

    'login'         => env('NEUROSCAN_LOGIN'),
    'password'      => env('NEUROSCAN_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Export URL
    |--------------------------------------------------------------------------
    */

    'export_url'    => env('NEUROSCAN_EXPORT_URL'),

    /*
    |--------------------------------------------------------------------------
    | Target class (model) to store data
    | (requires int() visitors nullable column in DB table)
    |--------------------------------------------------------------------------
    */

    'target_class'  => 'App\Models\Places'
];
