@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'required' => false,
    'error' => null,
    'help' => null,
    'value' => null
])

@php
    $name = $name ?? $attributes->get('name');
    $error = $error ?? $errors->first($name);
    $value = $value ?? old($name, $attributes->get('value'));
    
    $inputClasses = 'block w-full rounded-xl border-gray-300 shadow-sm transition-colors duration-200 focus:border-pink-500 focus:ring-pink-500 sm:text-sm';
    
    if ($error) {
        $inputClasses = 'block w-full rounded-xl border-red-300 shadow-sm transition-colors duration-200 focus:border-red-500 focus:ring-red-500 sm:text-sm';
    }
@endphp

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        @if($type === 'textarea')
            <textarea 
                name="{{ $name }}" 
                id="{{ $name }}"
                {{ $attributes->merge(['class' => $inputClasses]) }}
                @if($required) required @endif
            >{{ $value }}</textarea>
        @elseif($type === 'select')
            <select 
                name="{{ $name }}" 
                id="{{ $name }}"
                {{ $attributes->merge(['class' => $inputClasses]) }}
                @if($required) required @endif
            >
                {{ $slot }}
            </select>
        @else
            <input 
                type="{{ $type }}" 
                name="{{ $name }}" 
                id="{{ $name }}"
                value="{{ $value }}"
                {{ $attributes->merge(['class' => $inputClasses]) }}
                @if($required) required @endif
            />
        @endif
        
        @if($error)
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
        @endif
    </div>
    
    @if($error)
        <x-form-error>{{ $error }}</x-form-error>
    @endif
    
    @if($help && !$error)
        <p class="text-sm text-gray-500">{{ $help }}</p>
    @endif
</div>