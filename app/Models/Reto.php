<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reto extends Model
{
    protected $fillable = ['titulo', 'microleccion', 'tipo_actividad', 'actividad', 'consejo', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
        'actividad' => 'array',
    ];

    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }

    public function getNombreActividadAttribute(): string
    {
        return match ($this->tipo_actividad) {
            'escenario' => '¿Qué haces?',
            'completar' => 'Completa la frase',
            'ordenar' => 'Ordena los pasos',
            'mito' => 'Mito o realidad',
            'clasificar' => 'Clasifica',
            'emparejar' => 'Une las parejas',
            default => 'Actividad',
        };
    }
}
