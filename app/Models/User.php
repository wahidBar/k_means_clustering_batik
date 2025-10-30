<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'role_id',
        'email',
        'password',
        'full_name',
        'image',
        'address',
        'contact'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function setPasswordAttribute($value)
    {
        if ($value && \Illuminate\Support\Str::length($value) !== 60) {
            $this->attributes['password'] = bcrypt($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }


    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function partner()
    {
        return $this->hasOne(BatikUmkmPartner::class, 'user_id', 'id');
    }


    public function validationHistories()
    {
        return $this->hasMany(ValidationHistory::class, 'user_id', 'id');
    }


    public function isRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    // JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function validatedProductions()
    {
        return $this->hasMany(MonthlyProduction::class, 'validated_by');
    }
}
