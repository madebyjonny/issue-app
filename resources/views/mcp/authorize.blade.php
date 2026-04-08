<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Authorize Application - {{ config('app.name', 'Issues') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            -webkit-font-smoothing: antialiased;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .card-header {
            padding: 2rem 1.5rem 1rem;
            text-align: center;
        }

        .icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #eff6ff;
            margin-bottom: 1rem;
        }

        .icon-wrap svg {
            width: 28px;
            height: 28px;
            color: #3b82f6;
        }

        .card-header h1 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .card-header p {
            font-size: 0.875rem;
            color: #64748b;
            line-height: 1.5;
        }

        .card-body {
            padding: 0 1.5rem 1.5rem;
        }

        .user-info {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.875rem 1rem;
            margin-bottom: 1rem;
        }

        .user-info .label {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-bottom: 0.25rem;
        }

        .user-info .email {
            font-size: 0.875rem;
            font-weight: 500;
            color: #1e293b;
        }

        .scopes {
            margin-bottom: 1rem;
        }

        .scopes-title {
            font-size: 0.8125rem;
            font-weight: 500;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .scopes ul {
            list-style: none;
        }

        .scopes li {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.8125rem;
            color: #64748b;
            padding: 0.25rem 0;
        }

        .scopes li::before {
            content: '';
            display: block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #3b82f6;
            margin-top: 0.375rem;
            flex-shrink: 0;
        }

        .actions {
            display: flex;
            gap: 0.75rem;
            padding: 0 1.5rem 1.5rem;
        }

        .actions form {
            flex: 1;
        }

        button {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            height: 40px;
            padding: 0 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background 0.15s, box-shadow 0.15s;
            font-family: inherit;
        }

        .btn-cancel {
            background: #ffffff;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-cancel:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn-cancel svg {
            width: 16px;
            height: 16px;
            color: #94a3b8;
        }

        .btn-authorize {
            background: #3b82f6;
            color: #ffffff;
        }

        .btn-authorize:hover {
            background: #2563eb;
        }

        .btn-authorize:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            animation: spin 0.6s linear infinite;
        }

        .spinner.visible { display: block; }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <div class="icon-wrap">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h1>Authorize {{ $client->name }}</h1>
            <p>This application is requesting access to your account.</p>
        </div>

        <div class="card-body">
            <div class="user-info">
                <div class="label">Logged in as</div>
                <div class="email">{{ $user->email }}</div>
            </div>

            @if(count($scopes) > 0)
                <div class="scopes">
                    <div class="scopes-title">Permissions</div>
                    <ul>
                        @foreach($scopes as $scope)
                            <li>{{ $scope->description }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="actions">
            <form method="POST" action="{{ route('passport.authorizations.deny') }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="state" value="">
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="btn-cancel">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </button>
            </form>

            <form method="POST" action="{{ route('passport.authorizations.approve') }}" id="authorizeForm">
                @csrf
                <input type="hidden" name="state" value="">
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="btn-authorize" id="authorizeButton">
                    <span id="authorizeText">Authorize</span>
                    <svg id="loadingSpinner" class="spinner" fill="none" viewBox="0 0 24 24">
                        <circle opacity="0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path opacity="0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('authorizeForm');
            var button = document.getElementById('authorizeButton');
            var text = document.getElementById('authorizeText');
            var spinner = document.getElementById('loadingSpinner');

            form.addEventListener('submit', function() {
                button.disabled = true;
                text.textContent = 'Authorizing\u2026';
                spinner.classList.add('visible');

                setTimeout(function() {
                    var check = setInterval(function() {
                        if (!window.location.href.includes('/oauth/authorize') ||
                            window.location.search.includes('code=') ||
                            window.location.search.includes('error=')) {
                            clearInterval(check);
                            window.close();
                        }
                    }, 100);

                    setTimeout(function() {
                        clearInterval(check);
                        window.close();
                    }, 5000);
                }, 200);
            });
        });
    </script>
</body>
</html>
