<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8f9fa;
        }
        .login-container {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: white;
        }
        #logo {
            display: block;
            max-width: 200px;
            height: auto;
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
    @if(!empty($settings?->logo))
        <img id="logo" src="{{ asset('storage/' . $settings->logo) }}" alt="Logo del Restaurante">
    @endif
        <h1 class="text-center mb-4">Iniciar Sesión</h1>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Recordarme</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
        </form>
        <div class="text-center mt-3">
            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
