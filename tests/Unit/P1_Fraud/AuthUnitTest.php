<?php

namespace Tests\Unit\P1_Fraud;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthUnitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful registration.
     */
    public function test_user_registration_successfully(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Alice Register',
            'email' => 'alice.register@test.com',
            'password' => 'secret123',
            'role' => 'publisher'
        ]);

        $response->assertRedirect(route('publisher.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'alice.register@test.com',
            'name' => 'Alice Register',
            'role' => 'publisher'
        ]);

        $this->assertTrue(Auth::check());
    }

    /**
     * Test registration validation.
     */
    public function test_user_registration_validation(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => '',
            'email' => 'not-an-email',
            'password' => '123'
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertFalse(Auth::check());
    }

    /**
     * Test successful login and logout.
     */
    public function test_user_login_and_logout_successfully(): void
    {
        $user = User::create([
            'name' => 'Bob Login',
            'email' => 'bob.login@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'shop',
            'google2fa_enabled' => false
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'bob.login@test.com',
            'password' => 'secret123'
        ]);

        $response->assertRedirect(route('shop.dashboard'));
        $this->assertTrue(Auth::check());

        // Test logout
        $logoutResponse = $this->get(route('logout'));
        $logoutResponse->assertRedirect(route('home'));
        $this->assertFalse(Auth::check());
    }

    /**
     * Test 2FA login redirection and verification.
     */
    public function test_login_with_2fa_enabled(): void
    {
        $user = User::create([
            'name' => 'Secure User',
            'email' => 'secure@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'publisher',
            'google2fa_enabled' => true,
            'google2fa_secret' => 'SECRET2FAKEY'
        ]);

        // Attempt login -> Should NOT log in user yet and redirect to 2FA verify
        $response = $this->post(route('login.post'), [
            'email' => 'secure@test.com',
            'password' => 'secret123'
        ]);

        $response->assertRedirect(route('2fa.verify'));
        $this->assertFalse(Auth::check());
        $this->assertEquals($user->id, session('2fa:user:id'));

        // Mock Google2FA verifyKey to return true for code 123456
        $google2faMock = \Mockery::mock(app('pragmarx.google2fa'))->makePartial();
        $google2faMock->shouldReceive('verifyKey')
            ->with('SECRET2FAKEY', '123456')
            ->andReturn(true);
        $google2faMock->shouldReceive('verifyKey')
            ->with('SECRET2FAKEY', '000000')
            ->andReturn(false);
        app()->instance('pragmarx.google2fa', $google2faMock);

        // Attempt verification with WRONG code
        $verifyFailResponse = $this->withSession(['2fa:user:id' => $user->id])
            ->post(route('2fa.verify.post'), [
                'one_time_password' => '000000'
            ]);
        $verifyFailResponse->assertSessionHasErrors(['one_time_password']);
        $this->assertFalse(Auth::check());

        // Attempt verification with CORRECT code
        $verifySuccessResponse = $this->withSession(['2fa:user:id' => $user->id])
            ->post(route('2fa.verify.post'), [
                'one_time_password' => '123456'
            ]);

        $verifySuccessResponse->assertRedirect(route('publisher.dashboard'));
        $this->assertTrue(Auth::check());
    }

    /**
     * Test 2FA setup, enable and disable when logged in.
     */
    public function test_enable_and_disable_2fa_setup(): void
    {
        $user = User::create([
            'name' => 'Alice 2FA',
            'email' => 'alice.2fa@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'publisher',
            'google2fa_enabled' => false
        ]);

        $this->actingAs($user);

        // Show setup page - should generate key and return view
        $response = $this->get(route('2fa.setup'));
        $response->assertStatus(200);
        $user->refresh();
        $this->assertNotNull($user->google2fa_secret);

        // Mock Google2FA verification
        $google2faMock = \Mockery::mock(app('pragmarx.google2fa'))->makePartial();
        $google2faMock->shouldReceive('verifyKey')
            ->andReturn(true);
        app()->instance('pragmarx.google2fa', $google2faMock);

        // Enable 2FA
        $enableResponse = $this->post(route('2fa.enable'), [
            'one_time_password' => '123456'
        ]);
        $enableResponse->assertRedirect();
        $user->refresh();
        $this->assertTrue($user->google2fa_enabled);

        // Disable 2FA with correct password
        $disableResponse = $this->post(route('2fa.disable'), [
            'password' => 'secret123'
        ]);
        $disableResponse->assertRedirect();
        $user->refresh();
        $this->assertFalse($user->google2fa_enabled);
    }
}
