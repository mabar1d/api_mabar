<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobNotifFirebaseModel extends Model
{
    use HasFactory;

    protected $table = 'job_notif_firebase';
    protected $primaryKey = 'id';
    protected $fillable = [
        'notif_type',
        'client_key',
        'notif_title',
        'notif_body',
        'notif_img_url',
        'notif_url',
        'status'
    ];
    protected $hidden = array('created_at', 'updated_at');

    public static function getList($filter = NULL, $offset = NULL, $limit = NULL)
    {
        $result = array();
        $query = JobNotifFirebaseModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["tournamentId"]) && $filter["tournamentId"]) {
            $query = $query->where("tournament_id", $filter["tournamentId"]);
        }
        $query = $query->where("status", isset($filter["tournamentId"]) && $filter["tournamentId"] ? $filter["tournamentId"] : 0);
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
