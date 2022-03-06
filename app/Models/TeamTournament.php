<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamTournament extends Model
{
    use HasFactory;

    protected $table = 'team_tournament';
    protected $fillable = [
        'team_id',
        'tournament_id',
        'last_position',
        'active',
        'created_by'
    ];
    protected $hidden = array('created_at', 'updated_at');
}
