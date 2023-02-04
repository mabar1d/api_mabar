<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    use HasFactory;

    protected $table = 'personnel';
    protected $fillable = [
        'user_id',
        'firstname',
        'lastname',
        'nickname',
        'gender',
        'birthdate',
        'address',
        'sub_district_id',
        'district_id',
        'province_id',
        'zipcode',
        'team_id',
        'phone',
        'role',
        'is_verified',
        'image'
    ];
    protected $hidden = array('id', 'created_at', 'updated_at');
}
