@extends('layouts.app')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
        --font-heading: 'Playfair Display', serif;
        --font-body: 'Inter', sans-serif;
        --color-primary: #000000;
        --color-accent: #FF4D00;
        --color-light: #F8F8F8;
        --color-gray: #707070;
    }

    body {
        font-family: var(--font-body);
        color: var(--color-primary);
        background-color: #FFFFFF;
        overflow-x: hidden;
    }

    h1, h2, h3, h4, h5, h6 {
        font-family: var(--font-heading);
        font-weight: 600;
        letter-spacing: -0.02em;
    }

    .font-heading {
        font-family: var(--font-heading);
    }

    .font-body {
        font-family: var(--font-body);
    }

    /* Animations */
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
        100% { transform: translateY(0px); }
    }

    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-slow { animation: float 8s ease-in-out infinite; }
    .animate-float-medium { animation: float 7s ease-in-out infinite; }
    
    .animate-fade-in-up {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
    .delay-400 { animation-delay: 0.4s; }
    .delay-500 { animation-delay: 0.5s; }

    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-body);
        font-weight: 500;
        font-size: 0.875rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 0.75rem 2rem;
        border-radius: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-primary {
        background-color: var(--color-primary);
        color: white;
        border: 1px solid var(--color-primary);
    }

    .btn-primary:hover {
        background-color: transparent;
        color: var(--color-primary);
    }

    .btn-outline {
        background-color: transparent;
        color: var(--color-primary);
        border: 1px solid var(--color-primary);
    }

    .btn-outline:hover {
        background-color: var(--color-primary);
        color: white;
    }

    .btn-accent {
        background-color: var(--color-accent);
        color: white;
        border: 1px solid var(--color-accent);
    }

    .btn-accent:hover {
        background-color: transparent;
        color: var(--color-accent);
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f8f8f8;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #d1d1d1;
        border-radius: 3px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }

    /* Custom selection */
    ::selection {
        background: var(--color-primary);
        color: white;
    }

    /* Grid lines */
    .grid-lines {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 20px;
        padding: 0 40px;
    }

    .grid-line {
        height: 100%;
        border-left: 1px solid rgba(0, 0, 0, 0.05);
    }

    /* Scrolling Words Banner */
    .scrolling-words-container {
        display: flex;
        align-items: center;
        font-size: 1.5rem;
        font-weight: 600;
        color: #000;
        overflow: hidden;
        white-space: nowrap;
        padding: 1rem 0;
    }

    .scrolling-words-box {
        display: flex;
        animation: scrollLeft 20s linear infinite;
    }

    .scrolling-words-box span {
        margin-right: 3rem;
        display: inline-block;
        position: relative;
    }

    .scrolling-words-box span:after {
        content: '•';
        position: absolute;
        right: -1.5rem;
        color: var(--color-accent);
    }

    .scrolling-words-box span:last-child:after {
        display: none;
    }

    @keyframes scrollLeft {
        0% { transform: translateX(0%); }
        100% { transform: translateX(-50%); }
    }

    /* Product Card Hover Effect */
    .product-card {
        position: relative;
        overflow: hidden;
    }

    .product-card img {
        transition: transform 0.7s ease;
    }

    .product-card:hover img {
        transform: scale(1.05);
    }

    .product-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.9);
        transform: translateY(100%);
        transition: transform 0.3s ease-in-out;
        padding: 1.5rem;
    }

    .product-card:hover .product-overlay {
        transform: translateY(0);
    }
</style>
@endpush

