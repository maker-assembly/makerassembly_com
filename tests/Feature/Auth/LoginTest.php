<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_can_view_the_login_page()
    {
        $this->get(route('login'))
            ->assertSuccessful()
            ->assertViewIs('auth.login');
    }

    /** @test */
    public function a_guest_can_login_with_the_correct_credentials()
    {
        $user = factory(User::class)->create([
            'password' => $password = 'makers-assemble',
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => $password,
        ])
            ->assertRedirect(RouteServiceProvider::HOME);

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function a_guest_cannot_login_with_incorrect_credentials()
    {
        $user = factory(User::class)->create([
            'password' => 'makers-assemble',
        ]);

        $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'invalid-password',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_guest_cannot_login_with_an_email_that_does_not_exist()
    {
        $this->from(route('login'))->post(route('login'), [
            'email' => 'nobody@example.com',
            'password' => 'invalid-password',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_guest_cannot_logout()
    {
        $this->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }

    /** @test */
    public function a_guest_cannot_attempt_to_login_more_than_five_times_per_minute()
    {
        $user = factory(User::class)->create([
            'password' => $password = 'i-love-laravel',
        ]);

        foreach (range(0, 5) as $_) {
            $response = $this->from(route('login'))->post(route('login'), [
                'email' => $user->email,
                'password' => 'invalid-password',
            ]);
        }

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertRegExp(
            sprintf('/^%s$/', str_replace('\:seconds', '\d+', preg_quote(__('auth.throttle'), '/'))),
            collect(
                $response
                    ->baseResponse
                    ->getSession()
                    ->get('errors')
                    ->getBag('default')
                    ->get('email')
            )->first()
        );
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_user_cannot_view_the_login_page()
    {
        $this->actingAs(factory(User::class)->make())
            ->get(route('login'))
            ->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function a_user_can_use_the_remember_me_functionality_when_logging_in()
    {
        $user = factory(User::class)->create([
            'id' => random_int(1, 100),
            'password' => $password = 'i-love-laravel',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => $password,
            'remember' => 'on',
        ]);

        $user = $user->fresh();

        $response->assertRedirect(RouteServiceProvider::HOME)
            ->assertCookie(Auth::guard()->getRecallerName(), vsprintf('%s|%s|%s', [
                $user->id,
                $user->getRememberToken(),
                $user->password,
            ]));

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function a_user_can_logout()
    {
        $this->be(factory(User::class)->create());

        $this->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
