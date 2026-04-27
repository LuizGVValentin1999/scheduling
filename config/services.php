<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'ses'      => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // -------------------------------------------------------
    // Google — Social Login + Calendar API
    // Configurar em: https://console.cloud.google.com
    // Escopos necessários: openid, profile, email, calendar
    // -------------------------------------------------------
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT'),
    ],

    // -------------------------------------------------------
    // Microsoft Azure — Social Login + Outlook Calendar
    // Configurar em: https://portal.azure.com
    // Escopos: openid, profile, email, offline_access, Calendars.ReadWrite
    // -------------------------------------------------------
    'azure' => [
        'client_id'     => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect'      => env('MICROSOFT_REDIRECT'),
        'tenant'        => env('MICROSOFT_TENANT_ID', 'common'),
    ],

];
