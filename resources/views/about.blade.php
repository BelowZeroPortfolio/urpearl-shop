@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-b from-gray-50 to-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl md:text-6xl">
                <span class="block">About UrPearl</span>
                <span class="block text-pink-500">Our Story</span>
            </h1>
            <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                Discover the elegance and beauty of our handcrafted pearl jewelry collection.
            </p>
        </div>

        <!-- Our Story -->
        <div class="mt-12 grid gap-16 lg:grid-cols-2 lg:gap-8 items-center">
            <div class="lg:col-start-1">
                <div class="text-base max-w-prose mx-auto lg:max-w-lg">
                    <h2 class="text-3xl font-extrabold text-gray-900 mb-6">Our Journey</h2>
                    <p class="text-lg text-gray-600 mb-4">
                        Founded in 2023, UrPearl began with a simple mission: to bring the timeless beauty of pearls to the modern woman. 
                        Each piece in our collection is carefully selected and crafted to celebrate the natural elegance of pearls.
                    </p>
                    <p class="text-lg text-gray-600 mb-6">
                        Our commitment to quality and sustainability ensures that every piece tells a story of craftsmanship and care.
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-pink-500 hover:bg-pink-600 transition-colors">
                            Explore Our Collection
                        </a>
                    </div>
                </div>
            </div>
            <div class="mt-12 lg:mt-0">
                <div class="relative rounded-lg overflow-hidden">
                    <img class="w-full h-auto rounded-lg" src="https://images.unsplash.com/photo-1605100804763-247f67b3557e?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80" alt="Pearl Jewelry">
                </div>
            </div>
        </div>

        <!-- Our Values -->
        <div class="mt-24">
            <h2 class="text-3xl font-extrabold text-gray-900 text-center mb-12">Our Values</h2>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Quality Craftsmanship</h3>
                    <p class="text-gray-600">Each piece is meticulously crafted with attention to detail and the finest materials.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Timeless Design</h3>
                    <p class="text-gray-600">Classic designs that transcend trends and remain elegant for years to come.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h10a2 2 0 110 4H7a6 6 0 01-6-6V5a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Ethical Sourcing</h3>
                    <p class="text-gray-600">Committed to responsible and sustainable sourcing of all our materials.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Testimonial Section -->
<div class="bg-pink-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-12">What Our Customers Say</h2>
            <div class="max-w-3xl mx-auto">
                <div class="bg-white p-8 rounded-xl shadow-sm">
                    <svg class="h-12 w-12 text-pink-400 mx-auto mb-4" fill="currentColor" viewBox="0 0 32 32">
                        <path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.016 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.016 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.552-7.104 6.624-9.024L25.864 4z"/>
                    </svg>
                    <p class="text-lg text-gray-600 mb-6">
                        "The quality of the pearls is exceptional. I've received so many compliments on my necklace. Will definitely be shopping here again!"
                    </p>
                    <div class="font-medium text-gray-900">Sarah Johnson</div>
                    <div class="text-pink-500">Loyal Customer</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-pink-500 rounded-2xl px-6 py-12 md:p-12 text-center">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                <span class="block">Ready to find your perfect piece?</span>
            </h2>
            <p class="mt-3 max-w-md mx-auto text-base text-pink-100 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                Explore our collection of handpicked pearl jewelry and find something special for yourself or a loved one.
            </p>
            <div class="mt-8 flex justify-center">
                <div class="inline-flex rounded-md shadow">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-pink-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                        Shop Now
                    </a>
                </div>
                <div class="ml-3 inline-flex">
                    <a href="{{ route('contact') }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700 md:py-4 md:text-lg md:px-10">
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
