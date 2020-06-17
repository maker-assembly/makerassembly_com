<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReplyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_made()
    {
        $this->assertInstanceOf('App\Models\Reply', make('Reply'));
    }

    /** @test */
    public function it_can_be_created()
    {
        $this->assertDatabaseHas('replies', [ 'id' => create('Reply')->id ]);
    }

    /** @test */
    public function it_belongs_to_an_owner()
    {
        $this->assertInstanceOf('App\Models\User', create('Reply')->owner);
    }

    /** @test */
    public function it_can_return_its_thread()
    {
        $this->assertInstanceOf('App\Models\Thread', create('Reply')->thread);
    }

    /** @test */
    public function it_can_return_its_path()
    {
        $reply = create('Reply');

        $this->assertEquals($reply->path(), route('replies.show', [
            'category' => $reply->thread->category,
            'thread' => $reply->thread,
            'reply' => $reply
        ]));
    }
}
