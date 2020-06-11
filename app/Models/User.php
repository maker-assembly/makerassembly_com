<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $appends = ['displayName'];

    /**
     * Undocumented function
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $user->profile()->create();
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'username';
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function profile()
    {
        return $this->hasOne('App\Models\Profile');
    }

    /**
     * Set and encrypt the password attribute.
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getdisplayNameAttribute()
    {
        if (!empty($this->profile->preferred_name)) {
            return $this->profile->preferred_name;
        } else {
            if (!empty($this->profile->first_name) && !empty($this->profile->last_name)) {
                return "{$this->profile->first_name} {$this->profile->last_name}";
            }

            if (!empty($this->profile->first_name) && empty($this->profile->last_name)) {
                return $this->profile->first_name;
            }

            return "@{$this->username}";
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function path()
    {
        return route('profiles.show', [
            'user' => $this
        ]);
    }
}