@section('content')
    <section class="relative h-screen flex items-center overflow-hidden bg-gray-50">
        <!-- Grid Lines Background -->
        <div class="absolute inset-0 grid grid-cols-12 gap-0 h-full w-full opacity-10 pointer-events-none">
            @for($i = 0; $i < 12; $i++)
                <div class="h-full w-px bg-gray-400"></div>
            @endfor
        </div>
        
        <!-- Background Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-white via-white/90 to-transparent z-0"></div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center min-h-[80vh]">
                <!-- Left Column - Text Content -->
                <div class="animate-fade-in-up space-y-8">
                    <div class="overflow-hidden">
                        <h1 class="text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-bold leading-none tracking-tight">
                            <span class="block transform transition-all duration-1000 translate-y-0 opacity-100">where – <span class="italic font-light">a style</span></span>
                            <span class="block text-gray-400 transform transition-all duration-1000 translate-y-0 opacity-100">moment</span>
                        </h1>
                    </div>
                    
                    <p class="text-gray-600 text-lg md:text-xl max-w-lg leading-relaxed">
                        Discover the essence of timeless elegance with our latest collection. Each piece tells a story of craftsmanship and sophistication.
                    </p>
                    
                    <div class="flex flex-wrap gap-6 pt-4">
                        <a href="{{ route('products.index') }}" class="group relative px-8 py-4 bg-black text-white text-sm uppercase tracking-widest font-medium overflow-hidden transition-all duration-300 hover:bg-gray-900">
                            <span class="relative z-10">SHOP NOW</span>
                            <span class="absolute inset-0 bg-orange-500 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-500"></span>
                        </a>
                        <a href="#collections" class="group flex items-center text-sm uppercase tracking-widest font-medium border-b-2 border-transparent hover:border-black transition-all duration-300">
                            <span class="relative">
                                <span class="block group-hover:-translate-x-1 transition-transform duration-300">EXPLORE</span>
                                <span class="absolute bottom-0 left-0 w-0 h-px bg-black group-hover:w-full transition-all duration-300"></span>
                            </span>
                            <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-2 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                    
                    <!-- Scroll Indicator -->
                    <div class="absolute bottom-12 left-0 right-0 text-center opacity-0 animate-fade-in-up" style="animation-delay: 0.8s">
                        <div class="inline-flex flex-col items-center">
                            <span class="text-xs font-medium tracking-widest text-gray-500 mb-2">SCROLL</span>
                            <div class="w-px h-12 bg-gray-300"></div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Image -->
                <div class="relative h-[80vh] hidden lg:block transform transition-transform duration-1000 hover:scale-[1.02]">
                    <div class="absolute inset-0 rounded-3xl overflow-hidden shadow-2xl">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent z-10"></div>
                        <img 
                            src="https://images.unsplash.com/photo-1492707892479-7bc8d5a4ee93?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1035&q=80" 
                            alt="Fashion Model"
                            class="w-full h-full object-cover object-center transform transition-transform duration-1000 hover:scale-105"
                            loading="eager"
                            data-parallax
                        />
                    </div>
                    
                    <!-- Floating Badge -->
                    <div class="absolute -left-6 top-1/2 transform -translate-y-1/2 bg-white p-5 shadow-xl rotate-90 origin-left group cursor-pointer">
                        <span class="text-xs font-medium tracking-widest flex items-center">
                            <span class="mr-2 group-hover:mr-3 transition-all duration-300">EXPLORE</span>
                            <svg class="w-3 h-3 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </span>
                    </div>
                    
                    <!-- Decorative Elements -->
                    <div class="absolute -bottom-8 -right-8 w-40 h-40 bg-gray-100 rounded-full mix-blend-multiply opacity-20 animate-pulse"></div>
                    <div class="absolute -top-12 -right-12 w-24 h-24 border-2 border-gray-200 rounded-full transform animate-float"></div>
                </div>
            </div>
        </div>
    </section>

<!-- Scrolling Words Banner -->
<div class="bg-black text-white overflow-hidden">
    <div class="scrolling-words-container">
        <div class="scrolling-words-box">
            <marquee behavior="scroll" direction="left">
                <span>Minimalist Design</span>
                <span>Timeless Elegance</span>
                <span>Sustainable Materials</span>
                <span>Handcrafted Quality</span>
                <span>Ethical Production</span>
            </marquee>
        </div>
    </div>
</div>

<!-- Brand Collaboration -->
<section class="py-16 md:py-24 bg-white border-t border-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <p class="text-xs font-medium tracking-widest text-gray-500 mb-2">FEATURED IN</p>
            <h2 class="text-3xl md:text-4xl font-medium mb-4">As seen in</h2>
            <div class="w-16 h-0.5 bg-gray-300 mx-auto"></div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-5 gap-8 items-center">
            <div class="flex justify-center opacity-60 hover:opacity-100 transition-opacity">
                <span class="text-2xl font-bold italic">VOGUE</span>
            </div>
            <div class="flex justify-center opacity-60 hover:opacity-100 transition-opacity">
                <span class="text-2xl font-bold italic">ELLE</span>
            </div>
            <div class="flex justify-center opacity-60 hover:opacity-100 transition-opacity">
                <span class="text-2xl font-bold">BAZAAR</span>
            </div>
            <div class="flex justify-center opacity-60 hover:opacity-100 transition-opacity">
                <span class="text-2xl font-bold">GQ</span>
            </div>
            <div class="flex justify-center opacity-60 hover:opacity-100 transition-opacity">
                <span class="text-2xl font-bold">VANITY FAIR</span>
            </div>
        </div>
    </div>
