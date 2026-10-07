{{-- Tab switcher Bahasa Indonesia | English untuk form CMS.
     Dipakai bersama wrapper <div x-data="{ lang: 'id' }"> lalu field ID/EN
     ditampilkan/di-sembunyikan dengan x-show="lang === 'id'" / "lang === 'en'". --}}
<div class="flex items-center gap-1 self-start rounded-lg bg-gray-100 p-1 text-xs font-semibold">
    <button type="button" @click="lang = 'id'"
            :class="lang === 'id' ? 'bg-white text-denah-terracotta shadow-sm' : 'text-gray-500 hover:text-gray-700'"
            class="rounded-md px-3 py-1.5 transition">
        🇮🇩 Bahasa Indonesia
    </button>
    <button type="button" @click="lang = 'en'"
            :class="lang === 'en' ? 'bg-white text-denah-terracotta shadow-sm' : 'text-gray-500 hover:text-gray-700'"
            class="rounded-md px-3 py-1.5 transition">
        🇬🇧 English
    </button>
</div>
