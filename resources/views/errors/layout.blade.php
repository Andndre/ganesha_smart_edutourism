<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') - Penglipuran Smart Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #FAF9F6;
            color: #191A19;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            box-sizing: border-box;
        }

        .card {
            max-width: 26rem;
            width: 100%;
            text-align: center;
        }

        .code {
            font-size: 4rem;
            font-weight: 800;
            color: #1E5128;
            line-height: 1;
        }

        h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: .75rem 0 .5rem;
        }

        p {
            color: #6b6b6b;
            font-size: .9rem;
            line-height: 1.5;
            margin: 0 0 1.5rem;
        }

        a.btn {
            display: inline-block;
            background: #1E5128;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: .75rem 1.75rem;
            border-radius: .75rem;
            font-size: .875rem;
        }

        a.btn:active {
            transform: scale(.97);
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a class="btn" href="{{ url('/') }}">Kembali ke Beranda</a>
    </div>
</body>

</html>
