<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Reply;
use Faker\Generator as Faker;

$factory->define(Reply::class, function (Faker $faker) {
    return [
        'owner_id' => factory('App\Models\User'),
        'thread_id' => factory('App\Models\Thread'),
        'body' => $faker->paragraph
    ];
});
