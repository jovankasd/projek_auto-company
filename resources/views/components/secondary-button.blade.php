<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 min-h-[44px] bg-white hover:bg-slate-50 border border-slate-200/90 rounded-xl font-semibold text-xs sm:text-sm text-slate-800 tracking-wide shadow-sm hover:border-slate-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none transition-all duration-200'
]) }}>
    {{ $slot }}
</button>
