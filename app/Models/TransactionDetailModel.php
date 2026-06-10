<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionDetailModel extends Model
{
    protected $table         = 'transaction_details';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'transaction_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_size',
        'price',
        'quantity',
        'subtotal',
    ];

    protected $useTimestamps = true;
}