<?php


namespace Modules\Auth\Tests\Feature;


use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;
use Modules\Core\Tests\ModuleTestCase;


class RegisterTest extends ModuleTestCase
{
    public function test_it_registers_a_user_and_stores_hashed_password(): void
    {
        $payload = [
            'name' => 'Mubarak ismail',
            'email' => 'mubarak@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ];


        $res = $this->postJson('/api/v1/auth/register', $payload);
        $res->assertCreated();


        $this->assertDatabaseHas('users', ['email' => 'mubarak@example.com']);
        $hash = User::whereEmail('mubarak@example.com')->value('password');
        $this->assertTrue(Hash::check('secret1234', $hash));
    }


    public function test_register_validation_errors(): void
    {
        $res = $this->postJson('/api/v1/auth/register', []);
        $res->assertStatus(422)->assertJsonValidationErrors(['name', 'email', 'password']);
    }
}