</section>

<!-- Editorial Collection -->
<section class="py-24 bg-white" id="collections">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-medium mb-6">It's - about moments <span class="text-gray-400">24</span></h2>
            <p class="text-gray-600 max-w-2xl mx-auto leading-relaxed">Each piece in our collection is a testament to timeless elegance and contemporary design.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-16">
            <!-- Large Left Image -->
            <div class="md:col-span-8 h-[600px] relative group overflow-hidden rounded-2xl">
                <img 
                    src="https://images.unsplash.com/photo-1485462537746-965f33f7f6a7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                    alt="Editorial Collection"
                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                    loading="lazy"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex flex-col justify-end p-8 text-white">
                    <span class="text-sm font-medium tracking-widest mb-2">NEW COLLECTION</span>
                    <h3 class="text-3xl font-medium mb-4">Spring/Summer 2025</h3>
                    <p class="text-gray-200 mb-6 max-w-md">Discover our latest collection inspired by the beauty of nature and modern architecture.</p>
                    <a href="#" class="inline-flex items-center text-sm font-medium tracking-widest uppercase border-b border-transparent hover:border-white transition-colors">
                        Explore Collection
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Right Side - Two Smaller Images -->
            <div class="md:col-span-4 space-y-6">
                <div class="h-[288px] relative group overflow-hidden rounded-2xl">
                    <img 
                        src="https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=880&q=80" 
                        alt="Accessories"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex flex-col justify-end p-6 text-white">
                        <h3 class="text-xl font-medium mb-2">Accessories</h3>
                        <a href="#" class="text-sm font-medium tracking-widest uppercase opacity-0 group-hover:opacity-100 transition-opacity">
                            Shop Now
                        </a>
                    </div>
                </div>
                <div class="h-[288px] relative group overflow-hidden rounded-2xl">
                    <img 
                        src="https://images.unsplash.com/photo-1445205170230-053b83016042?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1471&q=80" 
                        alt="Jewelry"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex flex-col justify-end p-6 text-white">
                        <h3 class="text-xl font-medium mb-2">Jewelry</h3>
                        <a href="#" class="text-sm font-medium tracking-widest uppercase opacity-0 group-hover:opacity-100 transition-opacity">
                            Discover
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('products.index') }}" class="inline-flex items-center text-sm font-medium tracking-widest uppercase border-b border-transparent hover:border-black transition-colors">
                View All Collections
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>
        </div>
    </div>
</section>
        
<!-- Testimonial Section -->
<section class="py-24 bg-gray-50 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-white/80 to-white/20"></div>
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M54.627 0h-7.41L36.036 21.3h-1.512a1.5 1.5 0 0 0-1.5 1.5v1.5h.006l-1.62 25.2a1.5 1.5 0 0 0 1.5 1.5h18.717a1.5 1.5 0 0 0 1.5-1.5V3a3 3 0 0 0-2.88-2.999zM12.783 0H5.373A3 3 0 0 0 2.5 3v45.5a1.5 1.5 0 0 0 1.5 1.5h18.717a1.5 1.5 0 0 0 1.5-1.5V22.8a1.5 1.5 0 0 0-1.5-1.5h-1.512L15.195 0h-2.412z" fill="%23f3f4f6" fill-rule="evenodd"/%3E%3C/svg%3E');"></div>
    </div>
    
    <div class="container mx-auto px-4 relative">
        <div class="max-w-4xl mx-auto">
            <div class="flex flex-col md:flex-row items-center">
                <!-- Profile Image -->
                <div class="w-32 h-32 md:w-40 md:h-40 rounded-full overflow-hidden border-4 border-white shadow-lg mb-8 md:mb-0 md:mr-12">
                    <img 
                        src="https://randomuser.me/api/portraits/women/68.jpg" 
                        alt="Sarah Johnson"
                        class="w-full h-full object-cover"
                    />
                </div>
                
                <!-- Testimonial Content -->
                <div class="flex-1 text-center md:text-left">
                    <div class="text-4xl md:text-5xl font-medium font-heading leading-tight mb-6">
                        "Elegance is not standing out, but being remembered."
                    </div>
                    
                    <div class="flex flex-col md:flex-row md:items-center justify-between mt-8">
                        <div class="mb-4 md:mb-0">
                            <h4 class="text-lg font-medium">Sarah Johnson</h4>
                            <p class="text-gray-600">Fashion Editor, Vogue</p>
                        </div>
                        
                        <!-- Stats -->
                        <div class="flex space-x-8">
                            <div class="text-center">
                                <div class="text-2xl font-bold">15+</div>
                                <div class="text-sm text-gray-600">Years Experience</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold">1.2M</div>
                                <div class="text-sm text-gray-600">Followers</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold">4.9</div>
                                <div class="text-sm text-gray-600">Rating</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Scrolling Words Banner -->
