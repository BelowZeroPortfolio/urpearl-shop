<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'UrPearl SHOP') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Left side -->
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center">
                                <span class="text-2xl font-bold text-pink-600">UrPearl</span>
                                <span class="ml-2 text-sm font-medium text-gray-600">ADMIN</span>
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden md:ml-10 md:flex md:space-x-8">
                            <a href="{{ route('admin.dashboard') }}" 
                               class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'text-pink-600 border-b-2 border-pink-600' : 'text-gray-500 hover:text-gray-700' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                                </svg>
                                Dashboard
                            </a>

                            <a href="{{ route('admin.products.index') }}" 
                               class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('admin.products.*') ? 'text-pink-600 border-b-2 border-pink-600' : 'text-gray-500 hover:text-gray-700' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Products
                            </a>

                            <a href="{{ route('admin.inventory.index') }}" 
                               class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('admin.inventory.*') ? 'text-pink-600 border-b-2 border-pink-600' : 'text-gray-500 hover:text-gray-700' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                Inventory
                            </a>

                            <a href="{{ route('admin.orders.index') }}" 
                               class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('admin.orders.*') ? 'text-pink-600 border-b-2 border-pink-600' : 'text-gray-500 hover:text-gray-700' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                Orders
                            </a>

                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <!-- Notifications -->
                        <div class="relative">
                            <button type="button" 
                                    class="p-2 text-gray-500 hover:text-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 rounded-full transition-colors duration-200 relative"
                                    onclick="toggleNotifications()"
                                    aria-label="Notifications">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                </svg>
                                <span class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center transform hover:scale-110 transition-transform" id="notificationCount" style="display: none;">
                                    0
                                </span>
                            </button>
                            
                            <!-- Notification Dropdown -->
                            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                                    <h3 class="text-sm font-medium text-gray-900">Notifications</h3>
                                    <a href="{{ route('admin.notifications.index') }}" class="text-xs text-pink-600 hover:text-pink-700 font-medium">
                                        View All
                                    </a>
                                </div>
                                <div class="max-h-80 overflow-y-auto">
                                    <div id="notificationList" class="text-center text-gray-500 p-4">
                                        Loading notifications...
                                    </div>
                                </div>
                                <div class="p-3 border-t border-gray-200 text-center">
                                    <button onclick="markAllNotificationsAsRead()" class="text-xs text-pink-600 hover:text-pink-700 font-medium">
                                        Mark All as Read
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="relative">
                            <button type="button" 
                                    class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
                                    onclick="toggleUserMenu()">
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
                                    <a href="{{ route('home') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        View Site
                                    </a>
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

                        <!-- Mobile menu button -->
                        <div class="md:hidden">
                            <button type="button" 
                                    class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    onclick="toggleMobileMenu()">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div id="mobileMenu" class="hidden md:hidden">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white border-t border-gray-200">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="block px-3 py-2 text-base font-medium {{ request()->routeIs('admin.dashboard') ? 'text-pink-600 bg-pink-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.products.index') }}" 
                       class="block px-3 py-2 text-base font-medium {{ request()->routeIs('admin.products.*') ? 'text-pink-600 bg-pink-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                        Products
                    </a>
                    <a href="{{ route('admin.inventory.index') }}" 
                       class="block px-3 py-2 text-base font-medium {{ request()->routeIs('admin.inventory.*') ? 'text-pink-600 bg-pink-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                        Inventory
                    </a>
                    <a href="{{ route('admin.orders.index') }}" 
                       class="block px-3 py-2 text-base font-medium {{ request()->routeIs('admin.orders.*') ? 'text-pink-600 bg-pink-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                        Orders
                    </a>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <!-- Toast Notification Container -->
        <div id="toast-container" class="fixed top-6 right-6 z-[9999] space-y-3 w-96">
            @if(session('toast') && !request()->has('from_redirect'))
                <div class="toast-notification" 
                     data-type="{{ session('toast')['type'] ?? 'info' }}"
                     data-duration="{{ session('toast')['duration'] ?? 3000 }}">
                    <div class="relative flex items-start p-4 rounded-xl shadow-2xl bg-white border-l-8 {{ 
                        session('toast')['type'] === 'success' ? 'border-emerald-500 bg-emerald-50' : 
                        (session('toast')['type'] === 'error' ? 'border-red-500 bg-red-50' : 'border-blue-500 bg-blue-50') 
                    }} overflow-hidden">
                        <div class="flex-shrink-0">
                            @if(session('toast')['type'] === 'success')
                                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            @elseif(session('toast')['type'] === 'error')
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="ml-3 flex-1">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ session('toast')['type'] === 'success' ? 'Success' : (session('toast')['type'] === 'error' ? 'Error' : 'Info') }}
                            </div>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ session('toast')['message'] }}
                            </p>
                        </div>
                        <button type="button" 
                                class="ml-4 -mt-2 -mr-2 p-1.5 text-gray-400 hover:text-gray-900 rounded-full hover:bg-gray-100 transition-colors duration-200" 
                                onclick="this.closest('.toast-notification').remove()">
                            <span class="sr-only">Close</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100 overflow-hidden">
                            <div class="h-full {{ 
                                session('toast')['type'] === 'success' ? 'bg-emerald-500' : 
                                (session('toast')['type'] === 'error' ? 'bg-red-500' : 'bg-blue-500') 
                            }} progress-bar"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <style>
            #toast-container {
                position: fixed;
                top: 1.5rem;
                right: 1.5rem;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                width: 24rem;
                max-width: 100%;
            }

            .toast-notification {
                position: relative;
                width: 100%;
                margin-bottom: 0.75rem;
                opacity: 0;
                transform: translateX(2rem);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                pointer-events: auto;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                border-radius: 0.75rem;
                overflow: hidden;
                background-color: white;
            }
            
            .toast-notification.show {
                opacity: 1;
                transform: translateX(0);
            }
            
            .toast-notification.hide {
                opacity: 0;
                transform: translateX(2rem);
                max-height: 0;
                margin: 0;
                padding: 0;
                overflow: hidden;
            }
            
            .progress-bar {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background-color: currentColor;
                animation: progress linear forwards;
            }
            
            @keyframes progress {
                from { width: 100%; }
                to { width: 0; }
            }
        </style>

        <main class="py-6">
            @if(session('success'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script>
        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('hidden');
            
            // Close user dropdown if open
            document.getElementById('userDropdown').classList.add('hidden');
        }

        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
            
            // Close notifications dropdown if open
            document.getElementById('notificationDropdown').classList.add('hidden');
        }

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const notificationDropdown = document.getElementById('notificationDropdown');
            const userDropdown = document.getElementById('userDropdown');
            const mobileMenu = document.getElementById('mobileMenu');
            
            if (!event.target.closest('.relative')) {
                notificationDropdown.classList.add('hidden');
                userDropdown.classList.add('hidden');
            }
            
            if (!event.target.closest('.md\\:hidden') && !event.target.closest('#mobileMenu')) {
                mobileMenu.classList.add('hidden');
            }
        });

        // Load notifications on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
        });

        function loadNotifications() {
            fetch('/admin/notifications/api')
                .then(response => response.json())
                .then(data => {
                    updateNotificationCount(data.unread_count);
                    updateNotificationList(data.notifications);
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    document.getElementById('notificationList').innerHTML = '<div class="p-4 text-center text-red-500">Failed to load notifications</div>';
                });
        }

        function updateNotificationCount(count) {
            const countElement = document.getElementById('notificationCount');
            countElement.textContent = count;
            countElement.style.display = count > 0 ? 'flex' : 'none';
        }

        function updateNotificationList(notifications) {
            const listElement = document.getElementById('notificationList');
            
            if (notifications.length === 0) {
                listElement.innerHTML = '<div class="p-4 text-center text-gray-500">No new notifications</div>';
                return;
            }

            const notificationsHtml = notifications.map(notification => {
                const iconHtml = getNotificationIcon(notification.type);
                const unreadClass = notification.is_read ? '' : 'bg-pink-50 border-l-2 border-l-pink-500';
                
                return `
                    <div class="p-3 border-b border-gray-100 hover:bg-gray-50 ${unreadClass}" data-notification-id="${notification.id}">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mr-3">
                                ${iconHtml}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 truncate">${notification.title}</p>
                                    ${!notification.is_read ? '<div class="w-2 h-2 bg-pink-500 rounded-full ml-2"></div>' : ''}
                                </div>
                                <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                                <div class="flex items-center justify-between mt-2">
                                    <p class="text-xs text-gray-500">${notification.created_at}</p>
                                    ${!notification.is_read ? `<button onclick="markNotificationAsRead(${notification.id})" class="text-xs text-pink-600 hover:text-pink-700 font-medium">Mark as Read</button>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            listElement.innerHTML = notificationsHtml;
        }

        function getNotificationIcon(type) {
            switch(type) {
                case 'low_stock':
                    return `<div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>`;
                case 'order_created':
                    return `<div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>`;
                default:
                    return `<div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>`;
            }
        }

        function markNotificationAsRead(notificationId) {
            fetch(`/admin/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications(); // Refresh the notification list
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }

        function markAllNotificationsAsRead() {
            fetch('/admin/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications(); // Refresh the notification list
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
            });
        }

        // Toast Notification System
        document.addEventListener('DOMContentLoaded', function() {
            // Show any existing toast from session
            const toastElement = document.querySelector('.toast-notification');
            if (toastElement) {
                // Show the toast
                setTimeout(() => {
                    toastElement.classList.add('show');
                    
                    // Auto-hide after duration
                    const duration = parseInt(toastElement.dataset.duration) || 3000;
                    
                    // Start progress bar animation
                    const progressBar = toastElement.querySelector('.progress-bar');
                    if (progressBar) {
                        progressBar.style.animation = `progress ${duration/1000}s linear forwards`;
                    }
                    
                    // Auto-remove after duration
                    setTimeout(() => {
                        hideToast(toastElement);
                    }, duration);
                }, 100);
            }
        });

        function showToast(message, type = 'success', duration = 3000) {
            // If first argument is an object, it's the toast element from session
            if (typeof message === 'object' && message.classList && message.classList.contains('toast-notification')) {
                const toastElement = message;
                setTimeout(() => {
                    toastElement.classList.add('show');
                    
                    // Auto-hide after duration
                    const duration = parseInt(toastElement.dataset.duration) || 3000;
                    
                    // Start progress bar animation
                    const progressBar = toastElement.querySelector('.progress-bar');
                    if (progressBar) {
                        progressBar.style.animation = `progress ${duration/1000}s linear forwards`;
                    }
                    
                    // Auto-remove after duration
                    setTimeout(() => {
                        hideToast(toastElement);
                    }, duration);
                }, 100);
                return;
            }
            // Create a new toast notification
            const container = document.getElementById('toast-container');
            const toastId = 'toast-' + Date.now();
            
            // Determine colors and icons based on type
            const typeConfig = {
                success: {
                    bg: 'bg-emerald-50',
                    border: 'border-emerald-500',
                    iconBg: 'bg-emerald-100',
                    icon: 'M5 13l4 4L19 7',
                    iconColor: 'text-emerald-600',
                    progress: 'bg-emerald-500',
                    title: 'Success'
                },
                error: {
                    bg: 'bg-red-50',
                    border: 'border-red-500',
                    iconBg: 'bg-red-100',
                    icon: 'M6 18L18 6M6 6l12 12',
                    iconColor: 'text-red-600',
                    progress: 'bg-red-500',
                    title: 'Error'
                },
                info: {
                    bg: 'bg-blue-50',
                    border: 'border-blue-500',
                    iconBg: 'bg-blue-100',
                    icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    iconColor: 'text-blue-600',
                    progress: 'bg-blue-500',
                    title: 'Info'
                },
                warning: {
                    bg: 'bg-amber-50',
                    border: 'border-amber-500',
                    iconBg: 'bg-amber-100',
                    icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    iconColor: 'text-amber-600',
                    progress: 'bg-amber-500',
                    title: 'Warning'
                }
            };
            
            const config = typeConfig[type] || typeConfig.info;
            
            // Create toast element
            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = 'toast-notification';
            toast.dataset.type = type;
            toast.dataset.duration = duration;
            
            // Create toast HTML
            toast.innerHTML = `
                <div class="relative flex items-start p-4 rounded-xl shadow-2xl ${config.bg} border-l-8 ${config.border} overflow-hidden">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full ${config.iconBg} flex items-center justify-center">
                            <svg class="w-5 h-5 ${config.iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${config.icon}"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Message -->
                    <div class="ml-3 flex-1">
                        <div class="text-sm font-semibold text-gray-900">
                            ${config.title}
                        </div>
                        <p class="mt-1 text-sm text-gray-600">
                            ${message}
                        </p>
                    </div>
                    
                    <!-- Close Button -->
                    <button type="button" 
                            class="ml-4 -mt-2 -mr-2 p-1.5 text-gray-400 hover:text-gray-900 rounded-full hover:bg-gray-100 transition-colors duration-200" 
                            onclick="this.closest('.toast-notification').remove()">
                        <span class="sr-only">Close</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    
                    <!-- Progress Bar -->
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100 overflow-hidden">
                        <div class="h-full ${config.progress} progress-bar"></div>
                    </div>
                </div>`;
            
            // Add to container
            container.prepend(toast);
            
            // Trigger reflow
            void toast.offsetWidth;
            
            // Show the toast
            toast.classList.add('show');
            
            // Start progress bar animation
            const progressBar = toast.querySelector('.progress-bar');
            if (progressBar) {
                progressBar.style.animation = `progress ${duration/1000}s linear forwards`;
            }
            
            // Auto-remove after duration
            setTimeout(() => {
                hideToast(toast);
            }, duration);
            
            return toast;
        }
        
        function hideToast(toastElement) {
            if (!toastElement) return;
            
            toastElement.classList.remove('show');
            toastElement.classList.add('hide');
            
            // Remove from DOM after animation
            setTimeout(() => {
                if (toastElement && toastElement.parentNode) {
                    toastElement.remove();
                }
            }, 500);
        }
        
        // Initialize any toasts from session data
        document.addEventListener('DOMContentLoaded', function() {
            const toastElement = document.querySelector('.toast-notification');
            if (toastElement) {
                const type = toastElement.dataset.type || 'info';
                const message = toastElement.querySelector('.text-gray-600')?.textContent || '';
                const duration = parseInt(toastElement.dataset.duration) || 3000;
                
                // Remove the static toast and create a dynamic one
                toastElement.remove();
                showToast(message, type, duration);
            }
        });
    </script>

    @stack('scripts')
</body>
</html>