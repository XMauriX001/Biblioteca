<?php

namespace App\Policies;

use App\Models\User;

class LoanPolicy
{
    // Solo Estudiantes y Docentes pueden crear un préstamo 
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Estudiante', 'Docente']);
    }

    // El historial lo pueden ver todos los usuarios autenticados
    public function viewAny(User $user): bool
    {
        return true;
    }
}