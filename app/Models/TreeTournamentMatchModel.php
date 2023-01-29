<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreeTournamentMatchModel extends Model
{
    use HasFactory;

    protected $table = 'tree_tournament_match';
    protected $fillable = [
        'tournament_id',
        'tournament_phase',
        'round',
        'home_team_id',
        'opponent_team_id',
        'score_home',
        'score_opponent',
        'playing_date'
    ];
    protected $hidden = array('created_at', 'updated_at');

    public static function getInfo($filter = NULL)
    {
        $result = array();
        $query = TreeTournamentMatchModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["tournamentId"]) && $filter["tournamentId"]) {
            $query = $query->where("tournament_id", $filter["tournamentId"]);
        }
        if (isset($filter["tournamentPhase"]) && $filter["tournamentPhase"]) {
            $query = $query->where("tournament_phase", $filter["tournamentPhase"]);
        }
        if (isset($filter["tournamentRound"]) && $filter["tournamentRound"]) {
            $query = $query->where("round", $filter["tournamentRound"]);
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
        $query = TreeTournamentMatchModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["tournamentId"]) && $filter["tournamentId"]) {
            $query = $query->where("tournament_id", $filter["tournamentId"]);
        }
        if (isset($filter["tournamentPhase"]) && $filter["tournamentPhase"]) {
            $query = $query->where("tournament_phase", $filter["tournamentPhase"]);
        }
        if (isset($filter["tournamentRound"]) && $filter["tournamentRound"]) {
            $query = $query->where("round", $filter["tournamentRound"]);
        }
        if (isset($offset) && $offset) {
            $query = $query->offset($offset);
        }
        if (isset($limit) && $limit) {
            $query = $query->limit($offset);
        }
        $query->orderBy("tournament_phase", "ASC");
        $query->orderBy("round", "ASC");
        $query = $query->get();
        if ($query) {
            $result = $query->toArray();
        }
        return $result;
    }
}
