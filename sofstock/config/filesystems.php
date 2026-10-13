<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Disco predeterminado del sistema de archivos
    |--------------------------------------------------------------------------
    |
    | Aquí puede especificar el disco predeterminado que utilizará el
    | framework. La aplicación puede guardar archivos en el disco "local" o
    | en diversos discos disponibles en la nube.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Discos del sistema de archivos
    |--------------------------------------------------------------------------
    |
    | Aquí puede configurar los discos que necesite, incluso varios para el
    | mismo controlador. Se incluyen ejemplos de la mayoría de los
    | controladores de almacenamiento compatibles.
    |
    | Controladores disponibles: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Enlaces simbólicos
    |--------------------------------------------------------------------------
    |
    | Aquí puede configurar los enlaces simbólicos que se crearán al ejecutar
    | el comando Artisan `storage:link`. Las claves del arreglo indican la
    | ubicación de los enlaces y sus valores, los destinos.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
