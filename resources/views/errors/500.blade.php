<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Error - {{ config('app.name', 'CoreS') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'JetBrains Mono', monospace;
            background: #050507;
            color: #fff;
            display: flex; align-items: center; justify-content: center;
        }
        .container { text-align: center; padding: 2rem; }
        .code { font-size: 8rem; font-weight: 900; background: linear-gradient(135deg,rgb(34,211,238),rgb(168,85,247)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1; }
        .line { width: 60px; height: 2px; background: rgba(34,211,238,0.3); margin: 1.5rem auto; border-radius: 2px; }
        .msg { font-size: 0.95rem; color: rgba(255,255,255,0.5); margin-bottom: 2rem; }
        a {
            display: inline-block; padding: 0.75rem 1.5rem; font-size: 0.8rem;
            font-weight: 600; text-decoration: none; color: #000;
            background: rgb(34,211,238); border-radius: 12px;
            transition: all 0.2s;
        }
        a:hover { background: rgb(34,211,238,0.85); box-shadow: 0 0 25px -5px rgba(34,211,238,0.4); }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">500</div>
        <div class="line"></div>
        <p class="msg">Something went wrong on our end. Please try again later.</p>
        <a href="{{ url('/') }}">Back to Home</a>
    </div>
</body>
</html>
