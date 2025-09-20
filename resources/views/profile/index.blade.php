@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Profile Information</h2>
            <p class="mt-1 text-sm text-gray-500">Update your account's profile information and email address.</p>
        </div>

        <!-- Profile Update Form -->
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <!-- Profile Photo -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700">Profile Photo</label>
                <div class="mt-2 flex items-center">
                    <div class="h-20 w-20 rounded-full overflow-hidden bg-gray-100">
                        @if($user->avatar)
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full flex items-center justify-center bg-pink-100">
                                <span class="text-2xl font-medium text-pink-600">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="ml-4">
                        <label class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 cursor-pointer">
                            <span>Change</span>
                            <input type="file" name="avatar" class="sr-only" onchange="this.form.submit()">
                        </label>
                        @if($user->avatar)
                            <button type="button" 
                                    onclick="if(confirm('Are you sure you want to remove your profile photo?')) { document.getElementById('remove-avatar').submit(); }"
                                    class="ml-3 text-sm text-red-600 hover:text-red-700">
                                Remove
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <div class="mt-1">
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm" 
                           required autofocus autocomplete="name">
                </div>
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <div class="mt-1">
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm" 
                           required autocomplete="email">
                </div>
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-pink-600 text-white rounded-md hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                    Save Changes
                </button>
            </div>
        </form>

        <!-- Remove Avatar Form -->
        @if($user->avatar)
            <form id="remove-avatar" action="{{ route('profile.update') }}" method="POST" class="hidden">
                @csrf
                @method('PUT')
                <input type="hidden" name="remove_avatar" value="1">
            </form>
        @endif

        <!-- Password Update Section -->
        <div class="border-t border-gray-200 px-6 py-5">
            <h2 class="text-xl font-semibold text-gray-900">Update Password</h2>
            <p class="mt-1 text-sm text-gray-500">Ensure your account is using a long, random password to stay secure.</p>

            <form method="POST" action="{{ route('profile.password') }}" class="mt-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                    <div class="mt-1">
                        <input type="password" name="current_password" id="current_password" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm" 
                               required autocomplete="current-password">
                    </div>
                    @error('current_password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <div class="mt-1">
                        <input type="password" name="password" id="password" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm" 
                               required autocomplete="new-password">
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <div class="mt-1">
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm" 
                               required autocomplete="new-password">
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="px-4 py-2 bg-pink-600 text-white rounded-md hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Show file name when a file is selected
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        if (fileName) {
            const label = this.previousElementSibling;
            label.textContent = 'Change: ' + fileName;
        }
    });
</script>
@endpush
@endsection
