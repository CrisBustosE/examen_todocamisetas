<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seedeo de tallas cómunes de la industria: 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'Única'.
        $this->call([
            SizeSeeder::class,
            ClientSeeder::class, // Sedeo de los 2 principales clientes del caso
            ShirtSeeder::class, // Sedeo de algunas camisetas
        ]);
    }
}
