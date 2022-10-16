<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsTagModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'news_tag';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'created_by',
        'updated_by'
    ];
    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();

        // //create event to happen on creating
        // self::creating(function ($model) {
        //     $model->created_by = Auth::id();
        // });

        // //create event to happen on creating
        // self::updated(function ($model) {
        //     $model->updated_by = Auth::id();
        // });
    }

    public static function getCount($filter = NULL)
    {
        $result = 0;
        $query = NewsTagModel::select("id");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["name"]) && $filter["name"]) {
            $query = $query->where("name", $filter["name"]);
        }
        $result = $query->count();
        return $result;
    }

    public static function getRow($filter = NULL)
    {
        $result = array();
        $query = NewsTagModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["name"]) && $filter["name"]) {
            $query = $query->where("name", $filter["name"]);
        }
        if (isset($filter["search"]) && $filter["search"]) {
            $query = $query->where("name", 'like', $filter["search"] . '%');
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
        $query = NewsTagModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["name"]) && $filter["name"]) {
            $query = $query->where("name", $filter["name"]);
        }
        if (isset($filter["search"]) && $filter["search"]) {
            $query = $query->where("name", 'like', $filter["search"] . '%');
        }
        if (isset($filter["offset"]) && $filter["offset"]) {
            $query = $query->offset($filter["offset"]);
        }
        if (isset($filter["limit"]) && $filter["limit"]) {
            $query = $query->limit($filter["limit"]);
        }
        $query = $query->get();
        if ($query) {
            $result = $query->toArray();
        }
        return $result;
    }
}
