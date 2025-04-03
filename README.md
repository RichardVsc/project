# Projeto de Plataforma de Pagamentos Simplificada

Este projeto é uma plataforma de pagamentos que permite transferências de dinheiro entre usuários e lojistas.  
Abaixo está a arquitetura geral do sistema:

## 🏗️ Arquitetura do Sistema

![Arquitetura](docs/images/architecture.png)

## 🔧 Componentes Principais

1. **Request:** A requisição chega na aplicação através da rota configurada.
2. **Redis Lock Middleware:** Garante que apenas uma transação por vez seja realizada para evitar concorrência.
3. **TransferController:** Recebe a requisição e valida os dados iniciais.
4. **TransferService:** Contém a lógica de negócio da transferência, realiza validações e utiliza serviços externos.
   - **AuthorizationService:** Realiza a verificação de autorização de transferência com um serviço externo.
   - **TransferRepository:** Responsável por interagir com o banco de dados.
   - **NotificationService:** Envia notificações sobre transferências bem-sucedidas. 
5. **Database:** Armazena os dados de transferências e usuários.
6. **External Notification API:** Utilizada para notificar os usuários ou lojistas sobre transferências realizadas.

---

## 🚀 Tecnologias Utilizadas
- **Laravel 12**
- **PHP-FPM 8.2**
- **Redis para Lock de Transações e Fila de Notificações**

## Instalação 💡

1. Clone este repositório para o seu ambiente de desenvolvimento e entre na pasta do projeto.

   ```bash
   git clone https://github.com/RichardVsc/project.git && cd project

2. Execute o comando docker abaixo:
   ```bash
   docker-compose up -d

3. Após o container executar o build corretamente, é possível acessar a aplicação.

#### Projeto
Pode ser acessado na URL: `http://localhost:8080`

4. Para acessar o projeto, é possivel a criação de usuários com o Seeder.

   1. Acesse o container `docker exec -it project bash`
   2. Rode as migrations `php artisan migrate`
   3. Rode o seeder `php artisan db:seed --class=UserSeeder`
   4. Para verificar os usuarios, pode se usar o tinker
         - `php artisan tinker`
         - `App\Models\User::all();`
