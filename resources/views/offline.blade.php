<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - Penglipuran Smart Edutourism</title>
    <meta name="theme-color" content="#1E5128">
    @vite(['resources/css/app.css'])
    <style>
        body {
            background-color: #FAF9F6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .dark body {
            background-color: #0D2818;
        }

        .container {
            text-align: center;
            padding: 2rem;
            max-width: 400px;
        }

        .icon {
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
            color: #6b7280;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #191A19;
        }

        .dark h1 {
            color: #E8E8E6;
        }

        p {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .dark p {
            color: #9ca3af;
        }

        .btn {
            display: inline-block;
            background-color: #1E5128;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.15s;
        }

        .btn:hover {
            background-color: #152E1D;
        }

        .features {
            margin-top: 2rem;
            text-align: left;
            padding: 1rem;
            background-color: #f3f4f6;
            border-radius: 0.5rem;
        }

        .dark .features {
            background-color: #152E1D;
        }

        .features h2 {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #191A19;
        }

        .dark .features h2 {
            color: #E8E8E6;
        }

        .features ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .features li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .dark .features li {
            color: #9ca3af;
        }

        .features svg {
            width: 16px;
            height: 16px;
            color: #1E5128;
        }
    </style>
    <script>
        if (
            localStorage.getItem("theme") === "dark" ||
            (!("theme" in localStorage) &&
                window.matchMedia("(prefers-color-scheme: dark)").matches)
        ) {
            document.documentElement.classList.add("dark");
        }
    </script>
</head>

<body>
    <div class="container">
        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
        </svg>

        <h1>Anda Sedang Offline</h1>
        <p>Tidak ada koneksi internet. Beberapa fitur mungkin terbatas, namun Anda masih dapat mengakses peta dan data
            yang telah di-cache.</p>

        <a href="/" class="btn">Coba Lagi</a>

        <div class="features">
            <h2>Fitur Offline yang Tersedia:</h2>
            <ul>
                <li>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Peta lokasi budaya
                </li>
                <li>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Informasi object budaya
                </li>
                <li>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Halaman utama aplikasi
                </li>
            </ul>
        </div>
    </div>
</body>

</html>
