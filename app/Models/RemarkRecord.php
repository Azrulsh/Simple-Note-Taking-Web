<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RemarkRecord extends Model
{
    use HasFactory;
    protected $table = 'remark_record';
    protected $fillable =[ 'description', 'note_id', 'user_id' ];

    // Each remark belongs to one note
    public function note()
    {
        return $this->belongsTo(NoteRecord::class, 'note_id');
    }

    // Each remark belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
