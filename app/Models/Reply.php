<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    protected $fillable = ['owner_id', 'body'];

    public function owner()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function thread()
    {
        return $this->belongsTo('App\Models\Thread');
    }

    public function path()
    {
        return route('replies.show', [
            'category' => $this->thread->category,
            'thread' => $this->thread,
            'reply' => $this
        ]);
    }
}
