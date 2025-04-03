## Instalação 🚀 

1. Clone este repositório para o seu ambiente de desenvolvimento e entre na pasta do projeto.

   ```bash
   git clone https://github.com/RichardVsc/project.git && cd project

2. Execute o comando docker abaixo:
   ```bash
   docker-compose up -d

3. Após o container executar o build corretamente, é possível acessar a aplicação.

#### Projeto
http://localhost:8080/

4. Para acessar o projeto, é possivel a criação de usuários com o Seeder.

   1. Acesse o container `docker exec -it project bash`
   2. Rode as migrations `php artisan migrate`
   3. Rode o seeder `php artisan db:seed --class=UserSeeder`
   4. Para verificar os usuarios, pode se usar o tinker
         - `php artisan tinker`
         - `App\Models\User::all();`
