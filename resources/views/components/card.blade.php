@props([
    'padding' => 'md',
    'shadow' => 'soft',
    'hover' => true,
    'rounded' => '2xl'
])

@php
    $baseClasses = 'bg-white transition-all duration-300';
    
    $paddingClasses = [
        'none' => '',
        'sm' => 'p-4',
        'md' => 'p-6',
        'lg' => 'p-8',
        'xl' => 'p-12'
    ];
    
    $shadowClasses = [
        'none' => '',
        'soft' => 'shadow-soft',
        'lg' => 'shadow-soft-lg',
        'xl' => 'shadow-2xl'
    ];
    
    $roundedClasses = [
        'none' => '',
        'sm' => 'rounded-lg',
        'md' => 'rounded-xl',
        'lg' => 'rounded-2xl',
        'xl' => 'rounded-3xl',
        '2xl' => 'rounded-2xl'
    ];
    
    $hoverClasses = $hover ? 'hover:shadow-xl hover:-translate-y-1' : '';
    
    $classes = $baseClasses . ' ' . $paddingClasses[$padding] . ' ' . $shadowClasses[$shadow] . ' ' . $roundedClasses[$rounded] . ' ' . $hoverClasses;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>