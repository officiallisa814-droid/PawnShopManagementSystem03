<?php

namespace App\Models;


use App\Models\Concerns\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // role / is_active / phone / avatar ទុកចោលពី $fillable ដោយចេតនា
    // (កំណត់ដោយផ្ទាល់ក្នុង Controller ដើម្បីការពារ Mass Assignment)
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];
}