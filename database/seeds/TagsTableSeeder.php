<?php

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Tag::create(['name' => 'Vega', 'color' => 'green']);
        Tag::create(['name' => 'Gevogelte', 'color' => 'blue']);
        Tag::create(['name' => 'Rund', 'color' => 'grey']);
        Tag::create(['name' => 'Varken', 'color' => 'pink']);
        Tag::create(['name' => 'Vis', 'color' => 'brown']);
        Tag::create(['name' => 'Halal', 'color' => 'brown']);
    }
}
