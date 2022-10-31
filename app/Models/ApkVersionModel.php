<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApkVersionModel extends Model
{
    use HasFactory;

    protected $table = 'app_version';
    protected $primaryKey = 'id';

    public static function getRow($filter = NULL)
    {
        $result = array();
        $query = ApkVersionModel::select("id", "type", "version");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["type"]) && $filter["type"]) {
            $query = $query->where("type", $filter["type"]);
        }
        $query = $query->first();
        if ($query->first()) {
            $result = $query->toArray();
        }
        return $result;
    }

    public static function getList($filter = NULL)
    {
        $result = array();
        $query = ApkVersionModel::select("id", "type", "version");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["type"]) && $filter["type"]) {
            $query = $query->where("type", $filter["type"]);
        }
        $query = $query->get();
        if ($query->first()) {
            $result = $query->toArray();
        }
        return $result;
    }
}
