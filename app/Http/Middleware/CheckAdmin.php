<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    // Otros métodos y propiedades del modelo User

    /**
     * Determine if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin'; // O el campo correspondiente en tu tabla de usuarios
    }
}
