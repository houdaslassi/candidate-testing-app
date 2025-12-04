<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class AuthorControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Session::put('api_token', 'test-token');
    }

    public function test_authors_index_requires_authentication(): void
    {
        Session::forget('api_token');

        $response = $this->get('/authors');

        $response->assertRedirect(route('login'));
    }

    public function test_authors_index_displays_authors_list(): void
    {
        Http::fake([
            '*/api/v2/authors*' => Http::response([
                'total_results' => 2,
                'total_pages' => 1,
                'current_page' => 1,
                'limit' => 12,
                'items' => [
                    [
                        'id' => 1,
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'birthday' => '1990-01-01T00:00:00+00:00',
                        'gender' => 'male',
                        'place_of_birth' => 'New York',
                    ],
                    [
                        'id' => 2,
                        'first_name' => 'Jane',
                        'last_name' => 'Smith',
                        'birthday' => '1985-05-15T00:00:00+00:00',
                        'gender' => 'female',
                        'place_of_birth' => 'London',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->get('/authors');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('Jane Smith');
    }

    public function test_author_show_displays_author_with_books(): void
    {
        Http::fake([
            '*/api/v2/authors/1' => Http::response([
                'id' => 1,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'birthday' => '1990-01-01T00:00:00+00:00',
                'gender' => 'male',
                'place_of_birth' => 'New York',
                'biography' => 'A great author',
                'books' => [
                    ['id' => 1, 'title' => 'Book One', 'isbn' => '123', 'release_date' => '2020-01-01T00:00:00+00:00', 'number_of_pages' => 200],
                ],
            ], 200),
        ]);

        $response = $this->get('/authors/1');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('Book One');
        $response->assertSee('A great author');
    }

    public function test_delete_author_with_books_shows_error(): void
    {
        Http::fake([
            '*/api/v2/authors/1' => Http::response([
                'id' => 1,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'books' => [
                    ['id' => 1, 'title' => 'Book One'],
                ],
            ], 200),
        ]);

        $response = $this->delete('/authors/1');

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot delete author with associated books. Delete the books first.');
    }

    public function test_delete_author_without_books_succeeds(): void
    {
        Http::fake([
            '*/api/v2/authors/1' => Http::sequence()
                ->push([
                    'id' => 1,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'books' => [],
                ], 200)
                ->push([], 204),
        ]);

        $response = $this->delete('/authors/1');

        $response->assertRedirect(route('authors.index'));
        $response->assertSessionHas('success', 'Author deleted successfully.');
    }
}