<section class="py-6 bg-black text-white overflow-hidden">
    <div class="whitespace-nowrap animate-marquee">
        <span class="inline-block px-4 text-xl font-medium tracking-widest">MOMENTO • MOMENTO • MOMENTO •</span>
        <span class="inline-block px-4 text-xl font-medium tracking-widest">MOMENTO • MOMENTO • MOMENTO •</span>
        <span class="inline-block px-4 text-xl font-medium tracking-widest">MOMENTO • MOMENTO • MOMENTO •</span>
    </div>
</section>

<!-- Add marquee animation to styles -->
<style>
    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    
    .animate-marquee {
        display: inline-block;
        animation: marquee 20s linear infinite;
    }
    
    .animate-marquee:hover {
        animation-play-state: paused;
    }
</style>

<!-- Product Highlight Section -->
<section class="py-24 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Left Column - Content -->
            <div class="animate-fade-in-up">
                <span class="text-xs font-medium tracking-widest text-gray-500 block mb-4">FEATURED</span>
                <h2 class="text-4xl md:text-5xl font-medium mb-6">The Art of <span class="text-gray-400">Pearl Crafting</span></h2>
                <p class="text-gray-600 mb-8 text-lg leading-relaxed">
                    Our master jewelers combine traditional techniques with modern design to create pieces that transcend time. Each pearl is meticulously selected and hand-set to ensure the highest quality and beauty.
                </p>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-900 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <h4 class="font-medium text-gray-900">Ethically Sourced Pearls</h4>
                            <p class="text-sm text-gray-500">Responsibly harvested from sustainable farms</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-900 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <h4 class="font-medium text-gray-900">Handcrafted Excellence</h4>
                            <p class="text-sm text-gray-500">Each piece made with precision and care</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-900 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <h4 class="font-medium text-gray-900">Lifetime Warranty</h4>
                            <p class="text-sm text-gray-500">Guaranteed quality and craftsmanship</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('products.index') }}" class="inline-flex items-center mt-8 text-sm font-medium tracking-widest uppercase border-b border-black hover:border-transparent transition-colors">
                    Discover More
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            </div>
            
            <!-- Right Column - Images -->
            <div class="grid grid-cols-2 gap-4">
                <div class="relative h-80 overflow-hidden group">
                    <img 
                        src="https://images.unsplash.com/photo-1643330683233-ff2ae89b978c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                        alt="Pearl Crafting"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <div class="relative h-80 overflow-hidden group">
                    <img 
                        src="https://images.unsplash.com/photo 1603974731233-afdd8b1de47e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                        alt="Pearl Jewelry"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <div class="relative h-80 overflow-hidden group col-span-2">
                    <img 
                        src="https://images.unsplash.com/photo-1603974379569-9bdb32097a5e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                        alt="Pearl Collection"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
            </div>
        </div>
    </div>
