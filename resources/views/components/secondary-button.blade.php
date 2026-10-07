<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-denah-dark-alt border border-white/15 rounded-md font-semibold text-xs text-denah-cream uppercase tracking-widest shadow-sm hover:border-denah-terracotta hover:text-denah-terracotta-light focus:outline-none focus:ring-2 focus:ring-denah-terracotta focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
