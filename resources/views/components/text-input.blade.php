@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-200 bg-white/80 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm']) }}>
