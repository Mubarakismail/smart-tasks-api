<?php


namespace Modules\Auth\Tests\Feature;


use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;
use Modules\Core\Tests\ModuleTestCase;


class LoginTest extends ModuleTestCase
{
    public function test_it_logs_in_and_returns_sanctum_token(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('secret1234'),
        ]);


        $res = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'secret1234',
            'device_name' => 'phpunit',
        ]);


        $res->assertOk()->assertJsonStructure(['access_token']);
    }


    public function test_login_fails_with_422_on_bad_credentials(): void
    {
        $res = $this->postJson('/api/v1/auth/login', [
            'email' => 'nope@example.com',
            'password' => 'wrong',
        ]);


        $res->assertStatus(422)->assertJsonFragment(['message' => 'Invalid credentials']);
    }
}
