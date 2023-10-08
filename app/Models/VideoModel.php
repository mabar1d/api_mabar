<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VideoModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'video';
    protected $primaryKey = 'video_id';
    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'link',
        'notify',
        'status',
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

    public function getCreatedAtAttribute() //to show created_at column
    {
        return Carbon::parse($this->attributes['created_at'])
            ->format('d M Y H:i');
    }

    public function getUpdatedAtAttribute() //to show updated_at column
    {
        return Carbon::parse($this->attributes['updated_at'])
            ->format('d M Y H:i');
    }

    public function category()
    {
        return $this->hasOne(CategoryModel::class, 'id', 'category_id');
    }

    public function pivotVideoTags()
    {
        return $this->belongsToMany(
            TagsModel::class,
            ContentTagsModel::class,
            'content_id',
            'tag_id'
        );
    }

    public static function countVideo($filter = NULL)
    {
        $result = 0;
        $query = VideoModel::select("id");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("video_id", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("title", $filter["title"]);
        }
        if (isset($filter["slug"]) && $filter["slug"]) {
            $query = $query->where("slug", $filter["slug"]);
        }
        if (isset($filter["status"]) && $filter["status"]) {
            $query = $query->where("status", $filter["status"]);
        }
        $result = $query->count();
        return $result;
    }

    public static function getNews($filter = NULL)
    {
        $result = array();
        $query = VideoModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("video_id", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("title", $filter["title"]);
        }
        if (isset($filter["slug"]) && $filter["slug"]) {
            $query = $query->where("slug", $filter["slug"]);
        }
        if (isset($filter["status"]) && $filter["status"]) {
            $query = $query->where("status", $filter["status"]);
        }
        if (isset($filter["search"]) && $filter["search"]) {
            $query = $query->where("title", 'like', $filter["search"] . '%');
        }
        $query = $query->first();
        if ($query) {
            $result = $query->toArray();
        }
        return $result;
    }

    public static function getListVideo($filter = NULL)
    {
        $result = array();
        $query = VideoModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("video_id", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("title", $filter["title"]);
        }
        if (isset($filter["status"]) && $filter["status"]) {
            $query = $query->where("status", $filter["status"]);
        }
        if (isset($filter["slug"]) && $filter["slug"]) {
            $query = $query->where("slug", $filter["slug"]);
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

    public static function getVideoDetail($filter = NULL)
    {
        $result = array();
        $query = VideoModel::select("video.*", "personnel.firstname", "personnel.lastname");
        $query = $query->leftJoin("personnel", "video.created_by", "=", "personnel.user_id");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("video.video_id", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("video.title", $filter["title"]);
        }
        if (isset($filter["status"]) && $filter["status"]) {
            $query = $query->where("video.status", $filter["status"]);
        }
        if (isset($filter["slug"]) && $filter["slug"]) {
            $query = $query->where("video.slug", $filter["slug"]);
        }
        if (isset($filter["search"]) && $filter["search"]) {
            $query = $query->where("video.title", 'like', $filter["search"] . '%');
        }
        $query = $query->first();
        if ($query) {
            $result = $query->toArray();
        }
        return $result;
    }

    public static function getListVideoDetail($filter = NULL)
    {
        $result = array();
        $query = VideoModel::select("video.*", "personnel.firstname", "personnel.lastname");
        $query = $query->leftJoin("personnel", "video.created_by", "=", "personnel.user_id");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("video.video_id", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("video.title", $filter["title"]);
        }
        if (isset($filter["slug"]) && $filter["slug"]) {
            $query = $query->where("video.slug", $filter["slug"]);
        }
        if (isset($filter["status"]) && $filter["status"]) {
            $query = $query->where("video.status", $filter["status"]);
        }
        if (isset($filter["search"]) && $filter["search"]) {
            $query = $query->where("video.title", 'like', $filter["search"] . '%');
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
