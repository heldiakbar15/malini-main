<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table         = 'transactions';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'user_id',
        'invoice_number',
        'customer_name',
        'customer_phone',
        'shipping_address',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
    ];

    protected $useTimestamps = true;
}