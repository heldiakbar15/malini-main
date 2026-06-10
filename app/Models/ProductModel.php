<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table         = 'products';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'image'
    ];
    protected $useTimestamps = true;
}