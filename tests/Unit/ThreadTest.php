<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ThreadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_made()
    {
        $this->assertInstanceOf('App\Models\Thread', make('Thread'));
    }

    /** @test */
    public function it_can_be_created()
    {
        $this->assertDatabaseHas('threads', [ 'id' => create('Thread')->id ]);
    }

    /** @test */
    public function it_belongs_to_an_owner()
    {
        $this->assertInstanceOf('App\Models\User', create('Thread')->owner);
    }

    /** @test */
    public function it_belongs_to_a_category()
    {
        $this->assertInstanceOf('App\Models\Category', create('Thread')->category);
    }

    /** @test */
    public function it_can_return_its_path()
    {
        $thread = create('Thread');

        $this->assertEquals($thread->path(), route('threads.show', [
            'category' => $thread->category,
            'thread' => $thread
        ]));
    }
}
