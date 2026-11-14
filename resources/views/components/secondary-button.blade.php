<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-white text-slate-800 text-xs font-bold uppercase tracking-wider rounded-lg border border-slate-300 hover:bg-slate-50 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 transition duration-200 disabled:opacity-50 disabled:pointer-events-none shadow-2xs']) }}>
    {{ $slot }}
</button>
