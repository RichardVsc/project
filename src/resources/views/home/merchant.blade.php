<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Home - Lojista</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background-color: white;
            padding: 20px;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
            position: relative;
        }

        .sign-out-icon {
            background-color: transparent;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            width: 10%;
            color: #f44336;
            cursor: pointer;
        }

        .sign-out-icon:hover {
            color: #e53935;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="sign-out-icon">
            <i class="fas fa-sign-out-alt"></i>
        </button>
    </form>
    <div class="card">

        @if ($errors->any())
        <div class="error-message">
            <ul style="list-style: none; padding-left: 0; margin: 0;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <h2>Bem-vindo, {{ $user->name }} (Lojista)</h2>
        <p>Saldo: R$ {{ number_format($user->balance / 100, 2, ',', '.') }}</p>
        <p>Lojistas não podem efetuar transferências.</p>
    </div>
</body>

</html>