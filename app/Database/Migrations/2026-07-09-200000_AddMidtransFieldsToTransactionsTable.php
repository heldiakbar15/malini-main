<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMidtransFieldsToTransactionsTable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('transactions', [
            'payment_method' => [
                'name'       => 'payment_method',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'Midtrans',
            ],
        ]);

        $fields = [
            'snap_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'order_status',
            ],
            'midtrans_order_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'snap_token',
            ],
            'midtrans_transaction_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'midtrans_order_id',
            ],
            'midtrans_payment_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'midtrans_transaction_id',
            ],
            'midtrans_bank' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'midtrans_payment_type',
            ],
            'midtrans_va_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'midtrans_bank',
            ],
            'midtrans_transaction_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'midtrans_va_number',
            ],
            'midtrans_fraud_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'midtrans_transaction_status',
            ],
            'paid_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'midtrans_fraud_status',
            ],
        ];

        foreach ($fields as $field => $definition) {
            if (!$this->db->fieldExists($field, 'transactions')) {
                $this->forge->addColumn('transactions', [
                    $field => $definition,
                ]);
            }
        }
    }

    public function down()
    {
        foreach ([
            'paid_at',
            'midtrans_fraud_status',
            'midtrans_transaction_status',
            'midtrans_va_number',
            'midtrans_bank',
            'midtrans_payment_type',
            'midtrans_transaction_id',
            'midtrans_order_id',
            'snap_token',
        ] as $field) {
            if ($this->db->fieldExists($field, 'transactions')) {
                $this->forge->dropColumn('transactions', $field);
            }
        }
    }
}
