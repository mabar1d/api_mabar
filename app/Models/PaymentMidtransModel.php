<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMidtransModel extends Model
{
    use HasFactory;

    protected $table = 'payment_midtrans';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_id',
        'user_id',
        'status_code',
        'transaction_status',
        'request_body'
    ];
    protected $hidden = array('created_at', 'updated_at');
}
