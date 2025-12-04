<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Session::put('api_token', 'test-token');
    }

    public function test_create_book_page_requires_authentication(): void
    {
        Session::forget('api_token');

        $response = $this->get('/books/create');

        $response->assertRedirect(route('login'));
    }

    public function test_create_book_page_displays_form_with_authors(): void
    {
        Http::fake([
            '*/api/v2/authors*' => Http::response([
                'total_results' => 2,
                'total_pages' => 1,
                'current_page' => 1,
                'limit' => 100,
                'items' => [
                    ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                    ['id' => 2, 'first_name' => 'Jane', 'last_name' => 'Smith'],
                ],
            ], 200),
        ]);

        $response = $this->get('/books/create');

        $response->assertStatus(200);
        $response->assertSee('Add New Book');
        $response->assertSee('John Doe');
        $response->assertSee('Jane Smith');
    }

    public function test_create_book_page_preselects_author_from_url(): void
    {
        Http::fake([
            '*/api/v2/authors*' => Http::response([
                'items' => [
                    ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                ],
            ], 200),
        ]);

        $response = $this->get('/books/create?author_id=1');

        $response->assertStatus(200);
    }

    public function test_store_book_validates_required_fields(): void
    {
        $response = $this->post('/books', []);

        $response->assertSessionHasErrors([
            'author_id',
            'title',
            'release_date',
            'isbn',
            'format',
            'number_of_pages',
        ]);
    }

    public function test_store_book_creates_book_and_redirects(): void
    {
        Http::fake([
            '*/api/v2/books' => Http::response([
                'id' => 1,
                'title' => 'New Book',
            ], 201),
        ]);

        $response = $this->post('/books', [
            'author_id' => 1,
            'title' => 'New Book',
            'release_date' => '2024-01-01',
            'isbn' => '123456',
            'format' => 'Hardcover',
            'number_of_pages' => 200,
            'description' => 'A great book',
        ]);

        $response->assertRedirect(route('authors.show', 1));
        $response->assertSessionHas('success', 'Book created successfully.');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v2/books') &&
                   $request['title'] === 'New Book' &&
                   $request['author']['id'] === 1;
        });
    }

    public function test_delete_book_removes_book_and_redirects(): void
    {
        Http::fake([
            '*/api/v2/books/1' => Http::response([], 204),
        ]);

        $response = $this->from('/authors/1')->delete('/books/1');

        $response->assertRedirect('/authors/1');
        $response->assertSessionHas('success', 'Book deleted successfully.');
    }

    public function test_delete_book_shows_error_on_failure(): void
    {
        Http::fake([
            '*/api/v2/books/1' => Http::response(['error' => 'Not found'], 404),
        ]);

        $response = $this->from('/authors/1')->delete('/books/1');

        $response->assertRedirect('/authors/1');
        $response->assertSessionHas('error', 'Failed to delete book.');
    }
}

