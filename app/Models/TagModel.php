<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TagModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tag';
    protected $primaryKey = 'id_tag';
    protected $fillable = [
        'tag_name',
        'created_by',
        'updated_by'
    ];

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

    public static function countTag($filter = NULL)
    {
        $result = 0;
        $query = TagModel::select("id_tag");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id_tag", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("tag_name", $filter["title"]);
        }
        $result = $query->count();
        return $result;
    }

    public static function getTag($filter = NULL)
    {
        $result = array();
        $query = TagModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id_tag", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("tag_name", $filter["title"]);
        }
        if (isset($filter["search"]) && $filter["search"]) {
            $query = $query->where("tag_name", 'like', $filter["search"] . '%');
        }
        $query = $query->first();
        if ($query) {
            $result = $query->toArray();
        }
        return $result;
    }

    public static function getListTag($filter = NULL)
    {
        $result = array();
        $query = TagModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id_tag", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("tag_name", $filter["title"]);
        }
        if (isset($filter["title_array"]) && $filter["title_array"]) {
            $query = $query->whereIn("tag_name", $filter["title_array"]);
        }
        if (isset($filter["search"]) && $filter["search"]) {
            $query = $query->where("title", 'like', $filter["search"] . '%');
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
