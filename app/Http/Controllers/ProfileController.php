<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the user's profile.
     */
    public function index()
    {
        $user = auth()->user();
        $view = $user->is_admin ? 'profile.index' : 'profile.buyer';
        
        return view($view, [
            'user' => $user
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::delete('public/avatars/' . basename($user->avatar));
            }
            
            $path = $request->file('avatar')->store('public/avatars');
            $validated['avatar'] = Storage::url($path);
        }

        $user->update($validated);

        return redirect()->route('profile.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Profile updated successfully!'
            ]);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Password updated successfully!'
        ]);
    }
}
