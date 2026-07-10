<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appName }}</title>
    @if ($logoUrl)
        <link rel="icon" href="{{ $logoUrl }}">
    @endif
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            color: #0f172a;
            padding: 1.5rem;
            line-height: 1.5;
        }

        .card {
            width: 100%;
            max-width: 24rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 2rem 1.75rem;
            text-align: center;
            box-shadow: 0 1px 3px rgb(15 23 42 / 6%);
        }

        .logo {
            max-height: 4rem;
            max-width: 100%;
            margin: 0 auto 1.25rem;
            object-fit: contain;
        }

        h1 {
            font-size: 1.375rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .company {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .info {
            margin: 1.5rem 0;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
            text-align: left;
            font-size: 0.8125rem;
            color: #475569;
        }

        .info p + p { margin-top: 0.25rem; }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 0.75rem 1.25rem;
            background: #d97706;
            color: #fff;
            font-size: 0.9375rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: background 0.15s ease;
        }

        .btn:hover { background: #b45309; }
    </style>
</head>
<body>
    <div class="card">
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="logo">
        @endif

        <h1>{{ $appName }}</h1>

        @if ($companyName)
            <p class="company">{{ $companyName }}</p>
        @endif

        @if ($address || $phone || $email)
            <div class="info">
                @if ($address)
                    <p>{{ $address }}</p>
                @endif
                @if ($phone)
                    <p>{{ $phone }}</p>
                @endif
                @if ($email)
                    <p>{{ $email }}</p>
                @endif
            </div>
        @endif

        <a href="{{ url('/admin/login') }}" class="btn">Masuk ke Aplikasi</a>
    </div>
</body>
</html>
