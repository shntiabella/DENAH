@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-denah-ink/20 bg-white/70 focus:border-denah-terracotta focus:ring-denah-terracotta rounded-md shadow-sm']) }}>
