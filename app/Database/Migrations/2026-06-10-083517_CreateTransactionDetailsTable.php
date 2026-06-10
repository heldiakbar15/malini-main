<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionDetailsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'transaction_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'product_variant_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'product_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'variant_size' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_variant_id', 'product_variants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('transaction_details');
    }

    public function down()
    {
        $this->forge->dropTable('transaction_details');
    }
}