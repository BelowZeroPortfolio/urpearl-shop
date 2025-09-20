@props(['cartCount' => 0])

<nav class="bg-white shadow-soft sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-pink-500 hover:text-pink-600 transition-colors">
                    UrPearl SHOP
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-pink-500 transition-colors font-medium">
                    Products
                </a>
                
                @auth
                    <a href="{{ route('orders.index') }}" class="text-gray-600 hover:text-pink-500 transition-colors font-medium">
                        My Orders
                    </a>
                @endauth
            </div>
            
            <!-- Right side actions -->
            <div class="flex items-center space-x-4">
                @auth
                    <!-- Cart Icon -->
                    <a href="{{ route('cart.index') }}" class="relative text-gray-600 hover:text-pink-500 transition-colors p-2 rounded-lg hover:bg-pink-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                        @if($cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                    
                    <!-- User Menu -->
                    <div class="relative hidden md:block">
                        <button type="button" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2" onclick="toggleUserMenu()">
                            @if(auth()->user()->avatar)
                                <img class="h-8 w-8 rounded-full" src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
                            @else
                                <div class="h-8 w-8 rounded-full bg-pink-500 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <span class="ml-2 text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                            <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- User Dropdown -->
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="py-1">
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Admin Dashboard
                                    </a>
                                @endif
                                <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Profile
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-primary">
                        Sign In
                    </a>
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
                    Products
                </a>
                
                @auth
                    <a href="{{ route('cart.index') }}" class="flex items-center px-4 py-3 text-gray-600 hover:text-pink-500 hover:bg-pink-50 rounded-lg transition-all font-medium">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9"></path>
                        </svg>
                        Shopping Cart
                        @if($cartCount > 0)
                            <span class="ml-auto bg-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        @endif
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </div>
                    @endif
                    
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <div class="flex items-center px-4 py-3">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full mr-3">
                            @else
                                <div class="w-8 h-8 rounded-full bg-pink-500 flex items-center justify-center mr-3">
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
}

function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const userDropdown = document.getElementById('userDropdown');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (!event.target.closest('[onclick="toggleUserMenu()"]') && !userDropdown.contains(event.target)) {
        userDropdown.classList.add('hidden');
    }
    
    if (!event.target.closest('[onclick="toggleMobileMenu()"]') && !mobileMenu.contains(event.target)) {
        mobileMenu.classList.add('hidden');
        document.getElementById('mobile-menu-icon').classList.remove('hidden');
        document.getElementById('mobile-close-icon').classList.add('hidden');
    }
});
</script>