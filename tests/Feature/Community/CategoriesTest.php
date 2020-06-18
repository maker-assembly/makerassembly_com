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

        $this->post(route('categories.store'), [])
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_edit_a_category()
    {
        $this->get(route('categories.edit', $params = [
            'category' => $this->category
        ]))
            ->assertRedirect(route('login'));

        $this->patch(route('categories.update', $params), [])
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_delete_or_destroy_a_category()
    {
        $this->delete(route('categories.delete', $params = [
            'id' => $this->category
        ]))
            ->assertRedirect(route('login'));

        $this->delete(route('categories.destroy', $params))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_restore_a_category()
    {
        $this->category->delete();

        $this->patch(route('categories.restore', [
            'id' => $this->category->id
        ]), [])
            ->assertRedirect(route('login'));

        $this->assertTrue($this->category->fresh()->trashed());
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

        $this->get(route('categories.edit', $params = [
            'category' => $this->category
        ]))
            ->assertStatus(403);

        $this->patch(route('categories.update', $params), [])
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_delete_or_destroy_a_category()
    {
        $this->signIn();

        $this->delete(route('categories.delete', $params = [
            'id' => $this->category->id
        ]))
            ->assertStatus(403);

        $this->delete(route('categories.destroy', $params))
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_restore_a_category()
    {
        $this->category->delete();

        $this->signIn();

        $this->patch(route('categories.restore', [
            'id' => $this->category->id
        ]), [])
            ->assertStatus(403);

        $this->assertTrue($this->category->fresh()->trashed());
    }

    /** @test */
    public function a_moderator_can_create_categories()
    {
        $this->signIn($this->moderator);

        $this->get(route('categories.create'))
            ->assertStatus(200);

        $response = $this->post(route('categories.store'), $attributes = raw('Category'));

        $this->get($response->headers->get('Location'))
            ->assertSee($attributes['name']);
    }

    /** @test */
    public function a_moderator_can_edit_categories()
    {
        $this->signIn($this->moderator);

        $this->get(route('categories.edit', $params = [
            'category' => $this->category
        ]))
            ->assertStatus(200);

        $response = $this->patch(route('categories.update', $params), $attributes = raw('Category'));

        $this->get($response->headers->get('Location'))
            ->assertSee($attributes['name']);
    }

    /** @test */
    public function a_moderator_can_delete_a_category()
    {
        $this->signIn($this->moderator);

        $this->delete(route('categories.delete', [
            'id' => $this->category->id
        ]))
            ->assertStatus(200);

        $this->assertTrue($this->category->fresh()->trashed());
    }

    /** @test */
    public function a_moderator_can_restore_a_category()
    {
        $this->category->delete();

        $this->signIn($this->moderator);

        $this->patch(route('categories.restore', [
            'id' => $this->category->id
        ]), []);

        $this->assertFalse($this->category->fresh()->trashed());
    }

    /** @test */
    public function a_moderator_can_destroy_a_category()
    {
        $this->signIn($this->moderator);

        $this->delete(route('categories.destroy', [
            'id' => $this->category->id
        ]));

        $this->assertDatabaseMissing('categories', $this->category->toArray());
    }
}
