<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_made()
    {
        $this->assertInstanceOf('App\Models\Category', make('Category'));
    }

    /** @test */
    public function it_can_be_created()
    {
        $this->assertDatabaseHas('categories', [ 'id' => create('Category')->id ]);
    }

    /** @test */
    public function it_can_be_deleted_and_restored()
    {
        $category = create('Category');

        $category->delete();

        $this->assertTrue($category->trashed());

        $category->restore();

        $this->assertFalse($category->trashed());
    }

    /** @test */
    public function it_can_return_its_path()
    {
        $category = create('Category');

        $this->assertEquals($category->path(), route('categories.show', [
            'category' => $category
        ]));
    }
}
