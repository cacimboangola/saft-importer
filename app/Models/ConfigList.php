<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigList extends Model
{
    use HasFactory;
    public $table = "config_lists";

    protected $fillable = [
        "CompanyID",
        "localidades",
        "paises",
        "provincias",
        "projectos",
        "veiculos",
        "veiculos_marcas",
        "veiculos_modelos",
        "veiculos_tipos",
        "mesas",
        "utilizadores",
        "utilizadores_perfis",
        "modos_pagamento",
        "unidades_medida"

    ];

    protected $casts = [
        "localidades"=> 'array',
        "paises"=> 'array',
        "provincias"=> 'array',
        "projectos"=> 'array',
        "veiculos"=> 'array',
        "veiculos_marcas"=> 'array',
        "veiculos_modelos"=> 'array',
        "veiculos_tipos"=> 'array',
        "mesas"=> 'array',
        "utilizadores"=> 'array',
        "utilizadores_perfis"=> 'array',
        "modos_pagamento"=> 'array',
        "unidades_medida"=> 'array'
    ];

    public function toArray()
    {
        $data = parent::toArray();

        // Lista de campos que podem estar aninhados e precisam ser corrigidos
        $campos = [
            "localidades",
            "paises",
            "provincias",
            "projectos",
            "veiculos",
            "veiculos_marcas",
            "veiculos_modelos",
            "veiculos_tipos",
            "mesas",
            "utilizadores",
            "utilizadores_perfis",
            "modos_pagamento",
            "unidades_medida"
        ];

        foreach ($campos as $campo) {
            if (isset($data[$campo][$campo])) {
                $data[$campo] = $data[$campo][$campo];
            }
        }

        return $data;
    }
}
