<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SiapKompeten') · SiapKompeten</title>
    <style>
        :root {
            --bg: #f4f6fb;
            --surface: #ffffff;
            --line: #e3e8f0;
            --ink: #172033;
            --muted: #64748b;
            --brand: #2563eb;
            --brand-dark: #1d4ed8;
            --danger: #b91c1c;
            --success: #15803d;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--ink);
            line-height: 1.5;
        }
        a { color: var(--brand); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .app-header {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            padding: 12px 24px;
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }
        .brand { display: flex; align-items: center; gap: 12px; margin-right: auto; }
        .brand-logo {
            width: 38px; height: 38px; border-radius: 10px; object-fit: cover;
            background: #e0e7ff;
        }
        .brand-name { font-weight: 700; font-size: 15px; }
        .brand-sub { font-size: 12px; color: var(--muted); }
        .header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .select {
            border: 1px solid var(--line); border-radius: 8px; padding: 8px 10px;
            background: #fff; color: var(--ink); font: inherit; min-width: 210px;
        }
        .container { max-width: 1080px; margin: 0 auto; padding: 28px 20px 48px; }
        .page-title { margin: 0 0 4px; font-size: 24px; }
        .page-sub { margin: 0 0 22px; color: var(--muted); font-size: 14px; }
        .card {
            background: var(--surface); border: 1px solid var(--line); border-radius: 14px;
            padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }
        .card h2 { margin: 0 0 14px; font-size: 17px; }
        .grid { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field label { font-size: 13px; font-weight: 600; }
        .input {
            border: 1px solid var(--line); border-radius: 8px; padding: 10px 12px;
            font: inherit; width: 100%; background: #fff;
        }
        .btn {
            display: inline-flex; align-items: center; gap: 6px; border: 1px solid transparent;
            border-radius: 8px; padding: 9px 14px; font: inherit; font-weight: 600;
            cursor: pointer; background: var(--brand); color: #fff;
        }
        .btn:hover { background: var(--brand-dark); text-decoration: none; }
        .btn-secondary { background: #fff; color: var(--ink); border-color: var(--line); }
        .btn-secondary:hover { background: #f8fafc; }
        .btn-danger { background: #fff; color: var(--danger); border-color: #fecaca; }
        .btn-danger:hover { background: #fef2f2; }
        .btn-sm { padding: 6px 10px; font-size: 13px; }
        .alert { border-radius: 10px; padding: 12px 14px; margin-bottom: 18px; font-size: 14px; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { text-align: left; padding: 11px 10px; border-bottom: 1px solid var(--line); vertical-align: middle; }
        th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); }
        tr:last-child td { border-bottom: 0; }
        .muted { color: var(--muted); }
        .badge {
            display: inline-block; padding: 3px 9px; border-radius: 999px;
            background: #eff6ff; color: #1d4ed8; font-size: 12px; font-weight: 600;
        }
        .empty { text-align: center; padding: 28px 12px; color: var(--muted); }
        .meta { display: grid; gap: 8px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin: 0 0 18px; }
        .meta div { background: #f8fafc; border: 1px solid var(--line); border-radius: 10px; padding: 10px 12px; }
        .meta dt { font-size: 12px; color: var(--muted); margin-bottom: 2px; }
        .meta dd { margin: 0; font-weight: 600; overflow-wrap: anywhere; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        @media (max-width: 640px) {
            .app-header { padding: 12px 16px; }
            .container { padding: 20px 14px 40px; }
        }
    </style>
</head>
<body>
    <x-header />

    <main class="container">
        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                <strong>Periksa kembali isian Anda.</strong>
                <ul style="margin: 8px 0 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
