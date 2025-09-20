<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user already exists
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if ($user) {
                // Update existing user with Google data if not already set
                if (!$user->provider) {
                    $user->update([
                        'provider' => 'google',
                        'provider_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                    ]);
                }
            } else {
                // Create new user with Google profile data
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)), // Generate a random password for OAuth users
                    'email_verified_at' => now(), // Mark email as verified
                    'role' => UserRole::BUYER, // Default role for new users
                ]);
            }
            
            // Log the user in
            Auth::login($user, true);
            
            // Redirect based on user role
            if ($user->isAdmin()) {
                return redirect()->intended('/admin/dashboard');
            }
            
            return redirect()->intended('/');
            
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            return redirect('/login')->withErrors([
                'google' => 'Session expired. Please try again.'
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return redirect('/login')->withErrors([
                'google' => 'Authentication error. Please check your Google OAuth credentials.'
            ]);
        } catch (\Exception $e) {
            // Log the full error for debugging
            Log::error('Google OAuth Error: ' . $e->getMessage());
            
            return redirect('/login')->withErrors([
                'google' => 'Unable to login with Google. Error: ' . $e->getMessage()
            ]);
        }
    }
}