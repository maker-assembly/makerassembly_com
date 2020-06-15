<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfilesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = create('User');
    }

    /** @test */
    public function anyone_can_view_all_profiles()
    {
        $this->get(route('profiles.index'))
            ->assertStatus(200);
    }

    /** @test */
    public function anyone_can_view_a_single_profile()
    {
        $this->get(route('profiles.show', [
            'user' => $this->user
        ]))
            ->assertStatus(200);
    }

    /** @test */
    public function a_guest_cannot_update_a_users_profile()
    {
        $this->patch(route('profiles.update', ['user' => $this->user]), [])
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_user_can_update_their_profile()
    {
        $attributes = [
            'first_name' => $this->faker->firstName(),
            'last_mame' => $this->faker->lastName
        ];

        $this->signIn($this->user)
            ->patch(route('profiles.update', [
                'user' => $this->user
            ]), $attributes);

        $this->get($this->user->path())
            ->assertSee($attributes['first_name']);
    }
}
