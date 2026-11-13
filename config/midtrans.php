<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Server Key
    |--------------------------------------------------------------------------
    | Diambil dari Midtrans Dashboard → Settings → Access Keys
    */
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Client Key
    |--------------------------------------------------------------------------
    | Digunakan di sisi mobile (Midtrans SDK) untuk membuka payment widget.
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Sandbox / Production
    |--------------------------------------------------------------------------
    | true  = pakai sandbox (testing)
    | false = pakai production (live)
    */
    'is_sandbox' => env('MIDTRANS_IS_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | Sanitasi & 3DS
    |--------------------------------------------------------------------------
    */
    'is_sanitized' => true,
    'is_3ds'       => true,

];

