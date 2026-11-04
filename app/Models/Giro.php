<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Giro extends Model
{
    protected $table = 'giros';

    protected $fillable = ['user_id', 'reto_id', 'fecha'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reto()
    {
        return $this->belongsTo(Reto::class);
    }
}
