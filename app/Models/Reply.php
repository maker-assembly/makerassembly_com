<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reply extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['owner_id', 'body'];

    public function owner()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function thread()
    {
        return $this->belongsTo('App\Models\Thread');
    }

    /**
     * Returns the resource's public path.
     *
     * @return string
     */
    public function path()
    {
        return route('replies.show', [
            'category' => $this->thread->category,
            'thread' => $this->thread,
            'reply' => $this
        ]);
    }
}
