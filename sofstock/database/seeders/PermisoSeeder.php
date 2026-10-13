<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            [
                'nombre' => 'ver_dashboard',
                'descripcion' => 'Acceder al panel principal de SofStock.',
            ],
            [
                'nombre' => 'ver_productos',
                'descripcion' => 'Consultar los productos registrados.',
            ],
            [
                'nombre' => 'crear_productos',
                'descripcion' => 'Registrar nuevos productos.',
            ],
            [
                'nombre' => 'editar_productos',
                'descripcion' => 'Modificar productos registrados.',
            ],
            [
                'nombre' => 'eliminar_productos',
                'descripcion' => 'Eliminar productos registrados.',
            ],
            [
                'nombre' => 'ver_categorias',
                'descripcion' => 'Consultar las categorías.',
            ],
            [
                'nombre' => 'gestionar_categorias',
                'descripcion' => 'Crear, modificar y eliminar categorías.',
            ],
            [
                'nombre' => 'ver_proveedores',
                'descripcion' => 'Consultar los proveedores.',
            ],
            [
                'nombre' => 'gestionar_proveedores',
                'descripcion' => 'Crear, modificar y eliminar proveedores.',
            ],
            [
                'nombre' => 'ver_facturas',
                'descripcion' => 'Consultar las facturas.',
            ],
            [
                'nombre' => 'gestionar_facturas',
                'descripcion' => 'Crear y administrar facturas y sus detalles.',
            ],
            [
                'nombre' => 'gestionar_usuarios',
                'descripcion' => 'Crear, consultar, modificar y eliminar usuarios.',
            ],
            [
                'nombre' => 'gestionar_roles',
                'descripcion' => 'Crear y administrar los roles del sistema.',
            ],
            [
                'nombre' => 'gestionar_permisos',
                'descripcion' => 'Consultar, crear y asignar permisos a los roles.',
            ],
            [
                'nombre' => 'ver_reportes',
                'descripcion' => 'Consultar los reportes del sistema.',
            ],
            [
                'nombre' => 'ver_auditoria',
                'descripcion' => 'Consultar el historial de acciones del sistema.',
            ],
        ];

        foreach ($permisos as $permiso) {
            PermisoSeeder::firstOrCreate(
                ['nombre' => $permiso['nombre']],
                ['descripcion' => $permiso['descripcion']]
            );
        }
    }
}
