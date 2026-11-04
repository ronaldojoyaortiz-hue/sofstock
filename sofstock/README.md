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

7. Abre la dirección que muestra Artisan en el navegador. Si todavía no hay
   usuarios, usa el enlace **Crear la primera cuenta de administrador** en la
   pantalla de inicio de sesión. El formulario solo está disponible mientras no
   exista ninguna cuenta; esa primera cuenta recibe el rol de administrador y
   debe configurar una pregunta de seguridad al ingresar. Completa este paso
   antes de exponer la aplicación a otras personas.

Luego, las demás cuentas solo pueden ser creadas por un administrador desde la
sección de usuarios. El idioma predeterminado de la aplicación es español.
