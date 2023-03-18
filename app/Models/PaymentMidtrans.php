<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMidtrans extends Model
{
    use HasFactory;

    protected $table = 'payment_midtrans';
    protected $fillable = [
        'order_id',
        'request_body'
    ];
    // protected $hidden = array('created_at', 'updated_at');
}
