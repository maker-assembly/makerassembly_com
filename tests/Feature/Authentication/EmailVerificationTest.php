<?php

namespace Tests\Feature\Authentication;

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
        $this->get(route('verification.notice'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_resend_a_verification_email()
    {
        $this->post(route('verification.resend'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_view_the_email_verification_page()
    {
        $user = factory('App\Models\User')->states('unverified')->create();

        $this->get(URL::signedRoute('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function an_unverified_user_can_view_the_verification_notice_page()
    {
        $this->signIn(factory('App\Models\User')->states('unverified')->create())->get(route('verification.notice'))
            ->assertStatus(200)
            ->assertViewIs('auth.verify');
    }

    /** @test */
    public function an_unverified_user_can_resend_a_verification_email()
    {
        Notification::fake();

        $user = factory('App\Models\User')->states('unverified')->create();

        $this->signIn($user)
            ->from(route('verification.notice'))
            ->post(route('verification.resend'))
            ->assertRedirect(route('verification.notice'));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test */
    public function a_verified_user_cannot_view_the_verification_notice_page()
    {
        $this->signIn()->get(route('verification.notice'))
            ->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function a_verified_user_cannot_view_the_email_verification_page()
    {
        $user = create('User');

        $this->signIn($user)->get(URL::signedRoute('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]))
            ->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function a_verfied_user_cannot_resend_a_verification_email()
    {
        $this->signIn()->post(route('verification.resend'))
            ->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function a_user_with_an_invalid_signature_cannot_view_the_email_verification_page()
    {
        $user = create('User');

        $this->signIn($user)->get(route('verification.verify', [
            'id' => $user->id,
            'hash' => 'invalid-hash',
        ]))
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_verify_another_user()
    {
        $unverifiedUser = factory('App\Models\User')->states('unverified')->create();

        $this->signIn()->get(URL::signedRoute('verification.verify', [
            'id' => $unverifiedUser->id,
            'hash' => sha1($unverifiedUser->getEmailForVerification()),
        ]))
            ->assertForbidden();
        $this->assertFalse($unverifiedUser->fresh()->hasVerifiedEmail());
    }

    /** @test */
    public function a_user_can_verify_their_email()
    {
        $user = factory('App\Models\User')->states('unverified')->create();

        $this->signIn($user)->get(URL::signedRoute('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]))
            ->assertRedirect(RouteServiceProvider::HOME);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
