# Mongo Laravel

Uma biblioteca Laravel para integração simplificada com MongoDB, oferecendo uma interface fluente e intuitiva para operações com banco de dados MongoDB.

## 📋 Requisitos

- **PHP**: ^8.2
- **Laravel**: ^11.0 || ^12.0
- **Extensão MongoDB para PHP**: Obrigatória para funcionamento
- **Composer**: Para gerenciamento de dependências

## 🔧 Verificação e Instalação da Extensão MongoDB

Antes de usar esta biblioteca, você **deve** ter a extensão MongoDB instalada no PHP.

### Verificar se a extensão está instalada

```bash
php -m | grep mongodb
```

Se não retornar nada, a extensão não está instalada.

### Instalação da Extensão MongoDB

#### 🐧 Linux (Ubuntu/Debian)

```bash
# Instalar a extensão
sudo apt-get update
sudo apt-get install php-mongodb

# Ou via PECL
sudo pecl install mongodb

# Adicionar ao php.ini se necessário
echo "extension=mongodb" | sudo tee -a /etc/php/8.2/cli/php.ini
echo "extension=mongodb" | sudo tee -a /etc/php/8.2/fpm/php.ini

# Reiniciar serviços
sudo systemctl restart apache2  # ou nginx
sudo systemctl restart php8.2-fpm
```

#### 🍎 macOS

```bash
# Via Homebrew
brew install php@8.2
brew install mongodb/brew/mongodb-community

# Instalar extensão via PECL
pecl install mongodb

# Adicionar ao php.ini
echo "extension=mongodb" >> /usr/local/etc/php/8.2/php.ini

# Reiniciar serviços se necessário
brew services restart php@8.2
```

#### 🪟 Windows

