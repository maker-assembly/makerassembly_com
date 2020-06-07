<?php

namespace Tests\Feature\Authentication;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $token;

    public const PASSWORD = 'makers-assemble';

    public function setUp(): void
    {
        parent::setUp();

        $this->user = create('User', [
            'password' => self::PASSWORD
        ]);

        $this->token = Password::broker()->createToken($this->user);
    }

    /** @test */
    public function anyone_with_a_valid_token_can_view_the_password_reset_page()
    {
        $this->get(route('password.reset', $this->token))
            ->assertSuccessful()
            ->assertViewIs('auth.passwords.reset')
            ->assertViewHas('token', $this->token);

        $this->signIn($this->user)->get(route('password.reset', $this->token))
            ->assertSuccessful()
            ->assertViewIs('auth.passwords.reset')
            ->assertViewHas('token', $this->token);
    }

    /** @test */
    public function anyone_with_a_valid_token_can_reset_their_password()
    {
        Event::fake();

        $this->post('/password/reset', [
            'token' => $this->token,
            'email' => $this->user->email,
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ])
            ->assertRedirect(RouteServiceProvider::HOME);
        $this->assertEquals($this->user->email, $this->user->fresh()->email);
        $this->assertTrue(Hash::check('new-awesome-password', $this->user->fresh()->password));
        $this->assertAuthenticatedAs($this->user);

        Event::assertDispatched(PasswordReset::class, function ($e) {
            return $e->user->id === $this->user->id;
        });
    }

    /** @test */
    public function anyone_with_an_invalid_token_cannot_reset_their_password()
    {
        $this->from(route('password.reset', 'invalid-token'))->post('/password/reset', [
            'token' => 'invalid-token',
            'email' => $this->user->email,
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ])
            ->assertRedirect(route('password.reset', 'invalid-token'));
        $this->assertEquals($this->user->email, $this->user->fresh()->email);
        $this->assertTrue(Hash::check(self::PASSWORD, $this->user->fresh()->password));
        $this->assertGuest();
    }

    /** @test */
    public function anyone_with_a_valid_token_must_provide_a_new_password_to_change_their_password()
    {
        $this->from(route('password.reset', $this->token))->post('/password/reset', [
            'token' => $this->token,
            'email' => $this->user->email,
            'password' => '',
            'password_confirmation' => '',
        ])
            ->assertRedirect(route('password.reset', $this->token))
            ->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($this->user->email, $this->user->fresh()->email);
        $this->assertTrue(Hash::check(self::PASSWORD, $this->user->fresh()->password));
        $this->assertGuest();
    }

    /** @test */
    public function anyone_with_a_valid_token_must_provide_an_email_to_change_their_password()
    {
        $this->from(route('password.reset', $this->token))->post('/password/reset', [
            'token' => $this->token,
            'email' => '',
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ])
            ->assertRedirect(route('password.reset', $this->token))
            ->assertSessionHasErrors('email');
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($this->user->email, $this->user->fresh()->email);
        $this->assertTrue(Hash::check(self::PASSWORD, $this->user->fresh()->password));
        $this->assertGuest();
    }
}
