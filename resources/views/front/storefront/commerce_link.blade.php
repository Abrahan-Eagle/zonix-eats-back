<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $commerce->business_name }} — Zonix Eats</title>
    <meta name="description" content="Tu restaurante en Zonix Eats">
    <meta property="og:title" content="{{ $commerce->business_name }} — Zonix Eats">
    <meta property="og:description" content="Tu restaurante en Zonix Eats">
    <meta property="og:url" content="{{ $pageUrl }}">
    <meta property="og:type" content="website">
    <style>
        :root { --bg:#0f1923; --card:#fff; --text:#0f172a; --muted:#64748b; --accent:#3399ff; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: var(--bg); color: var(--text); min-height: 100vh;
            display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { background: var(--card); border-radius: 16px; padding: 28px 24px; max-width: 420px;
            width: 100%; box-shadow: 0 12px 40px rgba(0,0,0,.25); text-align: center; }
        h1 { font-size: 1.25rem; margin: 0 0 8px; line-height: 1.3; }
        p.sub { color: var(--muted); font-size: .95rem; margin: 0 0 20px; }
        a.btn { display: inline-block; background: var(--accent); color: #fff; text-decoration: none;
            padding: 14px 24px; border-radius: 999px; font-weight: 600; font-size: 1rem; }
        a.btn:hover { filter: brightness(1.05); }
        .hint { margin-top: 20px; font-size: .85rem; color: var(--muted); line-height: 1.5; }
        code { font-size: .75rem; word-break: break-all; color: var(--muted); }
    </style>
    <script>
        (function () {
            var deep = @json($deepLink);
            try { window.location.href = deep; } catch (e) {}
            setTimeout(function () {}, 800);
        })();
    </script>
</head>
<body>
    <div class="card">
        <h1>{{ $commerce->business_name }}</h1>
        <p class="sub">Tu restaurante en Zonix Eats</p>
        <p><a class="btn" href="{{ $deepLink }}">Abrir en la app</a></p>
        <p class="hint">Si no se abre sola, pulsa el botón. Necesitas tener Zonix Eats instalada.</p>
        <p class="hint"><code>{{ $pageUrl }}</code></p>
    </div>
</body>
</html>
