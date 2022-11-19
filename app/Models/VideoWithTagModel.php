<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VideoWithTagModel extends Model
{
    use HasFactory;
    // use SoftDeletes;

    protected $table = 'video_with_tag';
    protected $primaryKey = 'video_tag_id';
    protected $fillable = [
        'video_id',
        'tag_id',
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
        $query = VideoWithTagModel::select("video_tag_ids");
        if (isset($filter["videoId"]) && $filter["videoId"]) {
            $query = $query->where("video_id", $filter["videoId"]);
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
        $query = VideoWithTagModel::select("*");
        if (isset($filter["videoId"]) && $filter["videoId"]) {
            $query = $query->where("video_id", $filter["videoId"]);
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
        $query = VideoWithTagModel::select("*");
        if (isset($filter["videoId"]) && $filter["videoId"]) {
            $query = $query->where("video_id", $filter["videoId"]);
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

    public static function getListJoinVideoTag($filter = NULL)
    {
        $result = array();
        $query = VideoWithTagModel::select("video_with_tag.video_id", "video_with_tag.tag_id", "tag.name");
        $query = $query->leftJoin("tag", "video_with_tag.tag_id", "=", "tag.id");
        if (isset($filter["videoId"]) && $filter["videoId"]) {
            $query = $query->where("video_with_tag.video_id", $filter["videoId"]);
        }
        if (isset($filter["tagId"]) && $filter["tagId"]) {
            $query = $query->where("video_with_tag.tag_id", $filter["tagId"]);
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
