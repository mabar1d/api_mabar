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
        'register_fee',
        'prize',
        'game_id',
        'type',
        'image'
    ];
    protected $hidden = array('created_at', 'updated_at');

    public static function getInfo($filter = NULL)
    {
        $result = array();
        $query = MasterTournament::select('*');
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        $query = $query->first();
        if ($query) {
            $result = $query->toArray();
        }
        return $result;
    }

    public static function getList($filter = NULL)
    {
        $result = array();
        $query = MasterTournament::select('*');
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        $query = $query->get();
        if ($query) {
            $result = $query->toArray();
        }
        return $result;
    }
}
