<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class crud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criar os arquivos do crud';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        // Perguntar o nome do CRUD
        $crudName = $this->ask('Qual o nome do CRUD?');

        // Definindo os estilos
        $this->output->getFormatter()->setStyle('info', new \Symfony\Component\Console\Formatter\OutputFormatterStyle('white', 'blue', ['bold']));
        $this->output->getFormatter()->setStyle('bold', new \Symfony\Component\Console\Formatter\OutputFormatterStyle('white', 'default', ['bold']));

        $crudName_lowercase = strtolower($crudName);
        $crudName_ucfirst = ucfirst($crudName);
        $crudName_pascalcase = Str::studly($crudName);
        $crudName_singular_pascalcase = Str::studly(Str::singular($crudName));
        $crudName_snake = Str::snake($crudName);
        $crudName_plural = Str::plural($crudName);
        $crudName_plural_snake = Str::snake(Str::plural($crudName));
        $crudName_pascalsnake = Str::studly(Str::plural($crudName));

        // Criar Controller com resource
        $this->call('make:controller', [
            'name' => "{$crudName_singular_pascalcase}Controller",
            '--resource' => true,
        ]);
        $controllerPath = app_path("Http/Controllers/{$crudName_singular_pascalcase}Controller.php");
        file_put_contents($controllerPath, $this->getValorController($crudName_singular_pascalcase, $crudName_lowercase));


        // Criar Serviço
        $servicePath = app_path("Services/{$crudName_singular_pascalcase}Service.php");
        $this->line("  <info> INFO </info> Service <bold>[{$servicePath}]</bold> created successfully.");

        $directory = dirname($servicePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($servicePath, "<?php\n\nnamespace App\Services;\n\nuse App\Models\\" . $crudName_singular_pascalcase . ";\nuse Illuminate\Database\Eloquent\Model;\nuse App\Services\AbstractBaseService;\n\nclass {$crudName_singular_pascalcase}Service extends AbstractBaseService\n{\n    protected \$model;\n\n    public function setModel(Model \$model): void {\n        if (!\$model instanceof {$crudName_singular_pascalcase}) {\n            throw new \InvalidArgumentException(\"Modelo esperado App\\\Models\\\\" . $crudName_singular_pascalcase . "\");\n        }\n        \$this->model = \$model;\n    }\n\n    public function getModel(): Model {\n        return \$this->model;\n    }\n}\n");
        // Criar Model
        $this->call('make:model', [
            'name' => $crudName_singular_pascalcase
        ]);

        // Criar Migration
        $tableName = $crudName_plural_snake;
        $this->call('make:migration', [
            'name' => "create_{$crudName_plural_snake}_table",
            '--create' => $crudName_plural_snake,
        ]);

        // Criar views
        $viewsPath = resource_path("views/" . strtolower($crudName_snake));
        if (!file_exists($viewsPath)) {
            mkdir($viewsPath, 0777, true);
            $this->line("  <info> INFO </info> Views folder <bold>[{$viewsPath}]</bold> created successfully.");
        }

        file_put_contents("{$viewsPath}/index.blade.php", "@extends('layouts.app')\n\n@section('content')\n    <h1>{$crudName} Index</h1>\n@endsection");
        $this->line("  <info> INFO </info> View <bold>[{$viewsPath}/index.blade.php]</bold> created successfully.");
        file_put_contents("{$viewsPath}/add-edit.blade.php", "@extends('layouts.app')\n\n@section('content')\n    <h1>Add/Edit {$crudName}</h1>\n@endsection");


        $this->line("  <info> INFO </info> View <bold>[{$viewsPath}/add-edit.blade.php]</bold> created successfully.");
    }

    private function getValorController($crudNameSingularPascalCase, $crudNameLowercase)
    {
        $newContent = <<<CONTENT
<?php

namespace App\Http\Controllers;

use App\Services\{$crudNameSingularPascalCase}Service;
use Illuminate\Http\Request;

class {$crudNameSingularPascalCase}Controller extends Controller
{
    protected \${$crudNameLowercase}Service;

    public function __construct({$crudNameSingularPascalCase}Service \${$crudNameLowercase}Service)
    {
        \$this->{$crudNameLowercase}Service = \${$crudNameLowercase}Service;
    }

    public function index()
    {
        \${$crudNameLowercase}s = \$this->{$crudNameLowercase}Service->all();
        return response()->json(\${$crudNameLowercase}s);
    }

    public function show(\$id)
    {
        \${$crudNameLowercase} = \$this->{$crudNameLowercase}Service->find(\$id);
        return response()->json(\${$crudNameLowercase});
    }

    public function store(Request \$request)
    {
        \${$crudNameLowercase} = \$this->{$crudNameLowercase}Service->create(\$request->all());
        return response()->json(\${$crudNameLowercase}, 201);
    }

    public function update(Request \$request, \$id)
    {
        \$success = \$this->{$crudNameLowercase}Service->update(\$request->all(), \$id);
        return response()->json(['success' => \$success]);
    }

    public function destroy(\$id)
    {
        \$success = \$this->{$crudNameLowercase}Service->delete(\$id);
        return response()->json(['success' => \$success]);
    }
}
CONTENT;
        return $newContent;
    }
}
