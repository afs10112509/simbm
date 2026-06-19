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
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: #e5e7eb;
            padding: 1.5rem;
        }
        .card {
            width: 100%;
            max-width: 28rem;
            background: rgba(17, 24, 39, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.35);
        }
        .logo {
            max-height: 5rem;
            max-width: 100%;
            margin: 0 auto 1rem;
            object-fit: contain;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fbbf24;
            margin-bottom: 0.25rem;
        }
        .company {
            font-size: 0.95rem;
            color: #9ca3af;
            margin-bottom: 1.5rem;
        }
        .info {
            text-align: left;
            font-size: 0.875rem;
            color: #d1d5db;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .info p + p { margin-top: 0.35rem; }
        .btn {
            display: inline-block;
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: #f59e0b;
            color: #111827;
            font-weight: 600;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: background 0.15s;
        }
        .btn:hover { background: #fbbf24; }
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
                    <p>Telp: {{ $phone }}</p>
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
