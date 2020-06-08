<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_made()
    {
        $this->assertInstanceOf('App\Models\User', make('User'));
    }

    /** @test */
    public function it_can_be_created()
    {
        $this->assertDatabaseHas('users', [ 'id' => create('User')->id ]);
    }
}
