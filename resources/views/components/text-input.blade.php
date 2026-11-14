@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-black focus:border-black disabled:bg-slate-50 disabled:text-slate-400 transition duration-200 text-sm shadow-2xs']) }}>
