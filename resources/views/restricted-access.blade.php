<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Acces restreint</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .card {
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 2.5rem;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .icon { font-size: 2.5rem; margin-bottom: 1rem; }
        h1 { font-size: 1.25rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem; }
        .message { color: #6b7280; font-size: 0.875rem; line-height: 1.5; margin-bottom: 1.5rem; }
        form { display: flex; flex-direction: column; gap: 0.75rem; }
        input[type="password"] {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.15s;
        }
        input[type="password"]:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
        button {
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.15s;
        }
        button:hover { background: #4338ca; }
        .error { color: #dc2626; font-size: 0.8125rem; margin-top: 0.25rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">&#128274;</div>
        <h1>Acces restreint</h1>

        @if($message)
            <p class="message">{{ $message }}</p>
        @else
            <p class="message">Ce site est actuellement en acces restreint. Veuillez saisir le mot de passe pour continuer.</p>
        @endif

        <form method="POST" action="{{ url('cms/restricted-access') }}">
            @csrf
            <input type="password" name="password" placeholder="Mot de passe" required autofocus>

            @if(isset($errors) && $errors->has('password'))
                <p class="error">{{ $errors->first('password') }}</p>
            @endif

            <button type="submit">Acceder au site</button>
        </form>
    </div>
</body>
</html>
