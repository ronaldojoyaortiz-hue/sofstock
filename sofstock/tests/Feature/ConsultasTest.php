<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedores;
use App\Models\Rol as RolSistema;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ConsultasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('roles', function (Blueprint $table): void {
            $table->id('id_rol');
            $table->string('nombre_rol');
            $table->text('descripcion')->nullable();
        });
        Schema::create('usuarios', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('correo');
            $table->string('contrasena');
            $table->string('contrasena_cifrada')->nullable();
            $table->string('pregunta_seguridad')->nullable();
            $table->string('respuesta_seguridad')->nullable();
            $table->unsignedBigInteger('id_rol');
            $table->boolean('activo')->default(true);
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('genero')->nullable();
            $table->string('genero_detalle')->nullable();
            $table->string('tipo_documento')->nullable();
            $table->string('documento')->nullable();
            $table->timestamp('fecha_registro')->nullable();
            $table->timestamp('fecha_actualizacion')->nullable();
            $table->string('estado_aprobacion')->nullable();
        });
        Schema::create('permission_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });
        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });
        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });
        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });
        Schema::create('categorias', function (Blueprint $table): void {
            $table->id('id_categoria');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
        });
        Schema::create('proveedores', function (Blueprint $table): void {
            $table->id('id_proveedor');
            $table->string('razon_social');
            $table->string('nit')->nullable();
            $table->string('contacto_nombre')->nullable();
            $table->string('correo')->nullable();
            $table->string('telefono')->nullable();
            $table->text('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_registro')->nullable();
        });
        Schema::create('productos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('marca')->nullable();
            $table->unsignedBigInteger('id_categoria')->nullable();
            $table->unsignedBigInteger('id_proveedor')->nullable();
            $table->decimal('stock_actual', 12, 2)->default(0);
            $table->decimal('stock_minimo', 12, 2)->default(0);
            $table->string('unidad_base')->nullable();
            $table->decimal('precio_base', 12, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->date('fecha_caducidad')->nullable();
            $table->timestamp('fecha_registro')->nullable();
            $table->timestamp('fecha_actualizacion')->nullable();
        });
        Schema::create('auditorias', function (Blueprint $table): void {
            $table->id('id_auditoria');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('accion');
            $table->string('modulo');
            $table->text('descripcion');
            $table->string('tabla_afectada')->nullable();
            $table->string('id_registro_afectado')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('motivo')->nullable();
            $table->string('direccion_ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('auditoria_usuario_oculta', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_auditoria');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamp('created_at')->nullable();
            $table->primary(['id_auditoria', 'id_usuario']);
        });
        Schema::create('facturas', function (Blueprint $table): void {
            $table->id('id_factura');
            $table->string('numero');
            $table->string('cliente_nombre');
            $table->string('cliente_documento')->nullable();
            $table->date('fecha');
            $table->decimal('total', 14, 2)->default(0);
            $table->string('estado');
            $table->timestamps();
        });
        Schema::create('presupuestos_mensuales', function (Blueprint $table): void {
            $table->id();
            $table->date('mes');
            $table->decimal('presupuesto_inicial', 14, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('historial_presupuestos', function (Blueprint $table): void {
            $table->id();
            $table->date('mes');
            $table->decimal('presupuesto_anterior', 14, 2)->nullable();
            $table->decimal('presupuesto_nuevo', 14, 2);
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->timestamps();
        });
        Schema::create('egresos_mensuales', function (Blueprint $table): void {
            $table->id();
            $table->date('fecha');
            $table->string('tipo');
            $table->string('descripcion');
            $table->decimal('monto', 14, 2);
            $table->unsignedBigInteger('id_producto')->nullable();
            $table->unsignedBigInteger('id_proveedor')->nullable();
            $table->timestamps();
        });
        Schema::create('mermas_productos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('id_producto');
            $table->date('fecha');
            $table->decimal('cantidad', 14, 2);
            $table->decimal('valor_perdida', 14, 2);
            $table->string('motivo');
            $table->timestamps();
        });
        Schema::create('reportes_generados', function (Blueprint $table): void {
            $table->id();
            $table->string('tipo_reporte');
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->json('datos')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'productos',
            'auditorias',
            'auditoria_usuario_oculta',
            'reportes_generados',
            'mermas_productos',
            'egresos_mensuales',
            'historial_presupuestos',
            'presupuestos_mensuales',
            'facturas',
            'proveedores',
            'categorias',
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
            'permissions',
            'permission_roles',
            'usuarios',
            'roles',
        ] as $tabla) {
            Schema::dropIfExists($tabla);
        }

        parent::tearDown();
    }

    public function test_consultas_only_shows_modules_the_user_can_read(): void
    {
        $rolSistema = $this->crearRolSistema('Consulta de productos');
        $rolPermisos = Role::create(['name' => 'consulta-productos', 'guard_name' => 'web']);
        $rolPermisos->givePermissionTo([
            Permission::create(['name' => 'acceder productos', 'guard_name' => 'web']),
            Permission::create(['name' => 'ver productos', 'guard_name' => 'web']),
        ]);
        $usuario = $this->crearUsuario('Luz', 'luz@example.test', $rolSistema->id_rol);
        $usuario->assignRole($rolPermisos);

        $this->actingAs($usuario)
            ->get(route('consultas.index'))
            ->assertOk()
            ->assertSee('Inventario - Productos')
            ->assertDontSee('Categorías');

        $this->actingAs($usuario)
            ->get(route('consultas.categorias.index'))
            ->assertForbidden();
    }

    public function test_product_search_and_detail_show_registered_information(): void
    {
        $usuario = $this->crearAdministrador();
        $categoria = Categoria::withoutEvents(fn () => Categoria::query()->create([
            'nombre' => 'Ferretería',
            'descripcion' => 'Herramientas y fijaciones',
            'activa' => true,
        ]));
        $proveedor = Proveedores::withoutEvents(fn () => Proveedores::query()->create([
            'razon_social' => 'Suministros del Norte',
            'nit' => '900123456',
            'correo' => 'ventas@example.test',
        ]));
        $producto = Producto::withoutEvents(fn () => Producto::query()->create([
            'nombre' => 'Tornillo galvanizado',
            'descripcion' => 'Tornillo de acero para madera',
            'id_categoria' => $categoria->id_categoria,
            'id_proveedor' => $proveedor->id_proveedor,
            'stock_actual' => 12,
            'stock_minimo' => 5,
            'unidad_base' => 'unidad',
            'precio_base' => 250,
            'activo' => true,
        ]));
        Producto::withoutEvents(fn () => Producto::query()->create(['nombre' => 'Taladro eléctrico']));

        $this->actingAs($usuario)
            ->get(route('consultas.productos.index', ['q' => 'Tornillo']))
            ->assertOk()
            ->assertSee('Tornillo galvanizado')
            ->assertSee('Suministros del Norte')
            ->assertDontSee('Taladro eléctrico');

        $this->actingAs($usuario)
            ->get(route('consultas.productos.detalle', $producto->id))
            ->assertOk()
            ->assertSee('Tornillo de acero para madera')
            ->assertSee('Ferretería')
            ->assertSee('Suministros del Norte')
            ->assertSee('Existencias mínimas');
    }

    public function test_user_detail_does_not_expose_password_or_security_answers(): void
    {
        $administrador = $this->crearAdministrador();
        $rol = $this->crearRolSistema('Empleado');
        $consultado = $this->crearUsuario('Andrés', 'andres@example.test', $rol->id_rol);
        Usuario::withoutEvents(fn () => $consultado->forceFill([
            'contrasena' => 'SECRETO_CONTRASENA_DE_PRUEBA',
            'contrasena_cifrada' => 'SECRETO_CIFRADO_DE_PRUEBA',
            'pregunta_seguridad' => 'Pregunta confidencial',
            'respuesta_seguridad' => 'RESPUESTA_PRIVADA_DE_PRUEBA',
        ])->save());

        $this->actingAs($administrador)
            ->get(route('consultas.usuarios.detalle', $consultado->id))
            ->assertOk()
            ->assertSee('andres@example.test')
            ->assertDontSee('SECRETO_CONTRASENA_DE_PRUEBA')
            ->assertDontSee('SECRETO_CIFRADO_DE_PRUEBA')
            ->assertDontSee('Pregunta confidencial')
            ->assertDontSee('RESPUESTA_PRIVADA_DE_PRUEBA');
    }

    public function test_financial_summary_distinguishes_paid_income_from_net_profit(): void
    {
        $usuario = $this->crearAdministrador();
        DB::table('facturas')->insert([
            [
                'numero' => 'F-001',
                'cliente_nombre' => 'Cliente de prueba',
                'fecha' => '2026-03-05',
                'total' => 1000,
                'estado' => 'pagada',
            ],
            [
                'numero' => 'F-002',
                'cliente_nombre' => 'Cliente pendiente',
                'fecha' => '2026-03-06',
                'total' => 200,
                'estado' => 'borrador',
            ],
        ]);
        DB::table('egresos_mensuales')->insert([
            [
                'fecha' => '2026-03-07',
                'tipo' => 'inversion_producto',
                'descripcion' => 'Compra de productos',
                'monto' => 100,
            ],
            [
                'fecha' => '2026-03-08',
                'tipo' => 'gasto',
                'descripcion' => 'Gasto operativo',
                'monto' => 50,
            ],
        ]);
        DB::table('mermas_productos')->insert([
            [
                'id_producto' => 1,
                'fecha' => '2026-03-09',
                'cantidad' => 1,
                'valor_perdida' => 25,
                'motivo' => 'Producto dañado',
            ],
        ]);

        $this->actingAs($usuario)
            ->get(route('consultas.reportes.index', [
                'fecha_desde' => '2026-03-01',
                'fecha_hasta' => '2026-03-31',
            ]))
            ->assertOk()
            ->assertSee('$1.000,00')
            ->assertSee('$825,00')
            ->assertSee('$175,00')
            ->assertSee('ventas pagadas menos inversiones, gastos y pérdidas por productos dañados');
    }

    public function test_consultation_shows_results_only_after_searching_one_criterion(): void
    {
        $usuario = $this->crearAdministrador();
        $this->actingAs($usuario)
            ->get(route('consultas.index'))
            ->assertOk()
            ->assertSee('Selecciona un módulo')
            ->assertSee('Inventario - Productos');

        $this->actingAs($usuario)
            ->get(route('consultas.index', ['modulo' => 'productos']))
            ->assertRedirect(route('consultas.productos.index'));

        $categoria = Categoria::withoutEvents(fn () => Categoria::query()->create([
            'nombre' => 'Herramientas',
            'activa' => true,
        ]));
        Producto::withoutEvents(fn () => Producto::query()->create([
            'nombre' => 'Taladro de prueba',
            'id_categoria' => $categoria->id_categoria,
            'stock_actual' => 4,
            'stock_minimo' => 2,
            'fecha_caducidad' => '2027-05-01',
            'activo' => true,
        ]));

        $this->actingAs($usuario)
            ->get(route('consultas.productos.index'))
            ->assertOk()
            ->assertSee('Selecciona un criterio')
            ->assertDontSee('Taladro de prueba');

        $this->actingAs($usuario)
            ->get(route('consultas.productos.index', [
                'criterio' => 'q',
                'q' => 'Taladro',
                'categoria' => $categoria->id_categoria,
            ]))
            ->assertOk()
            ->assertSee('Taladro de prueba');

        $this->actingAs($usuario)
            ->get(route('consultas.productos.index', [
                'criterio' => 'fecha_caducidad',
                'fecha_caducidad' => '2027-05-01',
            ]))
            ->assertOk()
            ->assertSee('Taladro de prueba')
            ->assertSee('01/05/2027')
            ->assertSee('Elegir fecha');

        $this->actingAs($usuario)
            ->get(route('consultas.productos.index', [
                'criterio' => 'fecha_caducidad',
                'fecha_caducidad' => '2027-05-02',
            ]))
            ->assertOk()
            ->assertDontSee('Taladro de prueba')
            ->assertSee('No se encontraron registros');

        $this->actingAs($usuario)
            ->get(route('consultas.facturas.index'))
            ->assertOk()
            ->assertSee('Documento del cliente')
            ->assertDontSee('Número de factura');
    }

    public function test_upper_bound_criteria_can_be_searched_without_a_lower_bound(): void
    {
        $usuario = $this->crearAdministrador();

        $this->actingAs($usuario)
            ->get(route('consultas.productos.index', [
                'criterio' => 'stock_max',
                'stock_max' => 10,
            ]))
            ->assertOk();

        $this->actingAs($usuario)
            ->get(route('consultas.facturas.index', [
                'criterio' => 'precio_max',
                'precio_max' => 1000,
            ]))
            ->assertOk();

        $this->actingAs($usuario)
            ->get(route('consultas.reportes.index', [
                'criterio' => 'fecha_hasta',
                'fecha_hasta' => '2026-03-31',
            ]))
            ->assertOk();

        $this->actingAs($usuario)
            ->get(route('consultas.auditorias.index', [
                'criterio' => 'fecha_hasta',
                'fecha_hasta' => '2026-03-31',
            ]))
            ->assertOk();
    }

    public function test_monthly_budget_details_include_recorded_changes(): void
    {
        $usuario = $this->crearAdministrador();
        $presupuestoId = DB::table('presupuestos_mensuales')->insertGetId([
            'mes' => '2026-03-01',
            'presupuesto_inicial' => 500,
        ]);
        DB::table('historial_presupuestos')->insert([
            'mes' => '2026-03-01',
            'presupuesto_anterior' => 400,
            'presupuesto_nuevo' => 500,
            'id_usuario' => $usuario->id,
        ]);

        $this->actingAs($usuario)
            ->get(route('consultas.reportes.index', ['seccion' => 'presupuestos']))
            ->assertOk()
            ->assertSee('Cambios registrados')
            ->assertSee('1');

        $this->actingAs($usuario)
            ->get(route('consultas.reportes.registro', ['presupuestos', $presupuestoId]))
            ->assertOk()
            ->assertSee('Cambios de presupuesto')
            ->assertSee('400,00')
            ->assertSee('500,00')
            ->assertSee('Administradora Prueba');
    }

    public function test_audit_details_hide_credentials_saved_in_legacy_audit_data(): void
    {
        $usuario = $this->crearAdministrador();
        $auditoria = Auditoria::query()->create([
            'id_usuario' => $usuario->id,
            'accion' => 'ACTUALIZAR',
            'modulo' => 'Usuarios',
            'descripcion' => 'Se actualizó el usuario de prueba.',
            'datos_nuevos' => [
                'nombre' => 'Persona de prueba',
                'contrasena' => 'SECRETO_DE_AUDITORIA',
                'detalles' => ['access_token' => 'TOKEN_DE_AUDITORIA'],
            ],
            'created_at' => now(),
        ]);

        $this->actingAs($usuario)
            ->get(route('consultas.auditorias.detalle', $auditoria->id_auditoria))
            ->assertOk()
            ->assertSee('Persona de prueba')
            ->assertDontSee('SECRETO_DE_AUDITORIA')
            ->assertDontSee('TOKEN_DE_AUDITORIA');
    }

    private function crearAdministrador(): Usuario
    {
        $rol = $this->crearRolSistema('Administrador');

        return $this->crearUsuario('Administradora', 'admin@example.test', $rol->id_rol);
    }

    private function crearRolSistema(string $nombre): RolSistema
    {
        return RolSistema::withoutEvents(fn () => RolSistema::query()->create([
            'nombre_rol' => $nombre,
        ]));
    }

    private function crearUsuario(string $nombre, string $correo, int $rolId): Usuario
    {
        return Usuario::withoutEvents(fn () => Usuario::query()->create([
            'nombre' => $nombre,
            'apellido' => 'Prueba',
            'correo' => $correo,
            'contrasena' => Hash::make('contrasena-de-prueba'),
            'pregunta_seguridad' => 'Pregunta de prueba',
            'respuesta_seguridad' => Hash::make('respuesta-de-prueba'),
            'id_rol' => $rolId,
            'activo' => true,
        ]));
    }
}
