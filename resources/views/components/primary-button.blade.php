<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-black text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-800 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 transition duration-200 disabled:opacity-50 disabled:pointer-events-none shadow-sm']) }}>
    {{ $slot }}
</button>
