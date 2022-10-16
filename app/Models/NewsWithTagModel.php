<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsWithTagModel extends Model
{
    use HasFactory;
    // use SoftDeletes;

    protected $table = 'news_with_tag';
    protected $primaryKey = 'id';
    protected $fillable = [
        'news_id',
        'news_tag_id',
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
        $query = NewsWithTagModel::select("id");
        if (isset($filter["newsId"]) && $filter["newsId"]) {
            $query = $query->where("news_id", $filter["newsId"]);
        }
        if (isset($filter["tagId"]) && $filter["tagId"]) {
            $query = $query->where("tag_id", $filter["tagId"]);
        }
        $result = $query->count();
        return $result;
    }

    public static function getRow($filter = NULL)
    {
        $result = array();
        $query = NewsWithTagModel::select("*");
        if (isset($filter["newsId"]) && $filter["newsId"]) {
            $query = $query->where("news_id", $filter["newsId"]);
        }
        if (isset($filter["tagId"]) && $filter["tagId"]) {
            $query = $query->where("tag_id", $filter["tagId"]);
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
        $query = NewsWithTagModel::select("*");
        if (isset($filter["newsId"]) && $filter["newsId"]) {
            $query = $query->where("news_id", $filter["newsId"]);
        }
        if (isset($filter["tagId"]) && $filter["tagId"]) {
            $query = $query->where("tag_id", $filter["tagId"]);
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

    public static function getListJoinNewsTag($filter = NULL)
    {
        $result = array();
        $query = NewsWithTagModel::select("news_with_tag.news_id", "news_with_tag.news_tag_id", "news_tag.name");
        $query = $query->leftJoin("news_tag", "news_with_tag.news_tag_id", "=", "news_tag.id");
        if (isset($filter["newsId"]) && $filter["newsId"]) {
            $query = $query->where("news_with_tag.news_id", $filter["newsId"]);
        }
        if (isset($filter["tagId"]) && $filter["tagId"]) {
            $query = $query->where("news_with_tag.tag_id", $filter["tagId"]);
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
