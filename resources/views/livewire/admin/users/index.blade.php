<div class="space-y-6">
    <x-admin.page-header title="Manajemen User" description="CRUD akun admin. Semua admin akses setara, tanpa role." />

    <header class="border-b border-[#e8e1d6] bg-[#f5f1e8] shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <div class="grid h-9 w-9 place-items-center rounded-md bg-[#0f0f10] text-white shadow-sm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19V8l8-4 8 4v11" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M9 19v-6h6v6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="leading-tight">
                    <div class="font-display text-lg font-black tracking-tight text-[#141414]">DENAH</div>
                    <div class="text-[10px] uppercase tracking-[0.14em] text-[#4D4945]">Sistem Pemetaan Permukiman</div>
                </div>
            </div>

            <nav class="hidden items-center gap-8 text-sm font-medium text-[#3A3733] lg:flex">
                <a href="#" class="hover:text-[#141414]">Beranda</a>
                <a href="#" class="hover:text-[#141414]">Peta</a>
                <a href="#" class="hover:text-[#141414]">Data</a>
                <a href="#" class="hover:text-[#141414]">Statistik</a>
                <a href="#" class="hover:text-[#141414]">Tentang</a>
                <a href="#" class="hover:text-[#141414]">Metodologi</a>
            </nav>

            <div class="hidden items-center gap-2 rounded-full border border-[#d8cfc4] bg-white/60 px-1 py-1 text-[11px] font-semibold text-[#3A3733] sm:flex">
                <span class="rounded-full bg-white px-2.5 py-1 text-[#141414] shadow-sm">ID</span>
                <span class="rounded-full px-2.5 py-1">EN</span>
            </div>
        </div>
    </header>

    <section class="overflow-hidden bg-[#1c1817] text-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#C0B9AF]">Statistik</p>
            <h1 class="mt-3 font-display text-3xl font-black leading-tight sm:text-4xl">Statistik Permukiman Bengkulu</h1>
            <p class="mt-2 max-w-3xl text-sm text-[#C6C0B8] sm:text-base">Ringkasan klasifikasi dan sebaran data permukiman berdasar wilayah dan tahun.</p>
        </div>
    </section>

    <div class="grid gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-400">Total Admin</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <p class="font-display text-3xl font-bold text-gray-900">{{ \App\Models\User::count() }}</p>
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-denah-terracotta/10 text-denah-terracotta">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z" />
                    </svg>
                </span>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-400">Admin Baru</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <p class="font-display text-3xl font-bold text-gray-900">{{ \App\Models\User::whereDate('created_at', today())->count() }}</p>
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-emerald-500/10 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m9-7a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-400">Akun Saya</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate font-display text-xl font-bold text-gray-900">{{ auth()->user()?->name ?? 'Admin' }}</p>
                    <p class="mt-1 truncate text-xs text-gray-500">{{ auth()->user()?->email ?? 'user@denah.id' }}</p>
                </div>
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-amber-500/10 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </span>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 font-display text-base font-bold text-gray-900">{{ $editingId ? 'Edit Admin' : 'Tambah Admin' }}</h3>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" wire:model="name" wire:model.blur="name"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" wire:model="email" wire:model.blur="email"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">{{ $editingId ? 'Password Baru (opsional, reset)' : 'Password' }}</label>
                    <input type="password" wire:model="password" wire:model.blur="password"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                    <input type="password" wire:model="passwordConfirmation" wire:model.blur="passwordConfirmation"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('passwordConfirmation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-denah-terracotta px-4 py-2 text-sm font-semibold text-denah-cream hover:bg-denah-terracotta-light">{{ $editingId ? 'Perbarui' : 'Tambah' }}</button>
                    @if ($editingId)
                        <button type="button" wire:click="resetForm" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                    @endif
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="mb-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari admin..."
                       class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-400">
                        <tr><th class="py-2 pr-3">Nama</th><th class="py-2 pr-3">Email</th><th class="py-2 pr-3">Dibuat</th><th class="py-2 pr-3">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 pr-3">
                                    <div class="flex items-center gap-2">
                                        <span class="grid h-8 w-8 place-items-center rounded-full bg-denah-terracotta/15 text-xs font-bold text-denah-terracotta">{{ strtoupper($user->initials()) }}</span>
                                        <div>
                                            <div class="font-medium text-gray-800">{{ $user->name }}</div>
                                            @if ($user->id === auth()->id()) <span class="text-xs text-denah-terracotta">(Anda)</span> @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 pr-3 text-gray-600">{{ $user->email }}</td>
                                <td class="py-3 pr-3 text-gray-500">{{ $user->created_at?->format('d M Y') }}</td>
                                <td class="whitespace-nowrap py-3 pr-3">
                                    <button wire:click="edit({{ $user->id }})" class="text-xs font-semibold text-denah-terracotta hover:underline">Edit</button>
                                    <button wire:click="confirmDelete('user', {{ $user->id }}, 'Hapus admin &quot;{{ $user->name }}&quot;?')" class="ml-2 text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-gray-400">Belum ada admin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
    <x-admin.confirm-modal />
</div>

