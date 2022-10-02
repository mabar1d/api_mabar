<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'news';
    protected $primaryKey = 'id';
    protected $fillable = [
        'news_category_id',
        'title',
        'slug',
        'content',
        'image',
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

    public static function countNews($filter = NULL)
    {
        $result = 0;
        $query = NewsModel::select("id");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("title", $filter["title"]);
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
        $query = NewsModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("title", $filter["title"]);
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

    public static function getListNews($filter = NULL)
    {
        $result = array();
        $query = NewsModel::select("*");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("id", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("title", $filter["title"]);
        }
        if (isset($filter["status"]) && $filter["status"]) {
            $query = $query->where("status", $filter["status"]);
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

    public static function getNewsDetail($filter = NULL)
    {
        $result = array();
        $query = NewsModel::select("news.*", "personnel.firstname", "personnel.lastname");
        $query = $query->leftJoin("personnel", "news.created_by", "=", "personnel.user_id");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("news.id", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("news.title", $filter["title"]);
        }
        if (isset($filter["status"]) && $filter["status"]) {
            $query = $query->where("news.status", $filter["status"]);
        }
        if (isset($filter["search"]) && $filter["search"]) {
            $query = $query->where("news.title", 'like', $filter["search"] . '%');
        }
        $query = $query->first();
        if ($query) {
            $result = $query->toArray();
        }
        return $result;
    }

    public static function getListNewsDetail($filter = NULL)
    {
        $result = array();
        $query = NewsModel::select("news.*", "personnel.firstname", "personnel.lastname");
        $query = $query->leftJoin("personnel", "news.created_by", "=", "personnel.user_id");
        if (isset($filter["id"]) && $filter["id"]) {
            $query = $query->where("news.id", $filter["id"]);
        }
        if (isset($filter["title"]) && $filter["title"]) {
            $query = $query->where("news.title", $filter["title"]);
        }
        if (isset($filter["status"]) && $filter["status"]) {
            $query = $query->where("news.status", $filter["status"]);
        }
        if (isset($filter["search"]) && $filter["search"]) {
            $query = $query->where("news.title", 'like', $filter["search"] . '%');
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
