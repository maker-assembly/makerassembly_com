<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $fillable = [
        'owner_id', 'category_id', 'title', 'slug', 'body'
    ];

    public function owner()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    /**
     * Returns the resource's public path.
     *
     * @return void
     */
    public function path()
    {
        return route('threads.show', [
            'category' => $this->category,
            'thread' => $this
        ]);
    }
}
