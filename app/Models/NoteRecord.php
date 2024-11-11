<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteRecord extends Model
{
    use HasFactory;
    protected $table = 'note_record';
    protected $fillable =[ 'name', 'description',  ];
}
