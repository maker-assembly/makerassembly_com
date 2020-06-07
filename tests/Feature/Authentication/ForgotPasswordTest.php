<?php

namespace Tests\Feature\Authentication;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public const PASSWORD = 'makers-assemble';

    public function setUp(): void
    {
        parent::setUp();

        $this->user = create('User', [
            'password' => self::PASSWORD
        ]);
    }

    /** @test */
    public function anyone_can_view_the_password_reset_request_page()
    {
        $this->get(route('password.request'))
            ->assertSuccessful()
            ->assertViewIs('auth.passwords.email');

        $this->signIn($this->user)->get(route('password.request'))
            ->assertSuccessful()
            ->assertViewIs('auth.passwords.email');
    }

    /** @test */
    public function anyone_with_a_registered_email_is_sent_a_password_reset_email()
    {
        Notification::fake();

        $this->post(route('password.email'), [
            'email' => $this->user->email,
        ]);

        $this->assertNotNull($token = DB::table('password_resets')->first());

        Notification::assertSentTo($this->user, ResetPassword::class, function ($notification, $channels) use ($token) {
            return Hash::check($notification->token, $token->token) === true;
        });
    }

    /** @test */
    public function anyone_without_a_registered_email_is_not_sent_a_password_reset_email()
    {
        Notification::fake();

        $unregisteredEmail = 'nobody@example.com';

        $this->from(route('password.email'))->post(route('password.email'), [
            'email' => $unregisteredEmail,
        ])
            ->assertRedirect(route('password.email'))
            ->assertSessionHasErrors('email');

        Notification::assertNotSentTo(make('User', [
            'email' => $unregisteredEmail
        ]), ResetPassword::class);
    }

    /** @test */
    public function anyone_must_submit_an_email_to_request_a_password_reset()
    {
        $this->from(route('password.email'))->post(route('password.email'), [])
            ->assertRedirect(route('password.email'))
            ->assertSessionHasErrors('email');
    }

    /** @test */
    public function anyone_must_submit_a_valid_email_to_request_a_password_reset()
    {
        $this->from(route('password.email'))->post(route('password.email'), [
            'email' => 'invalid-email',
        ])
            ->assertRedirect(route('password.email'))
            ->assertSessionHasErrors('email');
    }
}
