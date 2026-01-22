<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WineType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function wines()
    {
        return $this->hasMany(Wine::class, 'type_id');
    }

    public function grapes()
    {
        return $this->hasMany(Grape::class);
    }
}
