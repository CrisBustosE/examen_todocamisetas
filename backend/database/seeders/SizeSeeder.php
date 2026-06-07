<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    public function run(): void
    {
        $tallas = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Única'];

        // firstOrCreate lo hace idempotente para evitar explosiones si corre multiples veces
        foreach ($tallas as $talla) {
            Size::firstOrCreate(['nombre' => $talla]);
        }
    }
}
