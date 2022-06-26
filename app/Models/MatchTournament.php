<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchTournament extends Model
{
    use HasFactory;

    protected $table = 'match_tournament';
    protected $fillable = [
        'tournament_id',
        'tournament_phase',
        'round',
        'home_team_id',
        'opponent_team_id',
        'score_home',
        'score_opponent',
    ];
    protected $hidden = array('created_at', 'updated_at');
}
