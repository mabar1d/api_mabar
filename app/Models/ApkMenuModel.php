<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApkMenuModel extends Model
{
    use HasFactory;

    protected $table = 'apk_menu';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title',
        'order',
        'status'
    ];

    public static function getListApkMenu($filter = NULL)
    {
        $result = array();
        $query = ApkMenuModel::select("id", "title", "order");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("title", $filter["title"]);
        }
        if (isset($filter["status"])) {
            $query = $query->where("status", $filter["status"]);
        }
        $query = $query->orderBy("order");
        $query = $query->get();
        if ($query->first()) {
            $result = $query->toArray();
        }

        return $result;
    }
}
