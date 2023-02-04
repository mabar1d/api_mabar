<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralModel extends Model
{
    use HasFactory;

    protected $table = 'general_info';
    protected $primaryKey = 'id';
    protected $fillable = [
        'type',
        'desc',
        'status'
    ];
}
