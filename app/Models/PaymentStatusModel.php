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
        'transaction_status'
    ];
    protected $hidden = array('created_at', 'updated_at');
}
