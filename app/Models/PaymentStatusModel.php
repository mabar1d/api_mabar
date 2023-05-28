<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentStatusModel extends Model
{
    use HasFactory;

    protected $table = 'payment_status';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'order_id',
        'status_code',
        'transaction_status',
        'transaction_time',
        'settlement_time',
        'expiry_time',
        'gross_amount',
        'payment_type',
        'bank_name',
        'va_number'
    ];
    protected $hidden = array('created_at', 'updated_at');
}
