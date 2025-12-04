<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CandidateApiClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.candidate_api.base_url', 'https://candidate-testing.com');
    }

    /**
     * Get the stored access token from session
     */
    protected function getToken(): ?string
    {
        return Session::get('api_token');
    }

    /**
     * Get the stored refresh token from session
     */
    protected function getRefreshToken(): ?string
    {
        return Session::get('refresh_token');
    }

    /**
     * Build the full API URL
     */
    protected function url(string $endpoint): string
    {
        return rtrim($this->baseUrl, '/') . '/api/v2/' . ltrim($endpoint, '/');
    }

    /**
     * Make an authenticated API request
     */
    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $response = $this->sendRequest($method, $endpoint, $data);

        // If 401, try refreshing token and retry once
        if ($response->status() === 401 && $this->getRefreshToken()) {
            if ($this->refreshToken()) {
                $response = $this->sendRequest($method, $endpoint, $data);
            }
        }

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        Log::error('API Request Failed', [
            'endpoint' => $endpoint,
            'status' => $response->status(),
        ]);

        throw new \Exception("API error: {$response->status()}");
    }

    /**
     * Send the HTTP request
     */
    protected function sendRequest(string $method, string $endpoint, array $data = []): Response
    {
        $http = Http::acceptJson()->withToken($this->getToken());

        return match (strtoupper($method)) {
            'GET' => $http->get($this->url($endpoint), $data),
            'POST' => $http->post($this->url($endpoint), $data),
            'DELETE' => $http->delete($this->url($endpoint)),
            default => throw new \InvalidArgumentException("Invalid method: {$method}"),
        };
    }

    /**
     * Refresh the access token
     */
    protected function refreshToken(): bool
    {
        try {
            $response = Http::acceptJson()->post($this->url('token/refresh'), [
                'refresh_token' => $this->getRefreshToken(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Session::put('api_token', $data['token_key']);
                Session::put('refresh_token', $data['refresh_token_key']);
                Session::put('token_expires_at', $data['expires_at']);
                
                if (isset($data['user'])) {
                    Session::put('user', $data['user']);
                }

                return true;
            }
        } catch (\Exception $e) {
            Log::error('Token refresh failed', ['error' => $e->getMessage()]);
        }

        // Clear session on refresh failure
        Session::forget(['api_token', 'refresh_token', 'token_expires_at', 'user']);
        
        return false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public API Methods
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Login and get access token
     */
    public function login(string $email, string $password): array
    {
        $response = Http::acceptJson()->post($this->url('token'), [
            'email' => $email,
            'password' => $password,
        ]);

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        throw new \Exception('Login failed: ' . $response->status());
    }

    /**
     * Get all authors with pagination
     */
    public function getAuthors(int $page = 1, int $limit = 12, string $orderBy = 'id', string $direction = 'ASC'): array
    {
        return $this->request('GET', 'authors', [
            'page' => $page,
            'limit' => $limit,
            'orderBy' => $orderBy,
            'direction' => $direction,
        ]);
    }

    /**
     * Get single author by ID (includes books)
     */
    public function getAuthor(int $id): array
    {
        return $this->request('GET', "authors/{$id}");
    }

    /**
     * Delete an author
     */
    public function deleteAuthor(int $id): array
    {
        return $this->request('DELETE', "authors/{$id}");
    }

    /**
     * Create a new author
     */
    public function createAuthor(array $data): array
    {
        return $this->request('POST', 'authors', $data);
    }

    /**
     * Get all books with pagination
     */
    public function getBooks(int $page = 1, int $limit = 12, string $orderBy = 'id', string $direction = 'ASC'): array
    {
        return $this->request('GET', 'books', [
            'page' => $page,
            'limit' => $limit,
            'orderBy' => $orderBy,
            'direction' => $direction,
        ]);
    }

    /**
     * Create a new book
     */
    public function createBook(array $data): array
    {
        return $this->request('POST', 'books', $data);
    }

    /**
     * Delete a book
     */
    public function deleteBook(int $id): array
    {
        return $this->request('DELETE', "books/{$id}");
    }
}
