<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $fillable = ['user_id', 'reto_id', 'tipo', 'puntos', 'errores'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reto()
    {
        return $this->belongsTo(Reto::class);
    }
}