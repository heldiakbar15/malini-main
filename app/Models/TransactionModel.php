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

        'snap_token',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'midtrans_bank',
        'midtrans_va_number',
        'midtrans_transaction_status',
        'midtrans_fraud_status',
        'paid_at',
    ];

    protected $useTimestamps = true;
}