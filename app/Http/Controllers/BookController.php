<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Book::class);
        
        $books = Book::when($request->has('title'), function ($query) use ($request) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        })->when($request->has('isbn'), function ($query) use ($request) {
            $query->where('ISBN', 'like', '%' . $request->input('isbn') . '%');
        })->when($request->has('is_available'), function ($query) use ($request) {
            $query->where('is_available', $request->boolean('is_available'));
        })->paginate();

        return response()->json(BookResource::collection($books));
    }

    public function show(Book $book)
    {
        $this->authorize('view', $book);
        return response()->json(BookResource::make($book));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Book::class);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ISBN' => 'required|string|unique:books,ISBN',
            'total_copies' => 'required|integer|min:1',
        ]);

        $book = Book::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'ISBN' => $validated['ISBN'],
            'total_copies' => $validated['total_copies'],
            'available_copies' => $validated['total_copies'],
            'is_available' => true,
        ]);

        return response()->json(BookResource::make($book), 201);
    }

    public function update(Request $request, Book $book)
    {
        $this->authorize('update', $book);
        
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'ISBN' => 'sometimes|string|unique:books,ISBN,' . $book->id,
            'total_copies' => 'sometimes|integer|min:1',
        ]);

        $book->update($validated);

        return response()->json(BookResource::make($book));
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);
        $book->delete();

        return response()->json(['message' => 'Book deleted successfully'], 204);
    }
}