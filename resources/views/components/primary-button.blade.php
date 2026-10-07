<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-denah-terracotta border border-transparent rounded-md font-semibold text-xs text-denah-cream uppercase tracking-widest shadow-sm shadow-denah-terracotta/25 hover:bg-denah-terracotta-light hover:text-denah-dark focus:bg-denah-terracotta-light active:bg-denah-terracotta focus:outline-none focus:ring-2 focus:ring-denah-terracotta focus:ring-offset-2 focus:ring-offset-denah-terracotta/20 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
