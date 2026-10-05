<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sofstock - Panel</title>
</head>
<body>
    <h1>Panel de administración</h1>
    <p>Selecciona la sección que deseas administrar:</p>

    <ul>
        <li><a href="{{ route('categorias.index') }}">Categorías</a></li>
        <li><a href="{{ route('roles.index') }}">Roles</a></li>
        <li><a href="{{ route('productos.index') }}">Productos</a></li>
        <li><a href="{{ route('facturas.index') }}">Facturas</a></li>
        <li><a href="{{ route('detallefacturas.index') }}">Detalles de factura</a></li>
        <li><a href="{{ route('proveedores.index') }}">Proveedores</a></li>
    </ul>
</body>
</html>
