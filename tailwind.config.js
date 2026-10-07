import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                /* Palet brand DENAH — tema "Minimalis Earthy Light Mode".
                   Terracotta Hangat (#C6653C) sebagai aksen utama, Ink Dark
                   Charcoal (#1A1A1A) untuk teks primer kontras tinggi, latar
                   utama putih bersih, dan kartu off-white/krem lembut. */
                gray: {
                    50: '#F7F7F5',
                    100: '#EEEEEA',
                    200: '#E5E5E0',
                    300: '#C9C9C4',
                    400: '#9CA3A9',
                    500: '#6B7280',
                    600: '#4B5563',
                    700: '#374151',
                    800: '#1F2937',
                    900: '#111827',
                    950: '#0B1220',
                },
                white: '#FFFFFF',
                denah: {
                    dark: '#2B2722', // espresso/tanah hangat — blok gelap (footer, CTA)
                    'dark-alt': '#332F2A', // variasi section gelap
                    terracotta: '#C6653C', // aksen utama: Terracotta Warm Earth (tombol, icon, angka aktif)
                    'terracotta-light': '#D98A62', // hover & highlight di latar gelap
                    'terracotta-dark': '#9E4E2C', // aksi tekan (focus, link tegas, hover tombol)
                    cream: '#F6F1E7', // krem/off-white lembut — kartu & aksen di atas putih
                    'cream-muted': '#D8CFC2', // teks krem redup di atas latar gelap
                    'cream-bg': '#F7F3EC', // background section terang — off-white krem hangat
                    paper: '#FFFFFF', // kartu / permukaan terang (putih bersih)
                    sand: '#E7E0D5', // garis & divider di atas permukaan terang (border kartu)
                    ink: '#1A1A1A', // teks primer — Hitam Pekat / Dark Charcoal (kontras tinggi)
                    'ink-muted': '#4B5563', // teks sekunder — Dark Gray (gray-600)
                    white: '#FFFFFF',
                },
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', ...defaultTheme.fontFamily.sans],
                display: ['Plus Jakarta Sans', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                soft: '0 4px 24px -8px rgba(46, 37, 27, 0.12)',
                glass: '0 8px 32px -12px rgba(46, 37, 27, 0.25)',
                warm: '0 20px 45px -20px rgba(143, 95, 51, 0.35)',
                'glass-dark': '0 8px 32px -12px rgba(0, 0, 0, 0.45)',
            },
        },
    },

    plugins: [forms],
};
