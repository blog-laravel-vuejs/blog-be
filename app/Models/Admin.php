<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class Admin extends Authenticatable implements JWTSubject 
{
    use HasFactory,HasApiTokens,Notifiable;
    protected $fillable = [
        'id',
        'email',
        'password',
        'name',
        'avatar',
        'role',
        'email_verified_at',
        'token_verify_email',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'token_verify_email',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
