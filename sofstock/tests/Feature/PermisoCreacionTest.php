<?php

namespace Tests\Feature;

use App\Models\Permiso;
use Database\Seeders\PermisoSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PermisoCreacionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre_rol', 50);
            $table->text('descripcion')->nullable();
        });

        Schema::create('permisos', function (Blueprint $table) {
            $table->id('id_permiso');
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('permiso_rol', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_permiso');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('permiso_rol');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');

        parent::tearDown();
    }

    public function test_se_puede_abrir_y_crear_un_permiso_desde_el_panel(): void
    {
        \DB::table('roles')->insert([
            'nombre_rol' => 'Administrador',
        ]);

        $this->get(route('permisos.index'))
            ->assertOk()
            ->assertSee(route('permisos.create'))
            ->assertSee('+ Crear permiso');

        $this->get(route('permisos.create'))
            ->assertOk()
            ->assertSee('Crear permiso')
            ->assertSee('Define una acción que luego podrás habilitar para cada rol.')
            ->assertSee('Guardar permiso');

        $this->post(route('permisos.store'), [
            'nombre' => 'crear_productos',
            'descripcion' => 'Registrar nuevos productos.',
        ])->assertRedirect(route('permisos.index'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Permiso creado correctamente.');

        $this->assertDatabaseHas('permisos', [
            'nombre' => 'crear_productos',
            'descripcion' => 'Registrar nuevos productos.',
        ]);
    }

    public function test_no_se_puede_crear_un_permiso_con_nombre_duplicado(): void
    {
        Permiso::create([
            'nombre' => 'ver_productos',
            'descripcion' => 'Consultar productos.',
        ]);

        $this->from(route('permisos.create'))
            ->post(route('permisos.store'), [
                'nombre' => 'ver_productos',
                'descripcion' => 'Duplicado.',
            ])
            ->assertRedirect(route('permisos.create'))
            ->assertSessionHasErrors('nombre');

        $this->assertDatabaseCount('permisos', 1);
    }

    public function test_el_seeder_crea_permisos_sin_duplicarlos(): void
    {
        app(PermisoSeeder::class)->run();
        app(PermisoSeeder::class)->run();

        $this->assertDatabaseHas('permisos', [
            'nombre' => 'gestionar_permisos',
        ]);
        $this->assertDatabaseCount('permisos', 16);
    }
}
