<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    // Solo el Bibliotecario puede crear, actualizar o eliminar
    public function create(User $user): bool
    {
        return $user->hasRole('Bibliotecario');
    }

    public function update(User $user, Book $book): bool
    {
        return $user->hasRole('Bibliotecario');
    }

    public function delete(User $user, Book $book): bool
    {
        return $user->hasRole('Bibliotecario');
    }

    //Solo Estudiantes y Docentes prestan
    public function borrow(User $user): bool
    {
        return $user->hasAnyRole(['Estudiante', 'Docente']);
    }

    // Listar y ver detalles lo permitimos a todos los autenticados
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Book $book): bool
    {
        return true;
    }
}