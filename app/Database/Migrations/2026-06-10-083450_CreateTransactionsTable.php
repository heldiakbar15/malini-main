<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionsTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'invoice_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'customer_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'customer_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'shipping_address' => [
                'type' => 'TEXT',
            ],
            'total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'payment_method' => [
                'type'       => 'ENUM',
                'constraint' => ['QRIS', 'OVO'],
                'default'    => 'QRIS',
            ],
            'payment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'paid', 'failed'],
                'default'    => 'pending',
            ],
            'order_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'processing', 'shipped', 'completed', 'cancelled'],
                'default'    => 'pending',
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('transactions');
    }

    public function down()
    {
        $this->forge->dropTable('transactions');
    }
}