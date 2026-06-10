<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nombre_comercial'  => 'Tienda 90minutos',
                'rut'               => '76111222-3',
                'direccion'         => 'Providencia, Santiago',
                'categoria'         => 'Preferencial',
                'contacto_nombre'   => 'Pedro Soto',
                'contacto_correo'   => 'pedro@90minutos.cl',
                'porcentaje_oferta' => 15,
            ],
            [
                'nombre_comercial'  => 'Tienda tdeportes',
                'rut'               => '77987654-3',
                'direccion'         => 'Las Condes, Santiago',
                'categoria'         => 'Regular',
                'contacto_nombre'   => 'Francisca Silva',
                'contacto_correo'   => 'contacto@tdeportes.cl',
                'porcentaje_oferta' => null,
            ]
        ];

        foreach ($clientes as $cliente) {
            Client::create($cliente);
        }
    }
}
