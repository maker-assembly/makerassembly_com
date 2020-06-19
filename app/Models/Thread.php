<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thread extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'owner_id', 'category_id', 'title', 'slug', 'body'
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function scopeFilter($query, $filter)
    {
        return $filter->apply($query);
    }

    public function owner()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function replies()
    {
        return $this->hasMany('App\Models\Reply');
    }

    /**
     * Returns the resource's public path.
     *
     * @return string
     */
    public function path()
    {
        return route('threads.show', [
            'category' => $this->category,
            'thread' => $this
        ]);
    }
}
