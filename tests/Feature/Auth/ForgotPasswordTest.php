<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_can_view_the_reset_password_request_page()
    {
        $response = $this->get(route('password.request'));

        $response->assertSuccessful();
        $response->assertViewIs('auth.passwords.email');
    }

    /** @test */
    public function a_user_can_view_the_reset_password_request_page()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get(route('password.request'));

        $response->assertSuccessful();
        $response->assertViewIs('auth.passwords.email');
    }

    /** @test */
    public function anyone_with_a_registered_email_is_sent_a_password_reset_email()
    {
        Notification::fake();
        $user = factory(User::class)->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'john@example.com',
        ]);

        $this->assertNotNull($token = DB::table('password_resets')->first());
        Notification::assertSentTo($user, ResetPassword::class, function ($notification, $channels) use ($token) {
            return Hash::check($notification->token, $token->token) === true;
        });
    }

    /** @test */
    public function anyone_without_a_registered_email_is_not_sent_a_password_reset_email()
    {
        Notification::fake();

        $response = $this->from(route('password.email'))->post(route('password.email'), [
            'email' => 'nobody@example.com',
        ]);

        $response->assertRedirect(route('password.email'));
        $response->assertSessionHasErrors('email');
        Notification::assertNotSentTo(factory(User::class)->make(['email' => 'nobody@example.com']), ResetPassword::class);
    }

    /** @test */
    public function anyone_must_submit_an_email_to_request_a_password_reset()
    {
        $response = $this->from(route('password.email'))->post(route('password.email'), []);

        $response->assertRedirect(route('password.email'));
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function anyone_must_submit_a_valid_email_to_request_a_password_reset()
    {
        $response = $this->from(route('password.email'))->post(route('password.email'), [
            'email' => 'invalid-email',
        ]);

        $response->assertRedirect(route('password.email'));
        $response->assertSessionHasErrors('email');
    }
}
