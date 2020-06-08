<?php

namespace Tests\Feature\Authentication;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
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
    public function a_guest_can_view_the_login_page()
    {
        $this->get(route('login'))
            ->assertSuccessful()
            ->assertViewIs('auth.login');
    }

    /** @test */
    public function a_guest_can_login_with_the_correct_email_and_password()
    {
        $this->post(route('login'), [
            'identity' => $this->user->email,
            'password' => self::PASSWORD,
        ])
            ->assertRedirect(RouteServiceProvider::HOME);

        $this->assertAuthenticatedAs($this->user);
    }

    /** @test */
    public function a_guest_can_login_with_the_correct_username_and_password()
    {
        $this->post(route('login'), [
            'identity' => $this->user->username,
            'password' => self::PASSWORD,
        ])
            ->assertRedirect(RouteServiceProvider::HOME);

        $this->assertAuthenticatedAs($this->user);
    }

    /** @test */
    public function a_guest_cannot_login_with_an_email_and_an_incorrect_password()
    {
        $this->from(route('login'))->post(route('login'), [
            'identity' => $this->user->email,
            'password' => 'invalid-password',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertTrue(session()->hasOldInput('identity'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_guest_cannot_login_with_a_username_and_an_incorrect_password()
    {
        $this->from(route('login'))->post(route('login'), [
            'identity' => $this->user->username,
            'password' => 'invalid-password',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertTrue(session()->hasOldInput('identity'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_guest_cannot_login_with_an_email_that_does_not_exist()
    {
        $this->from(route('login'))->post(route('login'), [
            'identity' => 'nobody@example.com',
            'password' => 'invalid-password',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertTrue(session()->hasOldInput('identity'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_guest_cannot_login_with_a_username_that_does_not_exist()
    {
        $this->from(route('login'))->post(route('login'), [
            'identity' => 'mrNobody',
            'password' => 'invalid-password',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertTrue(session()->hasOldInput('identity'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_guest_cannot_attempt_to_login_more_than_five_times_per_minute()
    {
        foreach (range(0, 5) as $_) {
            $response = $this->from(route('login'))->post(route('login'), [
                'identity' => $this->user->email,
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
        $this->assertTrue(session()->hasOldInput('identity'));
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
    public function a_user_cannot_view_the_login_page()
    {
        $this->signIn($this->user)
            ->get(route('login'))
            ->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function a_user_can_use_the_remember_me_functionality_when_logging_in()
    {
        $user = create('User', [
            'id' => random_int(1, 100),
            'password' => self::PASSWORD,
        ]);

        $response = $this->post(route('login'), [
            'identity' => $user->email,
            'password' => self::PASSWORD,
            'remember' => 'on',
        ]);

        $user = $user->fresh();

        $response
            ->assertRedirect(RouteServiceProvider::HOME)
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
        $this->signIn($this->user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
