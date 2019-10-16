<?php

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Order::create([
            'products' => [1, 2],
            'price' => 6.25,
            'user_id' => 1,
            'payment_id' => 'tr_b7UcUe7svr',
            'state' => 'paid',
        ]);
    }
}