</section>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                Featured Products
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-8">
                Handpicked selections from our finest collection, each piece telling its own story of elegance and craftsmanship.
            </p>
            <div class="w-24 h-1 bg-gradient-to-r from-pink-500 to-pink-600 mx-auto rounded-full"></div>
        </div>
        
        @if(isset($featuredProducts) && $featuredProducts->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
                @foreach($featuredProducts as $index => $product)
                    <div class="animate-fade-in-up" style="animation-delay: {{ $index * 100 }}ms;">
                        @include('products.partials.product-card', ['product' => $product])
                    </div>
                @endforeach
            </div>
            
            <div class="text-center mt-12">
                <a href="{{ route('products.index') }}" class="btn-primary text-lg px-8 py-4 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                    View All Products
                </a>
            </div>
        @else
            <!-- Placeholder when no products are available -->
            <div class="text-center py-16">
                <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4m0 0l-4-4m4 4V3"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No products available yet</h3>
                <p class="text-gray-600 mb-6">Products will appear here once they are added to the catalog.</p>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.products.create') }}" class="btn-primary">
                            Add Your First Product
                        </a>
                    @endif
                @endauth
            </div>
        @endif
    </div>

    <!-- Categories Section -->
    <div class="mb-20">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                Shop by Category
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-8">
                Explore our carefully curated categories, each offering unique pieces to complement your style.
            </p>
            <div class="w-24 h-1 bg-gradient-to-r from-pink-500 to-pink-600 mx-auto rounded-full"></div>
        </div>
        
        @if(isset($categories) && $categories->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($categories as $index => $category)
                    <div class="animate-fade-in-up" style="animation-delay: {{ $index * 150 }}ms;">
                        <a href="{{ route('products.index', ['category' => $category->id]) }}" 
                           class="group block bg-white rounded-2xl shadow-soft p-8 text-center hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300">
                            <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-pink-100 to-urpearl-beige-100 rounded-full flex items-center justify-center group-hover:from-pink-200 group-hover:to-urpearl-beige-200 transition-all duration-300 group-hover:scale-110">
                                <svg class="w-10 h-10 text-pink-600 group-hover:text-pink-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 group-hover:text-pink-600 transition-colors mb-2">
                                {{ $category->name }}
                            </h3>
                            <p class="text-sm text-gray-500 mb-4">
                                {{ $category->products_count ?? 0 }} {{ Str::plural('product', $category->products_count ?? 0) }}
                            </p>
                            <div class="inline-flex items-center text-pink-600 group-hover:text-pink-700 font-medium">
                                <span class="mr-2">Explore</span>
                                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Default categories when none exist -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $defaultCategories = [
                        ['name' => 'Pearl Necklaces', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                        ['name' => 'Pearl Earrings', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['name' => 'Pearl Bracelets', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        ['name' => 'Pearl Rings', 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z']
                    ];
                @endphp
                
                @foreach($defaultCategories as $index => $category)
                    <div class="animate-fade-in-up" style="animation-delay: {{ $index * 150 }}ms;">
                        <a href="{{ route('products.index') }}" 
                           class="group block bg-white rounded-2xl shadow-soft p-8 text-center hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300">
                            <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-pink-100 to-urpearl-beige-100 rounded-full flex items-center justify-center group-hover:from-pink-200 group-hover:to-urpearl-beige-200 transition-all duration-300 group-hover:scale-110">
                                <svg class="w-10 h-10 text-pink-600 group-hover:text-pink-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $category['icon'] }}"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 group-hover:text-pink-600 transition-colors mb-2">
                                {{ $category['name'] }}
                            </h3>
                            <p class="text-sm text-gray-500 mb-4">
                                Coming Soon
                            </p>
                            <div class="inline-flex items-center text-pink-600 group-hover:text-pink-700 font-medium">
                                <span class="mr-2">Explore</span>
                                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Features Section -->
    <div class="bg-gradient-to-r from-pink-50 to-urpearl-beige-50 rounded-3xl p-8 lg:p-12 mb-20">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                Why Choose UrPearl SHOP?
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Experience the difference with our commitment to quality, authenticity, and exceptional service.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center group">
                <div class="w-16 h-16 mx-auto mb-6 bg-white rounded-full flex items-center justify-center shadow-lg group-hover:shadow-xl transform group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Authentic Quality</h3>
                <p class="text-sm text-gray-600 leading-relaxed mt-2">Every pearl is carefully selected and authenticated to ensure the highest quality and genuine beauty.</p>
            </div>
            
            <div class="text-center group">
                <div class="w-16 h-16 mx-auto mb-6 bg-white rounded-full flex items-center justify-center shadow-lg group-hover:shadow-xl transform group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Fast Shipping</h3>
                <p class="text-sm text-gray-600 leading-relaxed mt-2">Quick and secure delivery to your doorstep with tracking and insurance for peace of mind.</p>
            </div>
            
            <div class="text-center group">
                <div class="w-16 h-16 mx-auto mb-6 bg-white rounded-full flex items-center justify-center shadow-lg group-hover:shadow-xl transform group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Customer Care</h3>
                <p class="text-sm text-gray-600 leading-relaxed mt-2">Dedicated support team ready to help with any questions or concerns about your purchase.</p>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    // Animate elements on scroll
    document.addEventListener('DOMContentLoaded', function() {
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.animate-fade-in-up');
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (elementTop < windowHeight - 100) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        };

        // Initial check
        animateOnScroll();
        
        // Check on scroll
        window.addEventListener('scroll', animateOnScroll);
    });
</script>
@endpush

@endsection