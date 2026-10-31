<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') · SiapKompeten</title>
    <style>
        :root { --line: #e3e8f0; --ink: #172033; --muted: #64748b; --brand: #2563eb; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center;
            padding: 24px; font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            color: var(--ink); background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 55%, #ecfeff 100%);
        }
        .auth-card {
            width: 100%; max-width: 420px; background: #fff; border: 1px solid var(--line);
            border-radius: 16px; padding: 30px; box-shadow: 0 18px 40px rgba(15, 23, 42, .08);
        }
        .auth-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .auth-mark {
            width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center;
            background: #dbeafe; color: #1d4ed8; font-weight: 800;
        }
        h1 { margin: 0; font-size: 22px; }
        .sub { margin: 4px 0 22px; color: var(--muted); font-size: 14px; }
        .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
        .field label { font-size: 13px; font-weight: 600; }
        .input {
            border: 1px solid var(--line); border-radius: 9px; padding: 11px 12px;
            font: inherit; width: 100%;
        }
        .row { display: flex; align-items: center; gap: 8px; margin: 4px 0 18px; font-size: 14px; color: var(--muted); }
        .btn {
            width: 100%; border: 0; border-radius: 9px; padding: 11px 14px; font: inherit;
            font-weight: 700; cursor: pointer; background: var(--brand); color: #fff;
        }
        .btn:hover { background: #1d4ed8; }
        .alert { border-radius: 9px; padding: 10px 12px; margin-bottom: 16px; font-size: 13px; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .foot { margin-top: 18px; text-align: center; font-size: 12px; color: var(--muted); }
    </style>
</head>
<body>
    <section class="auth-card">
        <div class="auth-brand">
            <div class="auth-mark">SK</div>
            <div>
                <strong>SiapKompeten</strong>
                <div style="font-size: 12px; color: #64748b;">Sistem Informasi LSP</div>
            </div>
        </div>

        <h1>@yield('heading', 'Masuk ke akun')</h1>
        <p class="sub">@yield('subheading', 'Gunakan akun yang terdaftar pada lembaga Anda.')</p>

        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')

        <p class="foot">SiapKompeten · Multi-LSP</p>
    </section>
</body>
</html>
