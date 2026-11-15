@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
    'class' => 'w-full min-h-[44px] px-3.5 py-2.5 bg-white border border-slate-200/90 text-sm text-slate-900 placeholder:text-slate-500 rounded-xl shadow-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none transition'
]) }}>
