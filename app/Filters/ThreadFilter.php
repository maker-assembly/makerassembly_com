<?php

namespace App\Filters;

use App\Models\User;

class ThreadFilter extends Filter
{
    protected $filters = ['by', 'popular', 'unanswered'];

    protected function by($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        return $this->builder->where('owner_id', $user->id);
    }
}
