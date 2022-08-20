<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandingTournamentMatchModel extends Model
{
    use HasFactory;

    protected $table = 'standing_tournament_match';
    protected $fillable = [
        'tournament_id',
        'home_team_id',
        'opponent_team_id',
        'score_home_team',
        'score_opponent_team',
        'playing_date',
        'has_played'
    ];
    protected $hidden = array('created_at', 'updated_at');

    public static function getInfo($filter = NULL)
    {
        $result = array();
        $query = StandingTournamentMatchModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["tournamentId"]) && $filter["tournamentId"]) {
            $query = $query->where("tournament_id", $filter["tournamentId"]);
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
        $query = StandingTournamentMatchModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["tournamentId"]) && $filter["tournamentId"]) {
            $query = $query->where("tournament_id", $filter["tournamentId"]);
        }
        if (isset($offset) && $offset) {
            $query = $query->offset($offset);
        }
        if (isset($limit) && $limit) {
            $query = $query->limit($offset);
        }
        $query->orderBy("updated_at", "desc");
        $query = $query->get();
        if ($query) {
            $result = $query->toArray();
        }
        return $result;
    }
}
