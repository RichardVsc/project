# Projeto de Plataforma de Pagamentos Simplificada

Este projeto é uma plataforma de pagamentos simplificada, desenvolvida em Laravel, que permite transferências seguras entre usuários e lojistas. Conta com um sistema robusto de validações, controle de concorrência via Redis e envio de notificações.

## 🧑‍💻 Tecnologias Utilizadas
- **Laravel 12**
- **PHP-FPM 8.2**
- **Redis para Lock de Transações e Fila de Notificações**
- **Docker** & **Docker Compose**

## 🏗️ Arquitetura do Sistema

![Arquitetura](docs/images/architecture.png)

---

## 🔧 Componentes Principais

1. **Request:** A requisição chega na aplicação através da rota configurada.
2. **Logs Middleware**: Cria os logs de requisição e resposta.
2. **Redis Lock Middleware**: Garante que apenas uma transação por vez seja realizada para evitar concorrência.
3. **TransferController**: Recebe a requisição, valida os dados iniciais e chama o TransferOrchestrator para gerenciar o fluxo da transferência.
4. **TransferOrchestrator**: Orquestra o processo de transferência, coordenando as interações entre os diferentes serviços, como validação de saldo, autorização e execução da transferência.
5. **TransferService**: Contém a lógica de negócio para realizar a transferência de forma coordenada, interagindo com os serviços necessários.
   - **BalanceValidator**: Realiza a validação do saldo do usuário pagador para garantir que a transferência possa ser realizada.
   - **RecipientResolver**: Resolve o destinatário da transferência, garantindo que o destinatário exista e está válido para a operação.
   - **TransferProcessor**: Realiza a execução do processo de transferência entre o pagador e o destinatário, atualizando os saldos e registrando a transação.
   - **AuthorizationService**: Verifica a autorização para a transferência, interagindo com serviços externos para garantir que a transação seja permitida.
   - **TransferRepository**: Interage com o banco de dados para recuperar informações sobre usuários, transferências e armazenar as transações.
   - **NotificationService**: Envia notificações para os usuários ou lojistas sobre transferências bem-sucedidas.
6. **Database**: Armazena os dados de usuários, transferências e outras informações necessárias.
7. **External Notification API**: Utilizada para notificar os usuários ou lojistas sobre transferências realizadas.

---

## 🚀 Primeiros Passos

### ✅ Pré-requisitos

Certifique-se de que você tem o seguinte instalado:

- **Docker**
- **Docker Compose**
- **Composer** (opcional)

### 🛠️ Instalação

1. Clone este repositório e entre na pasta do projeto:
```bash
git clone https://github.com/RichardVsc/project.git && cd project
```

2. Suba os containers com Docker:
```bash
docker-compose up -d
```

3. Acesse o container:
```bash
docker exec -it project bash
```

4. Instale as dependências PHP via Composer:
```bash
composer install
```

5. Copie o arquivo `.env.example` para `.env`:

```bash
cp .env.example .env
```

6. Atualize as configurações do banco de dados dentro do seu arquivo `.env` para usar o PostgreSQL definido no `docker-compose.yml`

```bash 
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=laravel_db
DB_USERNAME=laravel
DB_PASSWORD=secret
```

7. Gere a chave da aplicação:
```bash
php artisan key:generate
```

8. Execute as migrations e os seeders:
```bash
php artisan migrate
php artisan db:seed --class=UserSeeder
```
9. Acesse a aplicação no navegador:
```bash
http://localhost:8080
```

10. Para verificar os usuários criados via seeder, use o Tinker:
```bash
php artisan tinker
App\Models\User::all();
```

## 📚 Endpoints Disponíveis

| Método | Endpoint          | Descrição                          |
|--------|-------------------|-------------------------------------|
| POST   | /api/transfer     | Realiza uma transferência          |

> 🔐 Todos os endpoints são protegidos via token Bearer (Sanctum).
---

## 🧪 Testes e Análise de Código

### 📬 Testando a Rota de Transferência com cURL
Você pode testar a rota de transferência da API usando ferramentas como cURL ou Postman. Essa rota é útil para simular transferências entre usuários sem a necessidade de sessão ou CSRF, ideal para testes manuais.

1. Gerar um token de autenticação
   - Dentro do container docker, execute:
      ```bash
         php artisan tinker
      ```
   - Dentro do tinker:
      ```bash
         $user = App\Models\User::find(1); // ID de um usuário válido
         $token = $user->createToken('TestToken')->plainTextToken;
      ```
      Guarde esse token para usar nas requisições.

2. Fazer a requisição com cURL
   ```bash
      curl -X POST http://localhost:8080/api/transfer \
      -H "Authorization: Bearer SEU_TOKEN_AQUI" \
      -H "Accept: application/json" \
      -d "recipient_id=2" \
      -d "amount=50.00"
   ```
      Substitua SEU_TOKEN_AQUI pelo token gerado e recipient_id por um ID de usuário válido.

   Respostas esperadas:
   - Sucesso
   ```json
      {
         "status": "success",
         "message": "Transferência bem sucedida!",
         "new_balance": 9500
      }     
   ```
   - Não autorizado
   ```json
      {
         "status": "error",
         "message": "Transação não autorizada pelo serviço externo."
      }  
   ```
   - Usuário do tipo lojista não pode transferir
   ```json
      {
         "status": "error",
         "message": "Lojistas não podem realizar transferências."
      }  
   ```
   - Saldo insuficiente
   ```json
      {
         "status": "error",
         "message": "Saldo insuficiente."
      }  
   ```
   - Destinatário não encontrado
   ```json
      {
         "status": "error",
         "message": "Destinatário da transação não encontrado."
      }  
   ```
   - Serviço de autorização indisponível
   ```json
      {
         "status": "error",
         "message": "Erro ao consultar serviço autorizador."
      }  
   ```
   - Erro interno durante a transferência
   ```json
      {
         "status": "error",
         "message": "Erro ao processar a transferência."
      }  
   ```


### 📤 Rodando os Testes
Para rodar todos os testes automatizados:
```bash
composer test
```

### Análise Estática de Código
Executa todas as ferramentas de análise de uma vez:
```bash
composer analyze
```

Ou utilize individualmente:
- PHPCS Fixer (formatação):
```bash
composer check
```

- PHPStan (análise estática):
```bash
composer phpstan
```

- PHPMD (más práticas):
```bash
composer phpmd
```

### Correção Automática
Corrigir automaticamente os problemas de formatação:
```bash
composer fix
```

## 💡 Dicas
- Se estiver com dúvidas sobre os comandos disponíveis, veja a aba "scripts" no arquivo composer.json.

- A pasta vendor/ e o arquivo composer.lock não devem ser editados manualmente.

- Sempre que adicionar novas dependências, lembre-se de rodar os testes e as ferramentas de análise.
