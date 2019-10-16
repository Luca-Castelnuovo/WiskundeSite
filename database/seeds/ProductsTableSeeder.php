<?php

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Product::create([
            'user_id' => 2,
            'name' => 'Hoofdstuk 8 Oefentoets',
            'price' => 2.50,
            'subject' => 'Wiskunde A',
            'class' => '4vwo',
            'method' => 'Getal en Ruimte',
            'fileKey' => 'FILEKEY',
            'state' => 'accepted',
        ]);

        Product::create([
            'user_id' => 2,
            'name' => 'Hoofdstuk 9 Samenvatting',
            'price' => 3.75,
            'subject' => 'Wiskunde A',
            'class' => '4vwo',
            'method' => 'Getal en Ruimte',
            'fileKey' => 'FILEKEY',
            'state' => 'accepted',
        ]);
    }
}
