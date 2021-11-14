<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterReqJoinTeam extends Model
{
    use HasFactory;

    protected $table = 'm_request_join_team';
    protected $fillable = [
        'user_id',
        'team_id',
        'answer'
    ];
    protected $hidden = array('created_at', 'updated_at');
}
