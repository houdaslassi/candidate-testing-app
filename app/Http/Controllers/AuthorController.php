<?php

namespace App\Http\Controllers;

use App\Services\CandidateApiClient;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    protected CandidateApiClient $api;

    public function __construct(CandidateApiClient $api)
    {
        $this->api = $api;
    }

    /**
     * Display a listing of authors
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        
        $response = $this->api->getAuthors($page);
        
        return view('authors.index', [
            'authors' => $response['items'] ?? [],
            'pagination' => [
                'total' => $response['total_results'] ?? 0,
                'pages' => $response['total_pages'] ?? 1,
                'current' => $response['current_page'] ?? 1,
                'limit' => $response['limit'] ?? 12,
            ],
        ]);
    }

    /**
     * Display a single author with their books
     */
    public function show(int $id)
    {
        $author = $this->api->getAuthor($id);
        
        return view('authors.show', [
            'author' => $author,
        ]);
    }

    /**
     * Delete an author
     */
    public function destroy(int $id)
    {
        try {
            $author = $this->api->getAuthor($id);
            
            // Check if author has books
            if (!empty($author['books'])) {
                return back()->with('error', 'Cannot delete author with associated books. Delete the books first.');
            }
            
            $this->api->deleteAuthor($id);
            
            return redirect()->route('authors.index')
                ->with('success', 'Author deleted successfully.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete author.');
        }
    }
}

