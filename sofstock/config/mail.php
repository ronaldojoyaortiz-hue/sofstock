<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Servicio de correo predeterminado
    |--------------------------------------------------------------------------
    |
    | Esta opción controla el servicio predeterminado para enviar correos,
    | salvo que se especifique otro al enviar un mensaje. Los demás servicios
    | se pueden configurar en el arreglo "mailers", que incluye ejemplos.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Configuración de servicios de correo
    |--------------------------------------------------------------------------
    |
    | Aquí puede configurar los servicios de correo de la aplicación y sus
    | opciones. Se incluyen varios ejemplos y puede agregar otros según sus
    | necesidades.
    |
    | Laravel admite varios controladores de transporte para enviar correos.
    | Abajo puede especificar cuáles utilizarán sus servicios y agregar
    | otros si es necesario.
    |
    | Disponibles: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Dirección global del remitente
    |--------------------------------------------------------------------------
    |
    | Puede enviar todos los correos de la aplicación desde una misma
    | dirección. Aquí puede especificar el nombre y la dirección que se
    | utilizarán en todos los mensajes enviados.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'SofStock')),
    ],

];
