@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-b from-gray-50 to-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Contact Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl md:text-6xl">
                <span class="block">Get In Touch</span>
                <span class="block text-pink-500">We'd Love to Hear From You</span>
            </h1>
            <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                Have questions about our products or need assistance with an order? Our team is here to help.
            </p>
        </div>

        <!-- Contact Grid -->
        <div class="mt-12 grid gap-12 lg:grid-cols-2 lg:gap-8">
            <!-- Contact Form -->
            <div class="lg:col-start-1">
                <div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Send Us a Message</h2>
                    <form action="#" method="POST" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <div class="mt-1">
                                <input type="text" name="name" id="name" autocomplete="name" class="py-3 px-4 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <div class="mt-1">
                                <input id="email" name="email" type="email" autocomplete="email" class="py-3 px-4 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500">
                            </div>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone (Optional)</label>
                            <div class="mt-1">
                                <input type="text" name="phone" id="phone" autocomplete="tel" class="py-3 px-4 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500">
                            </div>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                            <div class="mt-1">
                                <textarea id="message" name="message" rows="4" class="py-3 px-4 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full flex justify-center py-3 px-6 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-pink-500 hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors">
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="lg:col-start-2">
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Contact Information</h2>
                        <div class="space-y-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-pink-100 text-pink-500">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 text-base text-gray-500">
                                    <p>Email us at</p>
                                    <p class="font-medium text-gray-900">support@urpearl.com</p>
                                </div>
                            </div>

                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-pink-100 text-pink-500">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 text-base text-gray-500">
                                    <p>Call us at</p>
                                    <p class="font-medium text-gray-900">+1 (555) 123-4567</p>
                                    <p class="mt-1 text-sm">Monday - Friday, 9:00 AM - 6:00 PM EST</p>
                                </div>
                            </div>

                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-pink-100 text-pink-500">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 text-base text-gray-500">
                                    <p>Visit our showroom</p>
                                    <p class="font-medium text-gray-900">123 Pearl Street</p>
                                    <p class="text-gray-900">New York, NY 10001</p>
                                    <p class="mt-1 text-sm">By appointment only</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Map Placeholder -->
                    <div class="h-64 bg-gray-200 flex items-center justify-center">
                        <p class="text-gray-500">Map would be displayed here</p>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="mt-8 bg-white rounded-2xl shadow-sm p-6 sm:p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-medium text-gray-900">What's your return policy?</h3>
                            <p class="mt-1 text-gray-600">We offer a 30-day return policy for all unworn items with original tags attached.</p>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">How long does shipping take?</h3>
                            <p class="mt-1 text-gray-600">Standard shipping takes 3-5 business days. Express options are available at checkout.</p>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Do you offer international shipping?</h3>
                            <p class="mt-1 text-gray-600">Yes, we ship worldwide. Shipping costs and delivery times vary by destination.</p>
                        </div>
                    </div>
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
                <span class="block">Still have questions?</span>
            </h2>
            <p class="mt-3 max-w-md mx-auto text-base text-pink-100 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                Check out our FAQ page or contact our customer support team for more information.
            </p>
            <div class="mt-8 flex justify-center space-x-4">
                <a href="#" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-pink-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-8">
                    View FAQ
                </a>
                <a href="tel:+15551234567" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700 md:py-4 md:text-lg md:px-8">
                    Call Us Now
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
