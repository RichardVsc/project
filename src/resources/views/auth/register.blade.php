<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuário</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
        }

        select,
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }

        button:hover {
            background-color: #45a049;
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
    <div class="form-container">
        <h2>Registro de Usuário</h2>

        @if ($errors->any())
        <div class="error-message">
            <ul style="list-style: none; padding-left: 0; margin: 0;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ url('register') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="user_type">Tipo de Usuário</label>
                <select id="user_type" name="user_type" required>
                    <option value="common" selected>Comum</option>
                    <option value="merchant">Lojista</option>
                </select>
            </div>

            <div class="form-group" id="cpf-cnpj-group" style="display: none;">
                <label for="cpf" id="cpf-label" style="display: none;">CPF</label>
                <input type="text" id="cpf" name="cpf" placeholder="Digite seu CPF" style="display: none;">

                <label for="cnpj" id="cnpj-label" style="display: none;">CNPJ</label>
                <input type="text" id="cnpj" name="cnpj" placeholder="Digite seu CNPJ" style="display: none;">
            </div>


            <div class="form-group">
                <label for="name">Nome Completo</label>
                <input type="text" id="name" name="name" placeholder="Digite seu nome completo" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="Digite sua senha" required>
                @error('password')
                <div style="color: #721c24; font-size: 12px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmação de Senha</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirme sua senha" required>
                @error('password_confirmation')
                <div style="color: #721c24; font-size: 12px;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit">Registrar</button>
        </form>
    </div>

    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            const userType = this.value;
            const cpfCnpjGroup = document.getElementById('cpf-cnpj-group');
            const cpfInput = document.getElementById('cpf');
            const cnpjInput = document.getElementById('cnpj');
            const cpfLabel = document.getElementById('cpf-label');
            const cnpjLabel = document.getElementById('cnpj-label');

            cpfInput.value = '';
            cnpjInput.value = '';

            cpfInput.removeAttribute('required');
            cnpjInput.removeAttribute('required');

            if (userType === 'merchant') {
                cpfCnpjGroup.style.display = 'block';
                cpfLabel.style.display = 'none';
                cnpjLabel.style.display = 'block';
                cpfInput.style.display = 'none';
                cnpjInput.style.display = 'block';
                cnpjInput.placeholder = 'Digite seu CNPJ';
                cnpjInput.setAttribute('required', 'required');
                cpfInput.removeAttribute('required');
            } else if (userType === 'common') {
                cpfCnpjGroup.style.display = 'block';
                cpfLabel.style.display = 'block';
                cnpjLabel.style.display = 'none';
                cpfInput.style.display = 'block';
                cnpjInput.style.display = 'none';
                cpfInput.placeholder = 'Digite seu CPF';
                cpfInput.setAttribute('required', 'required');
                cnpjInput.removeAttribute('required');
            } else {
                cpfCnpjGroup.style.display = 'none';
                cpfInput.removeAttribute('required');
                cnpjInput.removeAttribute('required');
            }
        });

        window.onload = function() {
            document.getElementById('user_type').dispatchEvent(new Event('change'));
        };
    </script>
</body>

</html>