<?php

use App\Models\Usuario;

return [

    /*
    |--------------------------------------------------------------------------
    | Valores predeterminados de autenticación
    |--------------------------------------------------------------------------
    |
    | Esta opción define el «guard» de autenticación y el «broker» para
    | restablecer contraseñas predeterminados de la aplicación. Puede cambiar
    | estos valores según sus necesidades.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Protecciones de autenticación
    |--------------------------------------------------------------------------
    |
    | Aquí puede definir las protecciones de autenticación de la aplicación.
    | La configuración predeterminada utiliza sesiones y el proveedor de
    | usuarios de Eloquent.
    |
    | Cada protección de autenticación tiene un proveedor de usuarios que
    | determina cómo se obtienen desde la base de datos u otro sistema de
    | almacenamiento. Normalmente se utiliza Eloquent.
    |
    | Compatible: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Proveedores de usuarios
    |--------------------------------------------------------------------------
    |
    | Cada protección de autenticación tiene un proveedor de usuarios que
    | determina cómo se obtienen desde la base de datos u otro sistema de
    | almacenamiento. Normalmente se utiliza Eloquent.
    |
    | Si tiene varias tablas o modelos de usuarios, puede configurar varios
    | proveedores para representarlos y asignarlos a las protecciones de
    | autenticación adicionales que defina.
    |
    | Compatibles: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', Usuario::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Restablecimiento de contraseñas
    |--------------------------------------------------------------------------
    |
    | Estas opciones definen el funcionamiento del restablecimiento de
    | contraseñas, incluida la tabla donde se guardan los códigos y el
    | proveedor utilizado para obtener los usuarios.
    |
    | El tiempo de expiración indica cuántos minutos es válido cada código
    | de restablecimiento. Esta medida de seguridad limita el tiempo para
    | adivinarlos. Puede cambiarlo según sus necesidades.
    |
    | La limitación indica cuántos segundos debe esperar una persona antes
    | de generar otros códigos. Así se evita que solicite demasiados en poco
    | tiempo.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tiempo de espera para confirmar la contraseña
    |--------------------------------------------------------------------------
    |
    | Aquí puede definir cuántos segundos tarda en vencer la confirmación de
    | contraseña y cuándo se solicita volver a escribirla. De forma
    | predeterminada, el tiempo de espera es de tres horas.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
