@props(['type' => 'success', 'message' => null])

@php
    $config = [
        'success' => [
            'bg' => 'bg-green-50 dark:bg-green-900/20',
            'ring' => 'ring-green-200 dark:ring-green-800',
            'icon' => 'text-green-600 dark:text-green-400',
            'text' => 'text-green-800 dark:text-green-400',
            'button' => 'text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300',
            'iconPath' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
        ],
        'error' => [
            'bg' => 'bg-red-50 dark:bg-red-900/20',
            'ring' => 'ring-red-200 dark:ring-red-800',
            'icon' => 'text-red-600 dark:text-red-400',
            'text' => 'text-red-800 dark:text-red-400',
            'button' => 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300',
            'iconPath' => 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z',
        ],
        'warning' => [
            'bg' => 'bg-yellow-50 dark:bg-yellow-900/20',
            'ring' => 'ring-yellow-200 dark:ring-yellow-800',
            'icon' => 'text-yellow-600 dark:text-yellow-400',
            'text' => 'text-yellow-800 dark:text-yellow-400',
            'button' => 'text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300',
            'iconPath' => 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z',
        ],
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
            'ring' => 'ring-blue-200 dark:ring-blue-800',
            'icon' => 'text-blue-600 dark:text-blue-400',
            'text' => 'text-blue-800 dark:text-blue-400',
            'button' => 'text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300',
            'iconPath' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z',
        ],
    ];

    $styles = $config[$type] ?? $config['success'];
@endphp

@if($message)
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="
            $store.notifications.add('{{ $type }}', @js($message));
            setTimeout(() => show = false, 5000);
        "
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-x-full"
        x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-x-0"
        x-transition:leave-end="opacity-0 transform translate-x-full"
        {{ $attributes->merge(['class' => "fixed right-4 top-20 z-50 w-96 max-w-[calc(100vw-2rem)] rounded-lg p-4 shadow-lg ring-1 {$styles['bg']} {$styles['ring']}"]) }}
    >
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 {{ $styles['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="{{ $styles['iconPath'] }}" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium {{ $styles['text'] }}">{{ $message }}</p>
                </div>
            </div>
            <button @click="show = false" class="flex-shrink-0 {{ $styles['button'] }}">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </div>
@endif
