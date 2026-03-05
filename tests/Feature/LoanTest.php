<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_loan()
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'available_copies' => 5,
            'is_available' => true
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/loans', [
                'requester_name' => 'John Doe',
                'book_id' => $book->id,
            ]);

        $response->assertStatus(201);

        $this->assertEquals(4, $book->fresh()->available_copies);
    }

    public function test_it_cannot_loan_unavailable_book()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'available_copies' => 0,
            'is_available' => false
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/loans', [
                'requester_name' => 'John Doe',
                'book_id' => $book->id
            ]);

        $response->assertStatus(422)->assertJson([
            'message' => 'Book is not available'
        ]);
    }

    public function test_it_can_return_loan()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'available_copies' => 3,
            'total_copies' => 5
        ]);

        $loan = Loan::factory()->create([
            'requester_name' => 'John Doe',
            'book_id' => $book->id,
            'return_at' => null
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(200);

        $this->assertEquals(4, $book->fresh()->available_copies);
        $this->assertNotNull($loan->fresh()->return_at);
    }


    public function it_cannot_return_already_returned_loan()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::create([
            'requester_name' => 'John Doe',
            'book_id' => $book->id,
            'return_at' => now()
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Loan already returned'
            ]);
    }

    public function test_it_can_list_loans()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Loan::factory()->count(3)->create([
            'book_id' => $book->id
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/loans');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(3, count($data));
    }

    public function test_loan_creation_requires_valid_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/loans', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['requester_name', 'book_id']);
    }

    public function test_it_cannot_create_loan()
    {
        $book = Book::factory()->create();

        $response = $this->postJson('/api/v1/loans', [
            'requester_name' => 'John Doe',
            'book_id' => $book->id,
        ]);

        $response->assertStatus(401);
    }
}
