<?php

namespace Tests\Feature;

use App\Services\CandidateApiClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Login');
    }

    public function test_login_redirects_to_authors_on_success(): void
    {
        Http::fake([
            '*/api/v2/token' => Http::response([
                'token_key' => 'test-token-123',
                'refresh_token_key' => 'refresh-token-123',
                'expires_at' => '2026-01-01T00:00:00+00:00',
                'user' => [
                    'id' => 1,
                    'email' => 'test@example.com',
                    'first_name' => 'Test',
                    'last_name' => 'User',
                ],
            ], 200),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('authors.index'));
        $this->assertEquals('test-token-123', Session::get('api_token'));
    }

    public function test_login_shows_error_on_invalid_credentials(): void
    {
        Http::fake([
            '*/api/v2/token' => Http::response(['error' => 'Invalid credentials'], 401),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_logout_clears_session_and_redirects(): void
    {
        Session::put('api_token', 'test-token');
        Session::put('user', ['name' => 'Test']);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertNull(Session::get('api_token'));
        $this->assertNull(Session::get('user'));
    }

    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        Session::put('api_token', 'test-token');

        $response = $this->get('/login');

        $response->assertRedirect(route('authors.index'));
    }
}

