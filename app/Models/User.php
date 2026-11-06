<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'login_record';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Define the relationship with user(many note one user)
    public function notes()
    {
        return $this->hasMany(NoteRecord::class, 'user_id');
    }

    // Define the relationship with user(many remark one user)
    public function remarks()
    {
        return $this->hasMany(RemarkRecord::class, 'user_id');
    }
}
