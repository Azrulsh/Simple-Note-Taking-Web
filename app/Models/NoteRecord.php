<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteRecord extends Model
{
    use HasFactory;
    protected $table = 'note_record';
    protected $fillable =[ 'name', 'description', 'user_id'  ];

    // Each note belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Each note has many remarks
    public function remarks()
    {
        return $this->hasMany(RemarkRecord::class, 'note_id');
    }
}

