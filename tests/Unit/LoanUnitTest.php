<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_becomes_unavailable_when_no_copies()
    {
        $book = Book::factory()->create([
            'available_copies' => 1,
            'total_copies' => 5,
            'is_available' => true
        ]);

        Loan::create([
            'requester_name' => 'John Doe',
            'book_id' => $book->id
        ]);

        $newAvailableCopies = $book->available_copies - 1;
        $book->update([
            'available_copies' => $newAvailableCopies,
            'is_available' => $newAvailableCopies > 0
        ]);

        $book->refresh();

        $this->assertEquals(0, $book->available_copies);
        $this->assertFalse($book->is_available);
    }

    public function test_book_becomes_available_after_return()
    {
        $book = Book::factory()->create([
            'available_copies' => 0,
            'total_copies' => 5,
            'is_available' => false
        ]);

        $loan = Loan::create([
            'requester_name' => 'John Doe',
            'book_id' => $book->id,
            'return_at' => null
        ]);

        $loan->update(['return_at' => now()]);

        $book->update([
            'available_copies' => $book->available_copies + 1,
            'is_available' => true
        ]);

        $book->refresh();

        $this->assertEquals(1, $book->available_copies);
        $this->assertTrue($book->is_available);
    }
}
