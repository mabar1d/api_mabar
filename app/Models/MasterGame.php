<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterGame extends Model
{
    use HasFactory;

    protected $table = 'm_game';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title',
        'image'
    ];
}
