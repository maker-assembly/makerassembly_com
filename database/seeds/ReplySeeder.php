<?php

use App\Models\User;
use App\Models\Thread;
use Illuminate\Database\Seeder;

class ReplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Thread::all()->each(function($thread) {
            factory('App\Models\Reply', 5)->create([
                'owner_id' => User::all()->random()->id,
                'thread_id' => $thread->id
            ]);
        });
    }
}
