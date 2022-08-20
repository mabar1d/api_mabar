<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandingTournamentModel extends Model
{
    use HasFactory;

    protected $table = 'standing_tournament';
    protected $fillable = [
        'team_id',
        'tournament_id',
        'group',
        'point',
        'win',
        'lose',
        'draw'
    ];
    protected $hidden = array('created_at', 'updated_at');

    public static function getInfo($filter = NULL)
    {
        $result = array();
        $query = StandingTournamentModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["tournamentId"]) && $filter["tournamentId"]) {
            $query = $query->where("tournament_id", $filter["tournamentId"]);
        }
        if (isset($filter["teamId"]) && $filter["teamId"]) {
            $query = $query->where("team_id", $filter["teamId"]);
        }
        if (isset($filter["group"]) && $filter["group"]) {
            $query = $query->where("group", $filter["group"]);
        }
        $query = $query->first();
        if ($query) {
            $result = $query->toArray();
        }
        return $result;
    }

    public static function getList($filter = NULL, $offset = NULL, $limit = NULL)
    {
        $result = array();
        $query = StandingTournamentModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["tournamentId"]) && $filter["tournamentId"]) {
            $query = $query->where("tournament_id", $filter["tournamentId"]);
        }
        if (isset($filter["teamId"]) && $filter["teamId"]) {
            $query = $query->where("team_id", $filter["teamId"]);
        }
        if (isset($filter["group"]) && $filter["group"]) {
            $query = $query->where("group", $filter["group"]);
        }
        if (isset($offset) && $offset) {
            $query = $query->offset($offset);
        }
        if (isset($limit) && $limit) {
            $query = $query->limit($offset);
        }
        $query->orderBy("group", "ASC");
        $query->orderBy("updated_at", "DESC");
        $query = $query->get();
        if ($query) {
            $result = $query->toArray();
        }
        return $result;
    }
}
