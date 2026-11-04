<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('roles')
            ->whereRaw('LOWER(nombre_rol) = ?', ['presidente'])
            ->exists();

        if (! $exists) {
            DB::table('roles')->insert([
                'nombre_rol' => 'presidente',
                'descripcion' => 'Presidente; puede asignarse a los usuarios de la cooperativa.',
            ]);
        }
    }

    public function down(): void
    {
        $presidentRole = DB::table('roles')
            ->whereRaw('LOWER(nombre_rol) = ?', ['presidente'])
            ->first(['id_rol']);

        if ($presidentRole && ! DB::table('usuarios')->where('id_rol', $presidentRole->id_rol)->exists()) {
            DB::table('roles')->where('id_rol', $presidentRole->id_rol)->delete();
        }
    }
};
