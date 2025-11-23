<?php
//Definición de las tarifas y variables que competen en el cobro de las facturas.
return[
    'currency' => 'COP',
    'company_name' => 'Acueducto S.A',
    'concepts' => [
        'water' =>[
            'name' => 'Consumo Básico de Agua',
            'price_per_m3' => 2000.00,
        ],
        'sewarage' => [
            'name' => 'Servicio de Alcantarillado',
            'fixed_price' => 5000,00,
        ],
        'tax_rate' => 0.19,
    ],
    'due_days' => 15,
];
