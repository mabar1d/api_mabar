<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterTeam extends Model
{
    use HasFactory;

    protected $table = 'm_team';
    protected $fillable = [
        'name',
        'info',
        'admin_id',
        'personnel',
        'photo'
    ];
    protected $hidden = array('created_at', 'updated_at');
}
