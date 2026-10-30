# SofStock

SofStock es una aplicación web para administrar productos, categorías,
proveedores, usuarios, roles y facturas.

## Requisitos

- PHP y Composer
- Node.js y npm
- MySQL

## Instalación

1. Instala las dependencias de PHP:

   ```bash
   composer install
   ```

2. Copia `.env.example` a `.env` y configura la conexión a la base de datos.

   En Windows:

   ```powershell
   Copy-Item .env.example .env
   ```

3. Genera la clave de la aplicación y ejecuta las migraciones:

   ```bash
   php artisan key:generate
   php artisan migrate
   ```

4. Crea y asigna los permisos de seguridad a los roles existentes:

   ```bash
   php artisan db:seed --class=SeguridadSeeder
   ```

5. Instala las dependencias del frontend e inicia Vite:

   ```bash
   npm install
   npm run dev
   ```

6. Inicia el servidor local:

   ```bash
   php artisan serve
   ```

Abre la dirección que muestra Artisan en el navegador. El idioma predeterminado
de la aplicación es español.
