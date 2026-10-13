<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nombre de la aplicación
    |--------------------------------------------------------------------------
    |
    | Este valor es el nombre de la aplicación. El framework lo usará en las
    | notificaciones y en otros elementos de la interfaz que lo requieran.
    |
    */

    'name' => env('APP_NAME', 'SofStock'),

    /*
    |--------------------------------------------------------------------------
    | Entorno de la aplicación
    |--------------------------------------------------------------------------
    |
    | Este valor determina el entorno en el que se ejecuta la aplicación y
    | puede influir en la configuración de los servicios utilizados. Defínalo
    | en el archivo ".env".
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Modo de depuración de la aplicación
    |--------------------------------------------------------------------------
    |
    | Cuando el modo de depuración está activo, se muestran mensajes de error
    | detallados con sus trazas. Si está desactivado, se muestra una página
    | de error genérica.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL de la aplicación
    |--------------------------------------------------------------------------
    |
    | La consola utiliza esta URL para generar enlaces al usar Artisan.
    | Debe establecerse en la dirección raíz de la aplicación para que esté
    | disponible en los comandos de Artisan.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Zona horaria de la aplicación
    |--------------------------------------------------------------------------
    |
    | Aquí puede definir la zona horaria predeterminada de la aplicación,
    | utilizada por las funciones de fecha y hora de PHP. Por defecto se usa
    | "UTC", una opción adecuada para la mayoría de los casos.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Configuración del idioma de la aplicación
    |--------------------------------------------------------------------------
    |
    | El idioma de la aplicación es el que usarán por defecto los métodos de
    | traducción y localización de Laravel. Puede configurarse con cualquier
    | idioma para el que existan cadenas traducidas.
    |
    */

    'locale' => env('APP_LOCALE', 'es'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'es'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'es_ES'),

    /*
    |--------------------------------------------------------------------------
    | Clave de cifrado
    |--------------------------------------------------------------------------
    |
    | Laravel utiliza esta clave para cifrar información. Debe ser una cadena
    | aleatoria de 32 caracteres para proteger los valores cifrados. Genérela
    | antes de poner la aplicación en producción.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Controlador del modo de mantenimiento
    |--------------------------------------------------------------------------
    |
    | Estas opciones determinan el controlador que gestiona el modo de
    | mantenimiento de Laravel. El controlador "cache" permite administrarlo
    | desde varias máquinas.
    |
    | Controladores disponibles: "file", "cache", "array"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
