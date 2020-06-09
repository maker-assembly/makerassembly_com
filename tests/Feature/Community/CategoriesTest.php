<?php

namespace Tests\Feature\Community;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class CategoriesTest extends TestCase
{
    use RefreshDatabase;

    protected $category;

    protected $moderator;

    public function setUp(): void
    {
        parent::setUp();

        $this->category = create('Category');

        $this->moderator = create('User')->assignRole(Role::create(['name' => 'moderator'])
            ->syncPermissions([
                Permission::create(['name' => 'create categories']),
                Permission::create(['name' => 'update categories']),
                Permission::create(['name' => 'delete categories']),
                Permission::create(['name' => 'restore categories']),
                Permission::create(['name' => 'destroy categories']),
            ]));

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    /** @test */
    public function anyone_can_view_all_categories()
    {
        $this->get(route('categories.index'))
            ->assertStatus(200)
            ->assertSee($this->category->name);
    }

    /** @test */
    public function anyone_can_view_a_single_category()
    {
        $this->get(route('categories.show', [
            'category' => $this->category
        ]))
            ->assertStatus(200)
            ->assertSee($this->category->name);
    }

    /** @test */
    public function a_guest_cannot_create_a_category()
    {
        $this->get(route('categories.create'))
            ->assertRedirect(route('login'));

        $this->post(
            route('categories.store'),
            []
        )
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_edit_a_category()
    {
        $this->get(route('categories.edit', [
            'category' => $this->category
        ]))
            ->assertRedirect(route('login'));

        $this->patch(
            route('categories.update', [
                'category' => $this->category
            ]),
            []
        )
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_delete_a_category()
    {
        $this->delete(route('categories.destroy', [
            'category' => $this->category
        ]))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_user_cannot_create_a_category()
    {
        $this->signIn();

        $this->get(route('categories.create'))
            ->assertStatus(403);

        $this->post(route('categories.store'), [])
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_edit_a_category()
    {
        $this->signIn();

        $this->get(route('categories.edit', [
            'category' => $this->category
        ]))
            ->assertStatus(403);

        $this->patch(route('categories.update', [
            'category' => $this->category
        ]), [])
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_delete_a_category()
    {
        $this->signIn();

        $this->delete(route('categories.destroy', [
            'category' => $this->category
        ]))
            ->assertStatus(403);
    }

    /** @test */
    public function a_moderator_can_create_categories()
    {
        $this->signIn($this->moderator);

        $this->get(route('categories.create'))
            ->assertStatus(200);

        $attributes = raw('Category');

        $response = $this->post(route('categories.store'), $attributes);

        $this->get($response->headers->get('Location'))
            ->assertSee($attributes['name']);
    }

    /** @test */
    public function a_moderator_can_edit_categories()
    {
        $this->signIn($this->moderator);

        $this->get(route('categories.edit', [
            'category' => $this->category
        ]))
            ->assertStatus(200);

        $attributes = raw('Category');

        $response = $this->patch(route('categories.update', [
            'category' => $this->category
        ]), $attributes);

        $this->get($response->headers->get('Location'))
            ->assertSee($attributes['name']);
    }

    /** @test */
    public function a_moderator_can_delete_categories()
    {
        $this->signIn($this->moderator);

        $this->delete(route('categories.destroy', [
            'category' => $this->category
        ]));

        $this->get(route('categories.index'))
            ->assertDontSee($this->category->name);
    }
}
