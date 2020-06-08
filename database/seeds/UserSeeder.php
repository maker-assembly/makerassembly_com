<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create a test user
        factory(User::class)->create([
            'username' => 'make.the.future',
            'email' => 'user@example.com'
        ]);

        // create 50 random test users
        factory(User::class, 50)->create();
    }
}
