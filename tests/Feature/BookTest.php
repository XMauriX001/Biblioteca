<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class BookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'librarian']);
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'teacher']);
    }

    private function librarian()
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');
        return $user;
    }

    private function student()
    {
        $user = User::factory()->create();
        $user->assignRole('student');
        return $user;
    }

    private function teacher()
    {
        $user = User::factory()->create();
        $user->assignRole('teacher');
        return $user;
    }

    public function test_it_can_list_books()
    {
        $user = $this->student();

        Book::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(3, 'data');
    }

    public function test_it_can_filter_books_by_title()
    {
        $user = $this->student();

        Book::factory()->create(['title' => 'Laravel Avanzado']);
        Book::factory()->create(['title' => 'Clean Code']);
        Book::factory()->create(['title' => 'Laravel para APIs']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books?title=Laravel');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(2, 'data');
    }

    public function test_it_can_filter_books_by_isbn()
    {
        $user = $this->student();

        Book::factory()->create([
            'title' => 'Libro A',
            'ISBN' => '1111111111111'
        ]);

        Book::factory()->create([
            'title' => 'Libro B',
            'ISBN' => '2222222222222'
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books?isbn=2222222222222');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'ISBN' => '2222222222222'
            ]);
    }

    public function test_it_can_view_book_detail()
    {
        $user = $this->student();

        $book = Book::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $book->id
            ]);
    }

    public function test_librarian_can_create_book()
    {
        $user = $this->librarian();

        $payload = [
            'title' => 'Nuevo Libro',
            'description' => 'Descripción del libro',
            'ISBN' => '9789999999999',
            'total_copies' => 5,
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'title' => 'Nuevo Libro'
        ]);
    }

    public function test_student_cannot_create_book()
    {
        $user = $this->student();

        $payload = [
            'title' => 'Libro prohibido',
            'description' => 'Texto',
            'ISBN' => '9788888888888',
            'total_copies' => 3,
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', $payload);

        $response->assertForbidden();
    }

    public function test_librarian_can_update_book()
    {
        $user = $this->librarian();

        $book = Book::factory()->create([
            'title' => 'Original'
        ]);

        $payload = [
            'title' => 'Actualizado'
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('books', [
            'title' => 'Actualizado'
        ]);
    }

    public function test_student_cannot_update_book()
    {
        $user = $this->student();

        $book = Book::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", [
                'title' => 'Cambio ilegal'
            ]);

        $response->assertForbidden();
    }

    public function test_librarian_can_delete_book()
    {
        $user = $this->librarian();

        $book = Book::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id
        ]);
    }

    public function test_student_cannot_delete_book()
    {
        $user = $this->student();

        $book = Book::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/books/{$book->id}");

        $response->assertForbidden();
    }

    public function test_book_creation_requires_valid_data()
    {
        $user = $this->librarian();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', []);

        $response->assertStatus(422);
    }
}