1. Baixe a extensão MongoDB apropriada do [PECL](https://pecl.php.net/package/mongodb)
2. Extraia o arquivo `php_mongodb.dll` para a pasta `ext` do PHP
3. Adicione `extension=mongodb` ao arquivo `php.ini`
4. Reinicie o servidor web

## 📦 Instalação

### 1. Instalar via Composer

```bash
composer require techapi/mongo-laravel
```

### 2. Configurar Conexão com MongoDB

Adicione a configuração do MongoDB no arquivo `config/database.php`:

```php
'connections' => [
    // ... outras conexões

    'mongodb' => [
        'driver'   => 'mongodb',
        'host'     => env('MONGO_HOST', 'localhost'),
        'port'     => env('MONGO_PORT', 27017),
        'username' => env('MONGO_USER', ''),
        'password' => env('MONGO_PASS', ''),
        'options'  => [
            'database' => env('MONGO_DB', 'laravel'),
            'authSource' => env('MONGO_AUTH_SOURCE', 'admin'), // Opcional
        ],
    ],
],
```

### 3. Configurar Variáveis de Ambiente

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
MONGO_HOST=localhost
MONGO_PORT=27017
MONGO_USER=seu_usuario
MONGO_PASS=sua_senha
MONGO_DB=nome_do_banco
MONGO_AUTH_SOURCE=admin
```

### 4. Publicar Service Provider (Opcional)

O package possui auto-discovery habilitado, mas se precisar registrar manualmente, adicione em `config/app.php`:

```php
'providers' => [
    // ...
    Mongo\Providers\MongoServiceProvider::class,
],

'aliases' => [
    // ...
    'Mongo' => Mongo\Facades\Mongo::class,
],
```

## 🚀 Uso

### Criando um Model

Use o comando artisan para criar um novo model:

```bash
php artisan make:mongo-model User
```

Ou especificando o nome da collection:

```bash
php artisan make:mongo-model User --collection=usuarios
```

Isso criará um model em `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Mongo\Facades\Mongo;

class User extends Mongo
{
    protected static string $collection = 'users';
}
```

### Operações Básicas

#### Criar Documento

```php
use App\Models\User;

// Criar um novo usuário
$result = User::create([
    'name' => 'João Silva',
    'email' => 'joao@email.com',
    'age' => 30
]);
```

#### Criar ou Atualizar Documento

```php
// Criar ou atualizar baseado em um filtro
$result = User::createOrUpdate(
    ['name' => 'João Silva', 'age' => 31], // dados
    ['email' => 'joao@email.com']         // filtro
);
```

#### Buscar Um Documento

```php
// Buscar um usuário
$user = User::findOneData(['email' => 'joao@email.com'])->toArray();
```

#### Buscar com Paginação

```php
// Buscar usuários com paginação
$users = User::findAllWithPaginate(
    ['age' => ['$gte' => 18]], // filtro
    10,                        // limite por página
    1                          // página atual
);

// Retorna:
// [
//     'data' => [...],
//     'paginate' => [
//         'current_page' => 1,
//         'total' => 100,
//         'per_page' => 10,
//         'last_page' => 10
//     ]
// ]
```

### Métodos Disponíveis

| Método | Descrição | Parâmetros |
|--------|-----------|------------|
| `create()` | Cria um novo documento | `array $data` |
| `createOrUpdate()` | Cria ou atualiza documento | `array $data, array $filter` |
| `findOneData()` | Busca um documento | `array $filter` |
| `findAllWithPaginate()` | Busca com paginação | `array $filter = [], int $limit = 10, int $page = 1` |
| `toArray()` | Converte resultado para array | - |

## 📝 Exemplos Práticos

### Exemplo Completo de CRUD

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        
        $users = User::findAllWithPaginate([], $limit, $page);
        
        return response()->json($users);
    }
    
    public function store(Request $request)
    {
        $userData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'age' => 'integer|min:0'
        ]);
        
        $result = User::create($userData);
        
        return response()->json(['created' => $result > 0]);
    }
    
    public function show(string $email)
    {
        $user = User::findOneData(['email' => $email])->toArray();
        
        if (empty($user)) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }
        
        return response()->json($user);
    }
    
    public function update(Request $request, string $email)
    {
        $userData = $request->validate([
            'name' => 'string',
            'age' => 'integer|min:0'
        ]);
        
        $result = User::createOrUpdate($userData, ['email' => $email]);
        
        return response()->json(['updated' => $result > 0]);
    }
}
```

### Filtros Avançados

```php
// Buscar usuários com idade entre 18 e 65 anos
$adults = User::findAllWithPaginate([
    'age' => [
        '$gte' => 18,
        '$lte' => 65
    ]
]);

// Buscar usuários por múltiplos emails
$users = User::findAllWithPaginate([
    'email' => [
        '$in' => ['joao@email.com', 'maria@email.com']
    ]
]);

// Buscar usuários cujo nome contém "Silva"
$users = User::findAllWithPaginate([
    'name' => new \MongoDB\BSON\Regex('Silva', 'i')
]);
```

## 🔍 Troubleshooting

### Erro: "Class 'MongoDB\Driver\Manager' not found"

- **Causa**: Extensão MongoDB não instalada
- **Solução**: Siga as instruções de instalação da extensão acima

### Erro: "Connection refused"

- **Causa**: MongoDB não está rodando ou configuração incorreta
- **Solução**: 
  1. Verifique se o MongoDB está rodando: `mongosh`
  2. Verifique as configurações no `.env`
  3. Teste a conexão: `php artisan tinker` → `DB::connection('mongodb')->getMongoDB()`

### Erro: "Authentication failed"

- **Causa**: Credenciais incorretas
- **Solução**: 
  1. Verifique `MONGO_USER` e `MONGO_PASS` no `.env`
  2. Verifique `MONGO_AUTH_SOURCE` (geralmente 'admin')

## 🤝 Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🆘 Suporte

Para questões e suporte, abra uma [issue](https://github.com/techapi/mongo-laravel/issues) no GitHub.