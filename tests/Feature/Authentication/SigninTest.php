<?php

namespace Authentication;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class SigninTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_signin()
    {
        $user = User::factory()->create([
            'email' => 'akbar@example.com',
            'password' => Hash::make('secret123$%^')
        ]);

        $response = $this->postJson('/api/signin', [
            'email' => 'akbar@example.com',
            'password' => 'secret123$%^',
        ]);

        $response->assertOk();
        self::assertIsString($response->content());
        assertEquals(1, $user->tokens()->count());
    }

    public function test_user_can_not_signin_with_incorrect_password()
    {
        $user = User::factory()->create([
            'email' => 'akbar@example.com',
            'password' => Hash::make('secret')
        ]);

        $response = $this->postJson('/api/signin', [
            'email' => 'akbar@example.com',
            'password' => 'something-wrong',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrorFor('email');

        assertEquals(0, $user->tokens()->count());
    }

    public function test_user_can_not_signin_with_empty_password()
    {
        $user = User::factory()->create([
            'email' => 'akbar@example.com',
            'password' => Hash::make('secret123$%^')
        ]);

        $response = $this->postJson('/api/signin', [
            'email' => 'akbar@example.com',
            'password' => '',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrorFor('password');
        assertEquals(0, $user->tokens()->count());
    }

    public function invalidEmailProvider()
    {
        return [
            'malformed' => ['something.com'],
            'empty' => [''],
            'long' => [str_repeat('a', 1000) . '@example.com']
        ];
    }

    /**
     * @dataProvider invalidEmailProvider
     * @return void
     */
    public function test_user_can_not_signin_with_invalid_email($email)
    {
        $response = $this->postJson('/api/signin', [
            'email' => $email,
            'password' => 'secret',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrorFor('email');

        $this->markTestIncomplete();
    }

    public function test_user_can_not_signin_if_already_authorized()
    {
        $user = User::factory()->create([
            'email' => 'akbar@example.com',
            'password' => Hash::make('akbar-secret')
        ]);

        $sara = User::factory()->create([
            'email' => 'sara@example.com',
            'password' => Hash::make('sara-secret')
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/signin', [
            'email' => 'sara@example.com',
            'password' => 'sara-secret',
        ], ['Accept' => 'application/json']);

        $response->assertRedirect();
        assertEquals(0, $sara->tokens()->count());
    }

}
