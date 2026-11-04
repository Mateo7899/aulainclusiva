<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insignia extends Model
{
    protected $fillable = ['clave', 'nombre', 'descripcion'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}