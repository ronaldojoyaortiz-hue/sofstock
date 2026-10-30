<?php

namespace Database\Seeders;

use App\Models\User;
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
        // User::factory(10)->create();

        $this->call(SeguridadSeeder::class);

        User::factory()->create([
            'name' => 'Usuario de prueba',
            'email' => 'test@example.com',
        ]);
    }
}
