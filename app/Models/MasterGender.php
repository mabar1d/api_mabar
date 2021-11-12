<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterGender extends Model
{
    use HasFactory;

    protected $table = 'm_gender';
    protected $primaryKey = 'gender_id';
    protected $fillable = [
        'gender'
    ];
}
