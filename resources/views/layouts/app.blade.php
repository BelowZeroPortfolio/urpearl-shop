<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'UrPearl SHOP') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js for interactive components -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-soft sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="text-2xl font-bold text-pink-500">
                            UrPearl SHOP
                        </a>
                    </div>
                    
                    <div class="hidden md:flex items-center space-x-8">
                        <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative group">
                            <div class="flex items-center">
                                <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-pink-500 transition-colors font-medium">
                                    Shop
                                </a>
                                <button @click="open = !open" type="button" class="ml-1 text-gray-600 hover:text-pink-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-50">
                                <a href="{{ route('products.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">All Products</a>
                                <a href="{{ route('products.index', ['category' => 'pearls']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pearls</a>
                                <a href="{{ route('products.index', ['category' => 'jewelry']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Jewelry</a>
                                <a href="{{ route('products.index', ['category' => 'accessories']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Accessories</a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="{{ route('products.new-arrivals') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">New Arrivals</a>
                                <a href="{{ route('products.best-sellers') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Best Sellers</a>
                            </div>
                        </div>
                        <a href="{{ route('products.new-arrivals') }}" class="flex items-center text-gray-600 hover:text-pink-500 transition-colors font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            New Arrivals
                        </a>
                        <a href="{{ route('products.best-sellers') }}" class="flex items-center text-gray-600 hover:text-pink-500 transition-colors font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                            Best Sellers
                        </a>
                        <a href="{{ route('about') }}" class="text-gray-600 hover:text-pink-500 transition-colors font-medium">
                            About Us
                        </a>
                        <a href="{{ route('contact') }}" class="text-gray-600 hover:text-pink-500 transition-colors font-medium">
                            Contact
                        </a>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        @guest
                            <div class="hidden md:flex items-center space-x-4">
                                <a href="{{ route('login') }}" class="text-gray-600 hover:text-pink-500 transition-colors font-medium">
                                    Sign in
                                </a>
                                <a href="{{ route('register') }}" class="px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors font-medium">
                                    Sign up
                                </a>
                            </div>
                        @else
                            <!-- Cart Icon -->
                            <a href="{{ route('cart.index') }}" class="relative p-2 text-gray-600 hover:text-pink-500 transition-colors rounded-full hover:bg-gray-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                @php
                                    $cartCount = 0;
                                    if (auth()->check()) {
                                        $cartCount = \App\Models\CartItem::where('user_id', auth()->id())->sum('quantity');
                                    }
                                @endphp
                                @if($cartCount > 0)
                                    <span class="absolute -top-1 -right-1 bg-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center transform hover:scale-110 transition-transform">
                                        {{ $cartCount }}
                                    </span>
                                @endif
                            </a>

@if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="hidden md:block text-gray-600 hover:text-pink-500 transition-colors">
                                    Admin Dashboard
                                </a>
                            @endif
                            <!-- User Menu with Alpine.js -->
                            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                                <button @click="open = !open" type="button" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                                    @if(auth()->user()->avatar)
                                        <img class="h-8 w-8 rounded-full" src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-pink-500 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <svg :class="{'transform rotate-180': open}" class="ml-1 h-4 w-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-opacity-5 py-1 z-50">
                                    <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                                    <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Orders</a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Wishlist</a>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Sign out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endauth
                        
                        <!-- Mobile menu button -->
                        <button type="button" class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition-colors" onclick="toggleMobileMenu()">
                            <svg id="mobile-menu-icon" class="h-6 w-6 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg id="mobile-close-icon" class="h-6 w-6 hidden transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Mobile menu -->
                <div id="mobile-menu" class="hidden md:hidden border-t border-gray-200 bg-white">
                    <div class="px-2 pt-4 pb-6 space-y-2">
                        <a href="{{ route('products.index') }}" class="flex items-center px-4 py-3 text-gray-600 hover:text-pink-500 hover:bg-pink-50 rounded-lg transition-all font-medium">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            All Products
                        </a>
                        <a href="{{ route('products.new-arrivals') }}" class="flex items-center px-4 py-3 text-gray-600 hover:text-pink-500 hover:bg-pink-50 rounded-lg transition-all font-medium">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            New Arrivals
                        </a>
                        <a href="{{ route('products.best-sellers') }}" class="flex items-center px-4 py-3 text-gray-600 hover:text-pink-500 hover:bg-pink-50 rounded-lg transition-all font-medium">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                            Best Sellers
                        </a>
                        <a href="{{ route('about') }}" class="flex items-center px-4 py-3 text-gray-600 hover:text-pink-500 hover:bg-pink-50 rounded-lg transition-all font-medium">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            About Us
                        </a>
                        <a href="{{ route('contact') }}" class="flex items-center px-4 py-3 text-gray-600 hover:text-pink-500 hover:bg-pink-50 rounded-lg transition-all font-medium">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Contact
                        </a>
                        
                        @auth
                            <a href="{{ route('cart.index') }}" class="flex items-center px-4 py-3 text-gray-600 hover:text-pink-500 hover:bg-pink-50 rounded-lg transition-all font-medium">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9"></path>
                                </svg>
                                Shopping Cart
                                <span class="ml-auto bg-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    {{ $cartCount ?? 0 }}
                                </span>
                            </a>
                            
                            <a href="{{ route('orders.index') }}" class="flex items-center px-4 py-3 text-gray-600 hover:text-pink-500 hover:bg-pink-50 rounded-lg transition-all font-medium">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                My Orders
                            </a>
                            
                            @if(auth()->user()->isAdmin())
                                <div class="border-t border-gray-200 pt-4 mt-4">
                                    <p class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</p>
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-gray-600 hover:text-pink-500 hover:bg-pink-50 rounded-lg transition-all font-medium">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                                        </svg>
                                        Dashboard
                                    </a>
                                </div>
                            @endif
                            
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <div class="flex items-center px-4 py-3">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-pink-500 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('logout') }}" class="px-4">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-4 py-3 text-gray-600 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all font-medium">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <a href="{{ route('login') }}" class="flex items-center px-4 py-3 text-white bg-pink-500 hover:bg-pink-600 rounded-lg transition-all font-medium">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Sign In
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('mobile-menu-icon');
            const closeIcon = document.getElementById('mobile-close-icon');
            
            menu.classList.toggle('hidden');
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
            
            // Add slide animation
            if (!menu.classList.contains('hidden')) {
                menu.style.maxHeight = menu.scrollHeight + 'px';
                menu.style.opacity = '1';
            } else {
                menu.style.maxHeight = '0';
                menu.style.opacity = '0';
            }
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobile-menu');
            const menuButton = event.target.closest('button[onclick="toggleMobileMenu()"]');
            
            if (!menuButton && !menu.contains(event.target) && !menu.classList.contains('hidden')) {
                toggleMobileMenu();
            }
        });

        // Close mobile menu on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                const menu = document.getElementById('mobile-menu');
                const menuIcon = document.getElementById('mobile-menu-icon');
                const closeIcon = document.getElementById('mobile-close-icon');
                
                menu.classList.add('hidden');
                menuIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        });
        </script>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Brand Section -->
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center mb-4">
                            <span class="text-2xl font-bold text-pink-500">UrPearl SHOP</span>
                        </div>
                        <p class="text-gray-600 mb-6 max-w-md">
                            Discover our exquisite collection of pearls and jewelry, crafted with elegance and sophistication for the modern connoisseur.
                        </p>
                        <div class="flex space-x-4">
                            <!-- Social Media Links -->
                            <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001.012.001z"/>
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">Shop</h3>
                        <ul class="space-y-3">
                            <li><a href="{{ route('products.index') }}" class="text-gray-600 hover:text-pink-500 transition-colors">All Products</a></li>
                            <li><a href="{{ route('products.index', ['category' => 'pearls']) }}" class="text-gray-600 hover:text-pink-500 transition-colors">Pearls</a></li>
                            <li><a href="{{ route('products.index', ['category' => 'jewelry']) }}" class="text-gray-600 hover:text-pink-500 transition-colors">Jewelry</a></li>
                            <li><a href="{{ route('products.index', ['category' => 'accessories']) }}" class="text-gray-600 hover:text-pink-500 transition-colors">Accessories</a></li>
                        </ul>
                    </div>

                    <!-- Customer Service -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">Support</h3>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-gray-600 hover:text-pink-500 transition-colors">Contact Us</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-pink-500 transition-colors">Shipping Info</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-pink-500 transition-colors">Returns</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-pink-500 transition-colors">Size Guide</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-pink-500 transition-colors">Care Instructions</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom Footer -->
                <div class="border-t border-gray-200 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-500 text-sm">
                        © {{ date('Y') }} UrPearl SHOP. All rights reserved.
                    </p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-gray-500 hover:text-pink-500 text-sm transition-colors">Privacy Policy</a>
                        <a href="#" class="text-gray-500 hover:text-pink-500 text-sm transition-colors">Terms of Service</a>
                        <a href="#" class="text-gray-500 hover:text-pink-500 text-sm transition-colors">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Global Cart JavaScript -->
    <script>
    // Global function for adding to cart from product cards
    function addToCart(productId, quantity = 1) {
        @guest
            // Redirect to login if not authenticated
            window.location.href = '{{ route("login") }}';
            return;
        @endguest

        const button = event.target;
        const originalText = button.innerHTML;
        
        // Disable button and show loading state
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Adding...';
        
        fetch(`/cart/${productId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: parseInt(quantity) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count in navigation
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                }
                
                // Show success message
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while adding to cart', 'error');
        })
        .finally(() => {
            // Re-enable button and restore original text
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }

    function showMessage(message, type) {
        // Create a simple toast notification
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
    </script>

    <!-- Scroll reveal animations -->
    <script>
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
            }
        });
    }, observerOptions);

    // Observe all elements with scroll-reveal class
    document.addEventListener('DOMContentLoaded', function() {
        const scrollElements = document.querySelectorAll('.scroll-reveal');
        scrollElements.forEach(el => observer.observe(el));
        
        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    });

    // Enhanced toast notification system
    window.showToast = function(message, type = 'success', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast ${type} show`;
        
        const icon = {
            success: '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            error: '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            warning: '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            info: '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        };
        
        toast.innerHTML = `
            <div class="flex items-center">
                ${icon[type] || icon.info}
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after duration
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };

    // Update the existing showMessage function to use the new toast system
    window.showMessage = function(message, type) {
        showToast(message, type);
    };
    </script>

    <!-- Form Validation Script -->
    @vite(['resources/js/validation.js'])
    
    @stack('scripts')
</body>
</html>