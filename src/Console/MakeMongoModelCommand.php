<?php

namespace Mongo\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeMongoModelCommand extends Command
{
    protected $signature = 'make:mongo-model {name} {--collection=}';
    protected $description = 'Cria um model que estende da Facade Mongo';

    public function handle()
    {
        $name = $this->argument('name');
        $className = ucfirst($name);
        $filePath = app_path("Models/{$className}.php");

        if (File::exists($filePath)) {
            $this->error("O model {$className} já existe!");
            return;
        }
        $className = $this->collectionName($className);
        $collection = $this->option('collection') ?? $className;
        $stub = <<<PHP
<?php

namespace App\Models;

use Mongo\Facades\Mongo;

class {$className} extends Mongo
{
    protected static string \$collection = '{$collection}';
}
PHP;

        File::ensureDirectoryExists(app_path('Models'));
        File::put($filePath, $stub);

        $this->info("Model {$className} criado com sucesso em app/Models!");
    }

    protected function collectionName(string $className): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }
}