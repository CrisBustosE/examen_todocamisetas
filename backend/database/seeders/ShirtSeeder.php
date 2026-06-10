<?php

namespace Database\Seeders;

use App\Models\Shirt;
use Illuminate\Database\Seeder;

class ShirtSeeder extends Seeder
{
    public function run(): void
    {
        $shirts = [
            [
                // Camiseta 1: Para cliente Preferencial (id: 1) CON oferta
                'cliente_id'      => 1,
                'titulo'          => 'Camiseta Titular Colo-Colo 2026',
                'club'            => 'Colo-Colo',
                'pais'            => 'Chile',
                'tipo'            => 'Local',
                'color'           => 'Blanco y Negro',
                'precio'          => 49990,
                'precio_oferta'   => 39990,
                'detalles'        => 'Edición campeonato nacional',
                'codigo_producto' => 'CC-LOC-2026',
                'sizes'           => [2, 3, 4, 5], // S, M, L, XL
            ],
            [
                // Camiseta 2: Para cliente Regular (id: 2) SIN oferta
                'cliente_id'      => 2,
                'titulo'          => 'Camiseta Visita Universidad de Chile 2026',
                'club'            => 'Universidad de Chile',
                'pais'            => 'Chile',
                'tipo'            => 'Visita',
                'color'           => 'Blanco',
                'precio'          => 45000,
                'precio_oferta'   => null,
                'detalles'        => 'Tela respirable DryFit',
                'codigo_producto' => 'UCH-VIS-2026',
                'sizes'           => [3, 4, 5], // M, L, XL
            ],
            [
                // Camiseta 3: Para cliente Preferencial (id: 1) CON oferta jugosa
                'cliente_id'      => 1,
                'titulo'          => 'Camiseta Titular Real Madrid 2026',
                'club'            => 'Real Madrid',
                'pais'            => 'España',
                'tipo'            => 'Local',
                'color'           => 'Blanco y Dorado',
                'precio'          => 85000,
                'precio_oferta'   => 70000,
                'detalles'        => 'Versión auténtica jugador',
                'codigo_producto' => 'RM-LOC-2026',
                'sizes'           => [1, 2, 3, 4, 5, 6], // XS a XXL
            ]
        ];

        foreach ($shirts as $data) {
            // Extraemos las tallas antes de crear la camiseta para no romper Eloquent
            $sizes = $data['sizes'];
            unset($data['sizes']);

            $shirt = Shirt::create($data);
            $shirt->sizes()->attach($sizes);
        }
    }
}
