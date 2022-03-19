<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingTournament extends Model
{
    use HasFactory;

    protected $table = 'rating_tournament';
    protected $fillable = [
        'id_tournament',
        'id_team',
        'id_rater',
        'rating',
        'notes'
    ];
    protected $hidden = array('created_at', 'updated_at');
}
