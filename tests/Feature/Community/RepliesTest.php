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
        $this->get(route('replies.create', $params = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread
        ]))
            ->assertRedirect(route('login'));

        $this->post(route('replies.store', $params), [])
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_edit_a_reply()
    {
        $this->get(route('replies.edit', $params = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread,
            'reply' => $this->reply
        ]))
            ->assertRedirect(route('login'));

        $this->patch(route('replies.update', $params), [])
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_delete_or_destroy_a_reply()
    {
        $this->delete(route('replies.delete', $params = [
            'id' => $this->reply->id
        ]))
            ->assertRedirect(route('login'));

        $this->delete(route('replies.destroy', $params))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function a_guest_cannot_restore_a_reply()
    {
        $this->reply->delete();

        $this->patch(route('replies.restore', [
            'id' => $this->reply->id
        ]))
            ->assertRedirect(route('login'));

        $this->assertTrue($this->reply->fresh()->trashed());
    }

    /** @test */
    public function a_user_can_create_a_reply()
    {
        $this->signIn();

        $this->get(route('replies.create', $params = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread
        ]))
            ->assertStatus(200);

        $response = $this->post(route('replies.store', $params), $attributes = raw('Reply'));

        $this->get($response->headers->get('Location'))
            ->assertSee($attributes['body']);
    }

    /** @test */
    public function a_user_cannot_edit_a_reply()
    {
        $this->signIn();

        $this->get(route('replies.edit', $params = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread,
            'reply' => $this->reply,
        ]))
            ->assertStatus(403);

        $this->patch(route('replies.update', $params))
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_delete_or_destroy_a_reply()
    {
        $this->signIn();

        $this->delete(route('replies.delete', $params = [
            'id' => $this->reply->id,
        ]))
            ->assertStatus(403);

        $this->delete(route('replies.destroy', $params))
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_restore_a_reply()
    {
        $this->reply->delete();

        $this->signIn();

        $this->patch(route('replies.restore', [
            'id' => $this->reply->id
        ]))
            ->assertStatus(403);

        $this->assertTrue($this->reply->fresh()->trashed());
    }

    /** @test */
    public function the_reply_owner_can_edit_their_reply()
    {
        $this->signIn($this->reply->owner);

        $this->get(route('replies.edit', $params = [
            'category' => $this->reply->thread->category,
            'thread' => $this->reply->thread,
            'reply' => $this->reply,
        ]))
            ->assertStatus(200);

        $response = $this->patch(route('replies.update', $params), $attributes = raw('Reply'));

        $this->get($response->headers->get('Location'))
            ->assertSee($attributes['body']);
    }

    /** @test */
    public function the_reply_owner_can_delete_their_reply()
    {
        $this->signIn($this->reply->owner);

        $this->delete(route('replies.delete', [
            'id' => $this->reply->id,
        ]))
            ->assertStatus(200);

        $this->assertTrue($this->reply->fresh()->trashed());
    }

    /** @test */
    public function the_reply_owner_can_restore_their_reply()
    {
        $this->signIn($this->reply->owner);

        $this->reply->delete();

        $this->patch(route('replies.restore', [
            'id' => $this->reply->id
        ]), []);

        $this->assertFalse($this->reply->fresh()->trashed());
    }

    /** @test */
    public function the_reply_owner_cannot_destroy_their_reply()
    {
        $this->signIn($this->reply->owner);

        $this->delete(route('replies.destroy', [
            'id' => $this->reply->id
        ]))
            ->assertStatus(403);

        $this->assertDatabaseHas('replies', ['id' => $this->reply->id]);
    }
}
