<?php

namespace Tests\Feature\Community;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RepliesTest extends TestCase
{
    use RefreshDatabase;

    protected $reply;

    public function setUp(): void
    {
        parent::setUp();

        $this->reply = create('Reply');
    }

    /** @test */
    public function anyone_can_view_all_thread_replies()
    {
        $this->get(route('threads.show', [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread
        ]))
            ->assertSee($this->reply->body);
    }

    /** @test */
    public function anyone_can_view_a_single_thread_reply()
    {
        $this->get($this->reply->path())
            ->assertSee($this->reply->body);
    }

    /** @test */
    public function a_guest_cannot_create_a_reply()
    {
        $parameters = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread
        ];

        $this->get(route('replies.create', $parameters))
            ->assertRedirect(route('login'));

        $this->post(route('replies.store', $parameters), [])
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_edit_a_reply()
    {
        $parameters = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread,
            'reply' => $this->reply
        ];

        $this->get(route('replies.edit', $parameters))
            ->assertRedirect(route('login'));

        $this->patch(route('replies.update', $parameters), [])
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_delete_a_reply()
    {
        $parameters = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread,
            'reply' => $this->reply
        ];

        // $this->delete(route('replies.delete', $parameters))
        //     ->assertRedirect(route('login'));

        $this->delete(route('replies.destroy', $parameters))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_user_can_create_a_reply()
    {
        $this->signIn();

        $parameters = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread
        ];

        $this->get(route('replies.create', $parameters))
            ->assertStatus(200);

        $response = $this->post(route('replies.store', $parameters), $attributes = raw('Reply'));

        $this->get($response->headers->get('Location'))
            ->assertSee($attributes['body']);
    }

    /** @test */
    public function a_user_cannot_edit_a_reply()
    {
        $this->signIn();

        $parameters = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread,
            'reply' => $this->reply,
        ];

        $this->get(route('replies.edit', $parameters))
            ->assertStatus(403);

        $this->patch(route('replies.update', $parameters))
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_delete_a_reply()
    {
        $this->signIn();

        $parameters = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread,
            'reply' => $this->reply,
        ];

        // $this->delete(route('replies.delete', $parameters))
        //     ->assertStatus(403);

        $this->delete(route('replies.destroy', $parameters))
            ->assertStatus(403);
    }

    /** @test */
    public function the_reply_owner_can_edit_their_reply()
    {
        $this->signIn($this->reply->owner);

        $parameters = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread,
            'reply' => $this->reply,
        ];

        $this->get(route('replies.edit', $parameters))
            ->assertStatus(200);

        $response = $this->patch(route('replies.update', $parameters), $attributes = raw('Reply'));

        $this->get($response->headers->get('Location'))
            ->assertSee($attributes['body']);
    }

    /** @test */
    public function the_reply_owner_can_delete_their_reply()
    {
        $this->signIn($this->reply->owner);

        $parameters = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread,
            'reply' => $this->reply,
        ];

        // $this->delete(route('replies.delete', $parameters))
        //     ->assertStatus(200);

        $this->delete(route('replies.destroy', $parameters))
            ->assertStatus(200);

        $this->get(route('threads.show', $parameters))
            ->assertDontSee($this->reply->body);
    }
}
