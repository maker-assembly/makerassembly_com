<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_with_a_valid_token_can_view_the_password_reset_page()
    {
        $user = factory(User::class)->create();

        $response = $this->get(route('password.reset', $token = Password::broker()->createToken($user)));

        $response->assertSuccessful();
        $response->assertViewIs('auth.passwords.reset');
        $response->assertViewHas('token', $token);
    }

    /** @test */
    public function a_user_with_a_valid_token_can_view_the_password_reset_page()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get(route('password.reset', $token = Password::broker()->createToken($user)));

        $response->assertSuccessful();
        $response->assertViewIs('auth.passwords.reset');
        $response->assertViewHas('token', $token);
    }

    /** @test */
    public function anyone_with_a_valid_token_can_reset_their_password()
    {
        Event::fake();
        $user = factory(User::class)->create();

        $response = $this->post('/password/reset', [
            'token' => Password::broker()->createToken($user),
            'email' => $user->email,
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ]);

        $response->assertRedirect(RouteServiceProvider::HOME);
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('new-awesome-password', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(PasswordReset::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }

    /** @test */
    public function anyone_with_an_invalid_token_cannot_reset_their_password()
    {
        $user = factory(User::class)->create([
            'password' => 'old-password',
        ]);

        $response = $this->from(route('password.reset', 'invalid-token'))->post('/password/reset', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ]);

        $response->assertRedirect(route('password.reset', 'invalid-token'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertGuest();
    }

    /** @test */
    public function anyone_with_a_valid_token_must_provide_a_new_password_to_change_their_password()
    {
        $user = factory(User::class)->create([
            'password' => 'old-password',
        ]);

        $response = $this->from(route('password.reset', $token = Password::broker()->createToken($user)))->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('password.reset', $token));
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertGuest();
    }

    /** @test */
    public function anyone_with_a_valid_token_must_provide_an_email_to_change_their_password()
    {
        $user = factory(User::class)->create([
            'password' => 'old-password',
        ]);

        $response = $this->from(route('password.reset', $token = Password::broker()->createToken($user)))->post('/password/reset', [
            'token' => $token,
            'email' => '',
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ]);

        $response->assertRedirect(route('password.reset', $token));
        $response->assertSessionHasErrors('email');
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertGuest();
    }
}
