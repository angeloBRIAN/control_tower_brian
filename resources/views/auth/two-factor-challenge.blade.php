<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor Authentication - Control Tower</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #003D43;
            --primary-teal: #00B8A9;
            --accent-gold: #FFD93D;
            --surface-dark: #1a2e30;
            --text-light: #e8f0f0;
        }
        body {
            background: linear-gradient(135deg, #1a1d21 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .challenge-card {
            background: linear-gradient(145deg, var(--surface-dark), #0d1f21);
            border: 1px solid rgba(0, 184, 169, 0.2);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5), 0 0 40px rgba(0, 184, 169, 0.1);
            max-width: 420px;
            width: 100%;
        }
        .shield-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-teal) 0%, #00a89a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 184, 169, 0.3);
        }
        .shield-icon i {
            font-size: 2.5rem;
            color: white;
        }
        h4 {
            color: var(--text-light);
            font-weight: 600;
        }
        .text-muted-custom {
            color: rgba(232, 240, 240, 0.6);
        }
        .code-input {
            background: rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(0, 184, 169, 0.3);
            color: var(--text-light);
            font-size: 2rem;
            text-align: center;
            letter-spacing: 0.5em;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .code-input:focus {
            background: rgba(0, 0, 0, 0.5);
            border-color: var(--primary-teal);
            box-shadow: 0 0 15px rgba(0, 184, 169, 0.3);
            outline: none;
            color: white;
        }
        .code-input::placeholder {
            color: rgba(232, 240, 240, 0.3);
        }
        .btn-verify {
            background: linear-gradient(135deg, var(--primary-teal) 0%, #00a89a 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .btn-verify:hover {
            background: linear-gradient(135deg, #00a89a 0%, var(--primary-teal) 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 184, 169, 0.4);
            color: white;
        }
        .btn-cancel {
            background: transparent;
            border: 1px solid rgba(232, 240, 240, 0.2);
            color: rgba(232, 240, 240, 0.6);
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(232, 240, 240, 0.4);
            color: var(--text-light);
        }
        .alert-danger {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b7a;
            border-radius: 10px;
        }
        small {
            color: rgba(232, 240, 240, 0.5);
        }
    </style>
</head>
<body>
    <div class="challenge-card">
        <div class="text-center mb-4">
            <div class="shield-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h4 class="mt-3">Two-Factor Authentication</h4>
            <p class="text-muted-custom">Enter the 6-digit code from your authenticator app</p>
        </div>
        
        @if(session('error'))
        <div class="alert alert-danger mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
        @endif
        
        <form action="{{ route('2fa.verify') }}" method="POST">
            @csrf
            <div class="mb-4">
                <input type="text" name="code" class="form-control code-input" 
                       maxlength="8" required autofocus autocomplete="one-time-code"
                       placeholder="• • • • • •">
                <small class="d-block text-center mt-2">
                    <i class="bi bi-key me-1"></i>Or enter a recovery code
                </small>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-verify btn-lg">
                    <i class="bi bi-unlock me-2"></i>Verify & Login
                </button>
                <a href="{{ route('logout') }}" class="btn btn-cancel"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-x-circle me-1"></i>Cancel Login
                </a>
            </div>
        </form>
        
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</body>
</html>
