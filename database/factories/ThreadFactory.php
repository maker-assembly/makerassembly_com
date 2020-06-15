<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Thread;
use Faker\Generator as Faker;

$factory->define(Thread::class, function (Faker $faker) {
    return [
        'owner_id' => factory('App\Models\User'),
        'category_id' => factory('App\Models\Category'),
        'slug' => $faker->unique()->slug,
        'title' => $faker->sentence,
        'body' => $faker->paragraph
    ];
});
