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
        'image',
        'game_id'
    ];
    protected $hidden = array('created_at', 'updated_at');

    public static function getInfo($filter = NULL)
    {
        $result = array();
        $query = MasterTeam::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["teamId"]) && $filter["teamId"]) {
            $query = $query->where("team_id", $filter["teamId"]);
        }
        $query = $query->first();
        if ($query) {
            $result = $query->toArray();
        }
        return $result;
    }
}
