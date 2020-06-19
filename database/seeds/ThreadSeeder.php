<?php

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ThreadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::all()->each(function($user) {
            factory('App\Models\Thread', 5)->create([
                'owner_id' => $user->id,
                'category_id' => Category::all()->random()->id
            ]);
        });
    }
}
