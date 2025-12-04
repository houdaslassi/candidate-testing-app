<?php

namespace App\Http\Controllers;

use App\Services\CandidateApiClient;
use Illuminate\Http\Request;

class BookController extends Controller
{
    protected CandidateApiClient $api;

    public function __construct(CandidateApiClient $api)
    {
        $this->api = $api;
    }

    /**
     * Show form to create a new book
     */
    public function create(Request $request)
    {
        // Get all authors for dropdown
        $authorsResponse = $this->api->getAuthors(1, 100);
        $authors = $authorsResponse['items'] ?? [];
        
        // Pre-select author if passed in URL
        $selectedAuthorId = $request->get('author_id');

        return view('books.create', [
            'authors' => $authors,
            'selectedAuthorId' => $selectedAuthorId,
        ]);
    }

    /**
     * Store a new book
     */
    public function store(Request $request)
    {
        $request->validate([
            'author_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'release_date' => 'required|date',
            'description' => 'nullable|string',
            'isbn' => 'required|string|max:50',
            'format' => 'required|string|max:50',
            'number_of_pages' => 'required|integer|min:1',
        ]);

        try {
            $this->api->createBook([
                'author' => ['id' => (int) $request->author_id],
                'title' => $request->title,
                'release_date' => $request->release_date,
                'description' => $request->description ?? '',
                'isbn' => $request->isbn,
                'format' => $request->format,
                'number_of_pages' => (int) $request->number_of_pages,
            ]);

            return redirect()->route('authors.show', $request->author_id)
                ->with('success', 'Book created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create book. Please try again.');
        }
    }

    /**
     * Delete a book
     */
    public function destroy(int $id, Request $request)
    {
        try {
            $this->api->deleteBook($id);
            
            return back()->with('success', 'Book deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete book.');
        }
    }
}

