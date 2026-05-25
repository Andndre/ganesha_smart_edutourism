<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover, maximum-scale=1, user-scalable=0">
    <title>{{ __('Mode Luring - Penglipuran Smart Tour') }}</title>
    <meta name="theme-color" content="#FAF9F6">
    <meta name="mobile-web-app-capable" content="yes">

    <style>
        :root {
            --primary: #1E5128;
            --primary-hover: #152E1D;
            --surface: #FAF9F6;
            --charcoal: #191A19;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-100: #f3f4f6;
            --sat: env(safe-area-inset-top);
            --sab: env(safe-area-inset-bottom);
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--surface);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100dvh;
            margin: 0;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--charcoal);
            padding: var(--sat) 1.5rem var(--sab) 1.5rem;
        }

        .container {
            text-align: center;
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 2.5rem 1.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
        }

        .icon-container {
            width: 72px;
            height: 72px;
            background-color: #fef2f2;
            /* Light red/warning bg */
            color: #dc2626;
            /* Red color */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
        }

        .icon-container svg {
            width: 36px;
            height: 36px;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.025em;
        }

        p.desc {
            color: var(--gray-500);
            line-height: 1.5;
            font-size: 0.875rem;
            margin: 0 0 2rem 0;
        }

        .btn {
            display: block;
            width: 100%;
            background-color: var(--primary);
            color: white;
            padding: 0.875rem 1.5rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: transform 0.1s, background-color 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn:active {
            transform: scale(0.97);
        }

        .features {
            margin-top: 2rem;
            text-align: left;
            padding: 1.25rem;
            background-color: var(--gray-100);
            border-radius: 1rem;
        }

        .features h2 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            margin: 0 0 1rem 0;
            color: var(--gray-500);
        }

        .features ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .features li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            color: var(--charcoal);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .features svg {
            width: 18px;
            height: 18px;
            color: var(--primary);
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* Dark Mode Handling (Opsional, jika sistem user memakai dark mode) */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #121212;
                color: #f3f4f6;
            }

            .container {
                background-color: #1e1e1e;
                border-color: #2d2d2d;
            }

            h1,
            .features li {
                color: #f3f4f6;
            }

            p.desc {
                color: #9ca3af;
            }

            .features {
                background-color: #2d2d2d;
            }

            .features h2 {
                color: #9ca3af;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon-container">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
            </svg>
        </div>

        <h1>{{ __('Anda Sedang Luring') }}</h1>
        <p class="desc">{{ __('Koneksi internet terputus. Jangan khawatir, Anda masih bisa menggunakan beberapa fitur penting desa yang telah disimpan di perangkat Anda.') }}</p>

        <button onclick="window.location.reload()" class="btn">{{ __('Coba Muat Ulang') }}</button>

        <div class="features">
            <h2>{{ __('Tersedia Tanpa Internet:') }}</h2>
            <ul>
                <li>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('Tiket Masuk & QR Code Anda') }}
                </li>
                <li>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('Peta Dasar Desa Penglipuran') }}
                </li>
                <li>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('Buku Saku (Teks Sejarah)') }}
                </li>
            </ul>
        </div>
    </div>
</body>

</html>