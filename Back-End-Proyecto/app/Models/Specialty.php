<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relación uno a muchos: una especialidad puede tener muchos usuarios (médicos)
    public function users()
    {
        return $this->hasMany(User::class);
    }

}
