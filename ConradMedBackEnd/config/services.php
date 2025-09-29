<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // 👇 Aquí agregas Wasapi
    'wasapi' => [
        'base_url'                => env('WASAPI_BASE_URL', 'https://api-ws.wasapi.io'),
        'token'                   => env('WASAPI_TOKEN'),
        'get_conversation_by_waid'=> env('WASAPI_GET_CONVERSATION_BY_WAID', 'api/v1/whatsapp-messages/{waid}'),
        'send_template'           => env('WASAPI_SEND_TEMPLATE', 'api/v1/whatsapp-messages/send-template'),
        'from_id'                 => env('WASAPI_FROM_ID', 15200),
        'template_id'             => env('WASAPI_TEMPLATE_ID', 'ed63d109-0a83-40d5-a9af-526de0459f27'),
    ],




];
