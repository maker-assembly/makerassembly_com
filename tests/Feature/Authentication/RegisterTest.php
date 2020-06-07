<?php

namespace Tests\Feature\Authentication;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public const PASSWORD = 'makers-assemble';

    /** @test */
    public function a_guest_can_view_the_registration_page()
    {
        $this->get(route('register'))
            ->assertSuccessful()
            ->assertViewIs('auth.register');
    }

    /** @test */
    public function a_guest_can_register()
    {
        Event::fake();

        $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])
            ->assertRedirect(RouteServiceProvider::HOME);
        $this->assertCount(1, $users = User::all());
        $this->assertAuthenticatedAs($user = $users->first());
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));
        Event::assertDispatched(Registered::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }

    /** @test */
    public function a_guest_cannot_register_without_a_name()
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => '',
            'email' => 'john@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ]);

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('name');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_guest_cannot_register_without_an_email()
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'John Doe',
            'email' => '',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ]);

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_guest_cannot_register_without_a_valid_email()
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ]);

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_guest_cannot_register_without_a_password()
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_guest_cannot_resiter_without_confirming_their_password()
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => '',
        ]);

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_guest_cannot_register_without_their_password_and_password_confirmation_matching()
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => 'not-the-same-password',
        ]);

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /** @test */
    public function a_user_cannot_view_the_registration_page()
    {
        $this->signIn()
            ->get(route('register'))
            ->assertRedirect(RouteServiceProvider::HOME);
    }
}
