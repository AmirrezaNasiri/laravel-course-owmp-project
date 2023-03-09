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

class SignupTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_signup()
    {
        # Action
        $response = $this->postJson('/api/signup', [
            'name' => 'Akbar',
            'email' => 'akbar@example.com',
            'password' => 'secret123%^&',
        ]);

        # Assertion
        $response->assertOk();

        self::assertIsString($response->content());

        $user = User::first();
        self::assertEquals('Akbar', $user->name);
        self::assertEquals('akbar@example.com', $user->email);
        self::assertTrue(Hash::check('secret123%^&', $user->password));
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
    public function test_user_can_not_signup_with_invalid_email($email)
    {
        # Action
        $response = $this->postJson('/api/signup', [
            'name' => 'Akbar',
            'email' => $email,
            'password' => 'secret123%^&',
        ]);

        # Assertion
        $response->assertUnprocessable()->assertJsonValidationErrorFor('email');
        self::assertEmpty(User::count());
    }

    public function invalidPasswordProvider()
    {
        return [
            'empty' => [''],
            'too short' => ['a1@'],
            'long' => [str_repeat('a', 1000).'123!@#'],
            'simple' => ['mypassword'],
            'simple-2' => ['123123123'],
            'simple-3' => ['mypassword123123123'],
        ];
    }

    /**
     * @dataProvider invalidPasswordProvider
     * @return void
     */
    public function test_user_can_not_signup_with_invalid_password($password)
    {
        # Action
        $response = $this->postJson('/api/signup', [
            'name' => 'Akbar',
            'email' => 'akbar@example.com',
            'password' => $password,
        ]);

        # Assertion
        $response->assertUnprocessable()->assertJsonValidationErrorFor('password');
        self::assertEmpty(User::count());
    }

    public function test_user_can_not_signup_if_email_already_exists()
    {
        # Preparation
        User::factory()->create([
            'email' => 'akbar@example.com'
        ]);

        # Action
        $response = $this->postJson('/api/signup', [
            'name' => 'Akbar',
            'email' => 'akbar@example.com',
            'password' => 'secret123%^&',
        ]);

        # Assertion
        $response->assertUnprocessable()->assertJsonValidationErrorFor('email');
        self::assertEquals(1, User::count());
    }

    public function test_user_can_not_signup_if_already_authorized()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        # Action
        $response = $this->postJson('/api/signup', [
            'name' => 'Akbar',
            'email' => 'akbar@example.com',
            'password' => 'secret123%^&',
        ]);

        # Assertion
        $response->assertRedirect();

        self::assertIsString($response->content());
        assertEquals(0, $user->tokens()->count());
    }
}
