<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Cargar datos iniciales en la base de datos de la aplicación.
     */
    public function run(): void
    {
        $this->call(SeguridadSeeder::class);
    }
}
