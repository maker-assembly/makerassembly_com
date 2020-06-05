<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\URL;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_cannot_view_the_verification_notice_page()
    {
        $response = $this->get(route('verification.notice'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function an_unverified_user_can_view_the_verification_notice_page()
    {
        $user = factory(User::class)->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.verify');
    }

    /** @test */
    public function a_user_is_redirected_home_after_verifying_their_email()
    {
        $user = factory(User::class)->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function a_guest_cannot_view_the_email_verification_page()
    {
        $user = factory(User::class)->create([
            'id' => 1,
            'email_verified_at' => null,
        ]);

        $response = $this->get(URL::signedRoute('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function a_user_cannot_verify_another_user()
    {
        $user = factory(User::class)->create([
            'id' => 1,
            'email_verified_at' => null,
        ]);

        $user2 = factory(User::class)->create(['id' => 2, 'email_verified_at' => null]);

        $response = $this->actingAs($user)->get(URL::signedRoute('verification.verify', [
            'id' => $user2->id,
            'hash' => sha1($user2->getEmailForVerification()),
        ]));

        $response->assertForbidden();
        $this->assertFalse($user2->fresh()->hasVerifiedEmail());
    }

    /** @test */
    public function a_verified_user_is_redirected_home()
    {
        $user = factory(User::class)->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(URL::signedRoute('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]));

        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function anyone_is_forbidden_from_the_verification_page_with_an_invalid_signature()
    {
        $user = factory(User::class)->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('verification.verify', [
            'id' => $user->id,
            'hash' => 'invalid-hash',
        ]));

        $response->assertStatus(403);
    }

    /** @test */
    public function a_user_can_verify_themself()
    {
        $user = factory(User::class)->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get(URL::signedRoute('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]));

        $response->assertRedirect(RouteServiceProvider::HOME);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    /** @test */
    public function a_guest_cannot_resend_a_verification_email()
    {
        $response = $this->post(route('verification.resend'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function anyone_is_redirected_home_if_already_verified()
    {
        $user = factory(User::class)->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('verification.resend'));

        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function a_user_can_resend_the_verification_email()
    {
        Notification::fake();
        $user = factory(User::class)->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.resend'));

        Notification::assertSentTo($user, VerifyEmail::class);
        $response->assertRedirect(route('verification.notice'));
    }
}
