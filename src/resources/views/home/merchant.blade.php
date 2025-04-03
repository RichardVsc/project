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

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 400px;
            position: relative;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .statement-button {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }

        .statement-button:hover {
            background-color: #45a049;
        }

        .statement-list {
            max-height: 300px;
            overflow-y: auto;
            margin-top: 10px;
        }

        .statement-item {
            padding: 8px;
            margin-bottom: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .statement-item p {
            margin: 2px 0;
        }

        .statement-empty {
            color: #666;
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
        <button class="statement-button" id="statementButton">Visualizar Extrato</button>
    </div>

    <div id="statementModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Extrato</h3>
            <div id="statementList"></div>
        </div>
    </div>

    <script>
        const statementButton = document.getElementById('statementButton');
        const statementModal = document.getElementById('statementModal');
        const closeBtn = statementModal.querySelector('.close');

        statementButton.addEventListener('click', function() {
            statementModal.style.display = 'block';
            fetchStatement();
        });

        closeBtn.addEventListener('click', function() {
            statementModal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target === statementModal) {
                statementModal.style.display = 'none';
            }
        });

        function fetchStatement() {
            const statementList = document.getElementById('statementList');
            statementList.innerHTML = '<p>Carregando...</p>';

            fetch("{{ route('statement.index') }}")
                .then(response => response.json())
                .then(data => {
                    statementList.innerHTML = '';
                    if (data.data.length === 0) {
                        statementList.innerHTML = '<p>Nenhum extrato encontrado.</p>';
                        return;
                    }

                    data.data.forEach(item => {
                        const payer = item.payer_user ? item.payer_user.name : 'Desconhecido';
                        const payee = item.payee_user ? item.payee_user.name : 'Desconhecido';
                        const value = new Intl.NumberFormat('pt-BR', {
                            style: 'currency',
                            currency: 'BRL'
                        }).format(item.value / 100);
                        const date = new Date(item.created_at).toLocaleString('pt-BR');

                        const statementItem = `
                            <div class="statement-item">
                                <p><strong>De:</strong> ${payer}</p>
                                <p><strong>Para:</strong> ${payee}</p>
                                <p><strong>Valor:</strong> ${value}</p>
                                <p><strong>Data:</strong> ${date}</p>
                            </div>
                        `;
                        statementList.insertAdjacentHTML('beforeend', statementItem);
                    });
                })
                .catch(() => {
                    statementList.innerHTML = '<p>Erro ao carregar o extrato.</p>';
                });
        }
    </script>
</body>

</html>