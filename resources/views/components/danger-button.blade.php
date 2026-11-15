<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 min-h-[44px] bg-red-600 hover:bg-red-700 active:bg-red-800 border border-transparent rounded-xl font-semibold text-xs sm:text-sm text-white tracking-wide shadow-sm hover:shadow-md hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none transition-all duration-200'
]) }}>
    {{ $slot }}
</button>
