<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Home - Usuário Comum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            overflow: hidden;
        }

        .transfer-button {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }

        .transfer-button:hover {
            background-color: #45a049;
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

        #recipient,
        #amount {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 16px;
            box-sizing: border-box;
            position: relative;
        }

        .autocomplete-suggestions {
            border: 1px solid #ccc;
            max-height: 150px;
            overflow-y: auto;
            position: absolute;
            width: calc(100% - 24px);
            background-color: white;
            z-index: 9999;
            top: calc(100% + 10px);
            left: 12px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .autocomplete-suggestions div {
            padding: 10px;
            cursor: pointer;
            font-size: 14px;
        }

        .autocomplete-suggestions div:hover {
            background-color: #f0f0f0;
        }

        .success-message {
            background-color: #d4edda;
            color: #45a049;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }

        .loading-spinner {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #4CAF50;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div id="loadingSpinner" class="loading-spinner" style="display: none;"></div>

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="sign-out-icon">
            <i class="fas fa-sign-out-alt"></i>
        </button>
    </form>
    <div class="card">
        @if (session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="error-message">
            <p style="color: red;">{{ session('error') }}</p>
        </div>
        @endif

        @if ($errors->any())
        <div class="error-message">
            <ul style="list-style: none; padding-left: 0; margin: 0;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <h2>Bem-vindo, {{ $user->name }}</h2>

        <p id="balance">Saldo: R$ {{ number_format($user->balance / 100, 2, ',', '.') }}</p>
        <button class="transfer-button" id="transferButton">Efetuar Transferência</button>
    </div>

    <div id="transferModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Efetuar Transferência</h3>

            <form id="transferForm" action="{{ route('transfer.store') }}" method="POST">
                @csrf
                <label for="recipient">Para:</label>
                <input type="text" id="recipient" name="recipient" autocomplete="off">
                <div id="suggestions" class="autocomplete-suggestions"></div>
                <input type="hidden" id="recipient_id" name="recipient_id">

                <label for="amount">Valor a enviar:</label>
                <input type="text" id="amount" name="amount" placeholder="R$ 0.00" autocomplete="off">

                <button type="submit" class="transfer-button">Confirmar Transferência</button>
            </form>
        </div>
    </div>

    <script>
        var modal = document.getElementById("transferModal");
        var btn = document.getElementById("transferButton");
        var span = document.getElementsByClassName("close")[0];

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        $('#recipient').on('input', function() {
            var query = $(this).val();
            if (query.length >= 2) {
                $.get('/autocomplete-users', {
                    query: query
                }, function(data) {
                    $('#suggestions').empty();
                    data.forEach(function(user) {
                        $('#suggestions').append('<div data-id="' + user.id + '">' + user.name + '</div>');
                    });

                    $('.autocomplete-suggestions div').on('click', function() {
                        $('#recipient').val($(this).text());
                        $('#recipient_id').val($(this).data('id'));
                        $('#suggestions').empty();
                    });
                });
            } else {
                $('#suggestions').empty();
            }
        });

        $('#transferForm').on('submit', function(e) {
            e.preventDefault();

            $('#loadingSpinner').show();

            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                data: $(this).serialize(),
                success: function(response) {
                    $('#loadingSpinner').hide();
                    modal.style.display = "none";

                    $('#recipient').val('');
                    $('#recipient_id').val('');
                    $('#amount').val('');

                    showMessage('success', response.message);

                    const formattedBalance = new Intl.NumberFormat('pt-BR', {
                        style: 'currency',
                        currency: 'BRL',
                        minimumFractionDigits: 2
                    }).format(response.new_balance / 100); // Divide by 100 to convert to reais

                    $('#balance').text('Saldo: ' + formattedBalance);
                },
                error: function(xhr) {
                    $('#loadingSpinner').hide();
                    modal.style.display = "none";

                    var message = 'Erro ao realizar a transferência.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    showMessage('error', message);
                }
            });
        });

        function showMessage(type, message) {
            var messageBox = $('<div>').addClass(type === 'success' ? 'success-message' : 'error-message');
            messageBox.text(message);
            $('.card').prepend(messageBox);

            setTimeout(function() {
                messageBox.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    </script>
</body>

</html>