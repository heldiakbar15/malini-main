<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFeaturedToProductsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('products', [
            'is_featured' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'image',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('products', 'is_featured');
    }
}
