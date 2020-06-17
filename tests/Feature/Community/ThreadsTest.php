<?php

namespace Tests\Feature\Community;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ThreadsTest extends TestCase
{
    use RefreshDatabase;

    protected $thread1;

    protected $thread2;

    public function setUp(): void
    {
        parent::setUp();

        $this->thread1 = create('Thread', [
            'title' => 'Thread 1'
        ]);

        $this->thread2 = create('Thread', [
            'title' => 'Thread 2'
        ]);
    }

    /** @test */
    public function anyone_can_view_all_threads()
    {
        $this->get(route('threads.index'))
            ->assertStatus(200)
            ->assertSee($this->thread1->title)
            ->assertSee($this->thread2->title);
    }

    /** @test */
    public function anyone_can_view_all_category_threads()
    {
        $this->get(route('threads.index', [
            'category' => $this->thread1->category
        ]))
            ->assertStatus(200)
            ->assertSee($this->thread1->title)
            ->assertDontSee($this->thread2->title);
    }

    /** @test */
    public function anyone_can_view_all_threads_by_author()
    {
        $this->get(route('threads.index', [
            'community' => $this->thread1->community,
            'by' => $this->thread1->owner->username
        ]))
            ->assertSee($this->thread1->title)
            ->assertDontSee($this->thread2->title);
    }

    /** @test */
    public function anyone_can_view_a_single_thread()
    {
        $this->get(route('threads.show', [
            'category' => $this->thread1->category,
            'thread' => $this->thread1
        ]))
            ->assertStatus(200)
            ->assertSee($this->thread1->title);
    }

    /** @test */
    public function a_guest_cannot_create_a_thread()
    {
        $this->get(route('threads.create'))
            ->assertRedirect(route('login'));

        $this->post(route('threads.store'), [])
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_edit_a_thread()
    {
        $parameters = [
            'category' => $this->thread1->category,
            'thread' => $this->thread1
        ];

        $this->get(route('threads.edit', $parameters))
            ->assertRedirect(route('login'));

        $this->patch(route('threads.update', $parameters), [])
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_delete_a_thread()
    {
        $parameters = [
            'category' => $this->thread1->category,
            'thread' => $this->thread1
        ];

        // $this->delete(route('threads.delete', $parameters))
        //     ->assertRedirect(route('login'));

        $this->delete(route('threads.destroy', $parameters))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_user_can_create_a_thread()
    {
        $this->signIn();

        $this->get(route('threads.create'))
            ->assertStatus(200);

        $response = $this->post(route('threads.store'), $attributes = raw('Thread'));

        $this->get($response->headers->get('Location'))
            ->assertSee($attributes['title']);
    }

    /** @test */
    public function a_user_cannot_edit_a_thread()
    {
        $this->signIn();

        $this->get(route('threads.edit', [
            'category' => $this->thread1->category,
            'thread' => $this->thread1
        ]))
            ->assertStatus(403);

        $this->patch(route('threads.update', [
            'category' => $this->thread1->category,
            'thread' => $this->thread1
        ]), [])
            ->assertStatus(403);

    }

    /** @test */
    public function a_user_cannot_delete_a_thread()
    {
        $this->signIn();

        // $this->delete(route('threads.delete', [
        //     'category' => $this->thread1->category,
        //     'thread' => $this->thread1
        // ]))
        //     ->assertStatus(403);

        $this->delete(route('threads.destroy', [
            'category' => $this->thread1->category,
            'thread' => $this->thread1
        ]))
            ->assertStatus(403);
    }

    /** @test */
    public function the_thread_owner_can_edit_their_thread()
    {
        $this->signIn($this->thread1->owner);

        $this->get(route('threads.edit', [
            'category' => $this->thread1->category,
            'thread' => $this->thread1
        ]))
            ->assertStatus(200);

        $response = $this->patch(route('threads.update', [
            'category' => $this->thread1->category,
            'thread' => $this->thread1
        ]), $attributes = raw('Thread'));

        $this->get($response->headers->get('Location'))
            ->assertStatus(200)
            ->assertSee($attributes['title']);
    }

    /** @test */
    public function the_thread_owner_can_delete_their_thread()
    {
        $this->signIn($this->thread1->owner);

        // $this->delete(route('threads.delete', [
        //     'category' => $this->thread1->category,
        //     'thread' => $this->thread1
        // ]))
        //     ->assertStatus(200);

        $this->delete(route('threads.destroy', [
            'category' => $this->thread1->category,
            'thread' => $this->thread1
        ]))
            ->assertStatus(200);

        $this->get(route('threads.index'))
            ->assertDontSee($this->thread1->title);
    }
}
