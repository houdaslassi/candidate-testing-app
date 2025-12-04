<?php

namespace App\Http\Controllers;

use App\Services\CandidateApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    protected CandidateApiClient $apiClient;

    public function __construct(CandidateApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Show the login form
     */
    public function showLogin()
    {
        // If already logged in, redirect to authors page
        if (Session::has('api_token')) {
            return redirect()->route('authors.index');
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $response = $this->apiClient->login(
                $request->email,
                $request->password
            );

            if (isset($response['token_key'])) {
                // Store access token in session
                Session::put('api_token', $response['token_key']);
                
                // Store refresh token for token refresh functionality
                if (isset($response['refresh_token_key'])) {
                    Session::put('refresh_token', $response['refresh_token_key']);
                }
                
                // Store token expiry time
                if (isset($response['expires_at'])) {
                    Session::put('token_expires_at', $response['expires_at']);
                }
                
                // Store user info if available
                if (isset($response['user'])) {
                    Session::put('user', $response['user']);
                }

                return redirect()->route('authors.index')
                    ->with('success', 'Login successful!');
            }

            return back()->withErrors([
                'email' => 'Invalid credentials. Please try again.',
            ])->withInput($request->only('email'));

        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Login failed. Please check your credentials.',
            ])->withInput($request->only('email'));
        }
    }

    /**
     * Handle logout request
     */
    public function logout()
    {
        // Clear all authentication related session data
        Session::forget(['api_token', 'refresh_token', 'token_expires_at', 'user']);

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}

