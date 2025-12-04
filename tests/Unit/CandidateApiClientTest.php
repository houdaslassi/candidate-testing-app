<?php

namespace Tests\Unit;

use App\Services\CandidateApiClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CandidateApiClientTest extends TestCase
{
    protected CandidateApiClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiClient = new CandidateApiClient();
    }

    public function test_login_returns_token_on_success(): void
    {
        Http::fake([
            '*/api/v2/token' => Http::response([
                'token_key' => 'test-token-123',
                'refresh_token_key' => 'refresh-token-123',
                'user' => [
                    'id' => 1,
                    'email' => 'test@example.com',
                    'first_name' => 'Test',
                    'last_name' => 'User',
                ],
            ], 200),
        ]);

        $response = $this->apiClient->login('test@example.com', 'password');

        $this->assertArrayHasKey('token_key', $response);
        $this->assertEquals('test-token-123', $response['token_key']);
        $this->assertArrayHasKey('user', $response);
    }

    public function test_login_throws_exception_on_failure(): void
    {
        Http::fake([
            '*/api/v2/token' => Http::response(['error' => 'Invalid credentials'], 401),
        ]);

        $this->expectException(\Exception::class);
        $this->apiClient->login('test@example.com', 'wrong-password');
    }

    public function test_get_authors_returns_paginated_results(): void
    {
        Session::put('api_token', 'test-token');

        Http::fake([
            '*/api/v2/authors*' => Http::response([
                'total_results' => 5,
                'total_pages' => 1,
                'current_page' => 1,
                'limit' => 12,
                'items' => [
                    ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                    ['id' => 2, 'first_name' => 'Jane', 'last_name' => 'Smith'],
                ],
            ], 200),
        ]);

        $response = $this->apiClient->getAuthors();

        $this->assertArrayHasKey('items', $response);
        $this->assertArrayHasKey('total_results', $response);
        $this->assertCount(2, $response['items']);
    }

    public function test_get_author_returns_author_with_books(): void
    {
        Session::put('api_token', 'test-token');

        Http::fake([
            '*/api/v2/authors/1' => Http::response([
                'id' => 1,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'books' => [
                    ['id' => 1, 'title' => 'Book One'],
                    ['id' => 2, 'title' => 'Book Two'],
                ],
            ], 200),
        ]);

        $response = $this->apiClient->getAuthor(1);

        $this->assertEquals(1, $response['id']);
        $this->assertEquals('John', $response['first_name']);
        $this->assertArrayHasKey('books', $response);
        $this->assertCount(2, $response['books']);
    }

    public function test_create_book_sends_correct_data(): void
    {
        Session::put('api_token', 'test-token');

        Http::fake([
            '*/api/v2/books' => Http::response([
                'id' => 1,
                'title' => 'New Book',
            ], 201),
        ]);

        $bookData = [
            'author' => ['id' => 1],
            'title' => 'New Book',
            'release_date' => '2024-01-01',
            'isbn' => '123456',
            'format' => 'Hardcover',
            'number_of_pages' => 200,
        ];

        $response = $this->apiClient->createBook($bookData);

        $this->assertEquals('New Book', $response['title']);
        
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v2/books') &&
                   $request['title'] === 'New Book';
        });
    }

    public function test_delete_book_makes_delete_request(): void
    {
        Session::put('api_token', 'test-token');

        Http::fake([
            '*/api/v2/books/1' => Http::response([], 204),
        ]);

        $this->apiClient->deleteBook(1);

        Http::assertSent(function ($request) {
            return $request->method() === 'DELETE' &&
                   str_contains($request->url(), '/api/v2/books/1');
        });
    }

    public function test_delete_author_makes_delete_request(): void
    {
        Session::put('api_token', 'test-token');

        Http::fake([
            '*/api/v2/authors/1' => Http::response([], 204),
        ]);

        $this->apiClient->deleteAuthor(1);

        Http::assertSent(function ($request) {
            return $request->method() === 'DELETE' &&
                   str_contains($request->url(), '/api/v2/authors/1');
        });
    }
}

