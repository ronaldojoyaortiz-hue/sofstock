<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Controlador de sesión predeterminado
    |--------------------------------------------------------------------------
    |
    | Esta opción determina el controlador de sesión predeterminado para las
    | solicitudes recibidas. Laravel admite diversas opciones para guardar
    | las sesiones; la base de datos es una buena opción inicial.
    |
    | Disponibles: "file", "cookie", "database", "memcached",
    |            "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Duración de la sesión
    |--------------------------------------------------------------------------
    |
    | Aquí puede definir cuántos minutos puede permanecer inactiva la sesión
    | antes de vencer. Para que venza al cerrar el navegador, utilice la
    | opción de configuración expire_on_close.
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Cifrado de la sesión
    |--------------------------------------------------------------------------
    |
    | Esta opción permite cifrar los datos de la sesión antes de guardarlos.
    | Laravel realiza el cifrado automáticamente y la sesión puede utilizarse
    | con normalidad.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Ubicación de los archivos de sesión
    |--------------------------------------------------------------------------
    |
    | Si se utiliza el controlador de sesión "file", los archivos se guardan
    | en el disco. Aquí se define la ubicación predeterminada, que puede
    | cambiarse por otra.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Conexión de base de datos para sesiones
    |--------------------------------------------------------------------------
    |
    | Al usar los controladores de sesión "database" o "redis", puede
    | especificar la conexión que administrará las sesiones. Debe coincidir
    | con una conexión definida en la configuración de la base de datos.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Tabla de base de datos para sesiones
    |--------------------------------------------------------------------------
    |
    | Al usar el controlador "database", puede especificar la tabla donde se
    | guardarán las sesiones. Se proporciona una tabla predeterminada, que
    | puede cambiar si lo necesita.
    |
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Almacén de caché para sesiones
    |--------------------------------------------------------------------------
    |
    | Al usar un controlador de sesión basado en caché, puede definir el
    | almacén donde se guardarán los datos entre solicitudes. Debe coincidir
    | con uno de los almacenes de caché configurados.
    |
    | Afecta a: "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Probabilidad de limpieza de sesiones
    |--------------------------------------------------------------------------
    |
    | Algunos controladores deben limpiar manualmente el almacenamiento para
    | eliminar sesiones antiguas. Aquí se define la probabilidad de que la
    | limpieza ocurra en cada solicitud; por defecto es de 2 en 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Nombre de la cookie de sesión
    |--------------------------------------------------------------------------
    |
    | Aquí puede cambiar el nombre de la cookie de sesión que crea el
    | framework. Normalmente no hace falta cambiarlo, ya que esto no mejora
    | la seguridad de forma significativa.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'sofstock')).'-session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Ruta de la cookie de sesión
    |--------------------------------------------------------------------------
    |
    | La ruta de la cookie determina en qué rutas estará disponible.
    | Normalmente será la ruta raíz de la aplicación, pero puede cambiarla
    | cuando sea necesario.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Dominio de la cookie de sesión
    |--------------------------------------------------------------------------
    |
    | Este valor determina en qué dominio y subdominios estará disponible la
    | cookie de sesión. Por defecto, estará disponible en el dominio raíz,
    | sin subdominios. Normalmente no es necesario cambiarlo.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Cookies solo por HTTPS
    |--------------------------------------------------------------------------
    |
    | Al activar esta opción, las cookies de sesión solo se enviarán al
    | servidor si el navegador utiliza una conexión HTTPS. Así no se
    | transmitirán cuando no pueda hacerse de forma segura.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | Acceso solo por HTTP
    |--------------------------------------------------------------------------
    |
    | Al activar esta opción, JavaScript no podrá acceder al valor de la
    | cookie y solo estará disponible mediante el protocolo HTTP. No se
    | recomienda desactivar esta opción.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Cookies del mismo sitio
    |--------------------------------------------------------------------------
    |
    | Esta opción determina cómo se comportan las cookies en solicitudes
    | entre sitios y puede ayudar a mitigar ataques CSRF. Por defecto, el
    | valor "lax" permite solicitudes seguras entre sitios.
    |
    | See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#samesitesamesite-value
    |
    | Disponibles: "lax", "strict", "none", null
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Cookies particionadas
    |--------------------------------------------------------------------------
    |
    | Al activar esta opción, la cookie queda vinculada al sitio de nivel
    | superior en contextos entre sitios. El navegador acepta cookies
    | particionadas cuando se marcan como "secure" y Same-Site es "none".
    |
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

    /*
    |--------------------------------------------------------------------------
    | Serialización de la sesión
    |--------------------------------------------------------------------------
    |
    | Este valor controla la serialización de los datos de sesión, que por
    | defecto utiliza JSON. El valor "php" permite guardar objetos PHP en la
    | sesión, pero puede hacer que la aplicación sea vulnerable a ataques de
    | serialización si se filtra APP_KEY.
    |
    | Disponibles: "json", "php"
    |
    */

    'serialization' => 'json',

];
