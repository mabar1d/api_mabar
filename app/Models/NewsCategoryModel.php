<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsCategoryModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'news_category';
    protected $primaryKey = 'id';
    protected $fillable = [
        'parent_id',
        'name',
        'desc',
        'status',
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

    public static function countNewsCategory($filter = NULL)
    {
        $result = 0;
        $query = NewsCategoryModel::select("id");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["name"]) && $filter["name"]) {
            $query = $query->where("name", $filter["name"]);
        }
        if (isset($filter["status"]) && $filter["status"]) {
            $query = $query->where("status", $filter["status"]);
        }
        $result = $query->count();
        return $result;
    }

    public static function getNewsCategory($filter = NULL)
    {
        $result = array();
        $query = NewsCategoryModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["name"]) && $filter["name"]) {
            $query = $query->where("name", $filter["name"]);
        }
        if (isset($filter["status"]) && $filter["status"]) {
            $query = $query->where("status", $filter["status"]);
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

    public static function getListNewsCategory($filter = NULL)
    {
        $result = array();
        $query = NewsCategoryModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["name"]) && $filter["name"]) {
            $query = $query->where("name", $filter["name"]);
        }
        if (isset($filter["status"]) && $filter["status"]) {
            $query = $query->where("status", $filter["status"]);
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
