<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterTournament extends Model
{
    use HasFactory;

    protected $table = 'm_tournament';
    protected $fillable = [
        'name',
        'id_created_by',
        'start_date',
        'end_date',
        'detail',
        'number_of_participants',
        'register_date_start',
        'register_date_end',
        'prize',
        'game_id',
        'type'
    ];
    protected $hidden = array('created_at', 'updated_at');
}
