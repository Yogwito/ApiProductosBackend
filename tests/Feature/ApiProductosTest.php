<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiProductosTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        $this->createSchema();
    }

    public function test_login_returns_token_and_user_data()
    {
        $user = $this->createUser(User::ROLE_ADMIN, 'admin@test.com');

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email', 'role'],
            ]);
    }

    public function test_me_returns_authenticated_user()
    {
        $user = $this->createUser(User::ROLE_USUARIO, 'usuario@test.com');

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/me');

        $response->assertOk()
            ->assertJson([
                'email' => $user->email,
                'role' => User::ROLE_USUARIO,
            ]);
    }

    public function test_usuario_can_read_productos_but_cannot_modify_them()
    {
        $usuario = $this->createUser(User::ROLE_USUARIO, 'sololectura@test.com');
        $producto = Producto::create([
            'nombre' => 'Cafe',
            'precio' => 9.50,
            'stock' => 5,
            'descripcion' => 'Cafe molido',
        ]);

        $this->withHeaders($this->authHeaders($usuario))
            ->getJson('/api/productos')
            ->assertOk();

        $this->withHeaders($this->authHeaders($usuario))
            ->getJson("/api/productos/{$producto->id}")
            ->assertOk();

        $this->withHeaders($this->authHeaders($usuario))
            ->postJson('/api/productos', [
                'nombre' => 'Nuevo',
                'precio' => 5,
                'stock' => 2,
            ])
            ->assertStatus(403);

        $this->withHeaders($this->authHeaders($usuario))
            ->putJson("/api/productos/{$producto->id}", [
                'precio' => 12,
            ])
            ->assertStatus(403);

        $this->withHeaders($this->authHeaders($usuario))
            ->deleteJson("/api/productos/{$producto->id}")
            ->assertStatus(403);
    }

    public function test_operador_can_store_and_update_but_cannot_destroy()
    {
        $operador = $this->createUser(User::ROLE_OPERADOR, 'operador@test.com');
        $headers = $this->authHeaders($operador);

        $storeResponse = $this->withHeaders($headers)->postJson('/api/productos', [
            'nombre' => 'Te',
            'precio' => 7.25,
            'stock' => 8,
            'descripcion' => 'Te verde',
        ]);

        $storeResponse->assertCreated()
            ->assertJsonPath('data.nombre', 'Te')
            ->assertJsonPath('data.stock', 8);

        $productoId = $storeResponse->json('data.id');

        $this->withHeaders($headers)->putJson("/api/productos/{$productoId}", [
            'precio' => 8.10,
            'stock' => 12,
        ])
            ->assertOk()
            ->assertJsonPath('data.precio', 8.1)
            ->assertJsonPath('data.stock', 12);

        $this->withHeaders($headers)->deleteJson("/api/productos/{$productoId}")
            ->assertStatus(403);
    }

    public function test_admin_can_delete_productos()
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin-delete@test.com');
        $producto = Producto::create([
            'nombre' => 'Azucar',
            'precio' => 4.00,
            'stock' => 20,
            'descripcion' => 'Azucar refinada',
        ]);

        $this->withHeaders($this->authHeaders($admin))
            ->deleteJson("/api/productos/{$producto->id}")
            ->assertOk()
            ->assertJson([
                'message' => "Producto con id ({$producto->id}) ha sido eliminado",
            ]);

        $this->assertDatabaseMissing('productos', [
            'id' => $producto->id,
        ]);
    }

    public function test_store_validation_returns_422_json()
    {
        $operador = $this->createUser(User::ROLE_OPERADOR, 'operador-validation@test.com');

        $this->withHeaders($this->authHeaders($operador))
            ->postJson('/api/productos', [
                'nombre' => '',
                'precio' => -1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre', 'precio', 'stock']);
    }

    public function test_update_validation_returns_422_json()
    {
        $operador = $this->createUser(User::ROLE_OPERADOR, 'operador-update@test.com');
        $producto = Producto::create([
            'nombre' => 'Harina',
            'precio' => 3.50,
            'stock' => 10,
            'descripcion' => 'Harina de trigo',
        ]);

        $this->withHeaders($this->authHeaders($operador))
            ->putJson("/api/productos/{$producto->id}", [
                'precio' => -10,
                'stock' => -2,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['precio', 'stock']);
    }

    public function test_logout_invalidates_the_current_token()
    {
        $user = $this->createUser(User::ROLE_ADMIN, 'logout@test.com');
        $headers = $this->authHeaders($user);

        $this->withHeaders($headers)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJson([
                'message' => 'Logout correcto',
            ]);

        app('auth')->forgetGuards();
        JWTAuth::unsetToken();

        $this->withHeaders($headers)
            ->getJson('/api/me')
            ->assertStatus(401);
    }

    public function test_refresh_returns_a_new_token()
    {
        $user = $this->createUser(User::ROLE_ADMIN, 'refresh@test.com');
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/refresh');

        $newToken = $response->json('token');

        $response->assertOk()
            ->assertJson([
                'message' => 'Token renovado correctamente',
                'user' => [
                    'email' => $user->email,
                    'role' => User::ROLE_ADMIN,
                ],
            ]);

        $this->assertNotEmpty($newToken);
        $this->assertNotSame($token, $newToken);

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $newToken,
        ])->getJson('/api/me')
            ->assertOk()
            ->assertJson([
                'email' => $user->email,
            ]);
    }

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function authHeaders(User $user): array
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . JWTAuth::fromUser($user),
        ];
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('productos');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role', 20)->default(User::ROLE_USUARIO);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('precio', 10, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->longText('descripcion')->nullable();
            $table->timestamps();
        });
    }
}
