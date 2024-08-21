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

        // Criar Requests
        $this->call('crud:requests', [
            'name' => "$crudName_singular_pascalcase"
        ]);

        // Criar Controller com resource
        $this->call('make:controller', [
            'name' => "{$crudName_singular_pascalcase}Controller",
            '--resource' => true,
        ]);

        $controllerPath = app_path("Http/Controllers/{$crudName_singular_pascalcase}Controller.php");
        file_put_contents($controllerPath, $this->getValorController($crudName_singular_pascalcase, $crudName_lowercase, $crudName_snake));


        // Criar Serviço
        $servicePath = app_path("Services/{$crudName_singular_pascalcase}Service.php");
        $this->line("  <info> INFO </info> Service <bold>[{$servicePath}]</bold> created successfully.");

        $directory = dirname($servicePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($servicePath, $this->getValorService($crudName_singular_pascalcase, $crudName_lowercase));

        // $this->getValorService($crudName_singular_pascalcase, $crudName_lowercase);
        // file_put_contents($servicePath, "<?php\n\nnamespace App\Services;\n\nuse App\Models\\" . $crudName_singular_pascalcase . ";\nuse Illuminate\Database\Eloquent\Model;\nuse App\Services\AbstractBaseService;\n\nclass \{$crudName_singular_pascalcase}Service extends AbstractBaseService\n{\n    \nprotected Model \$model;\n\npublic function __construct(\$crudName_singular_pascalcase \$crudNameLowercase)\n{\n\$this->model = \$pais;\n}\n    public function setModel(Model \$model): void {\n        if (!\$model instanceof {$crudName_singular_pascalcase}) {\n            throw new \InvalidArgumentException(\"Modelo esperado App\\\Models\\\\" . $crudName_singular_pascalcase . "\");\n        }\n        \$this->model = \$model;\n    }\n\n    public function getModel(): Model {\n        return \$this->model;\n    }\n}\n");

        // Criar Model
        $this->call('make:model', [
            'name' => $crudName_singular_pascalcase
        ]);

        // Adicionar propriedades na Model
        $modelPath = app_path("Models/{$crudName_singular_pascalcase}.php");
        if (file_exists($modelPath)) {
            $modelContent = file_get_contents($modelPath);
            $modelContent = str_replace(
                "use HasFactory;",
                "use HasFactory;\n\n    protected \$table = '{$crudName_snake}';\n    protected \$fillable = [\n        'nome',\n        'ativo',\n    ];",
                $modelContent
            );
            file_put_contents($modelPath, $modelContent);
        }

        // Criar Migration
        $tableName = $crudName_plural_snake;
        $this->call('make:migration', [
            'name' => "create_{$crudName_plural_snake}_table",
            '--create' => $crudName_plural_snake,
        ]);

        $migrationPath = database_path('migrations');
        $migrationFile = collect(scandir($migrationPath))
            ->first(fn($file) => str_contains($file, "create_{$crudName_plural_snake}_table"));

        if ($migrationFile) {
            $migrationFilePath = $migrationPath . DIRECTORY_SEPARATOR . $migrationFile;
            $migrationContent = file_get_contents($migrationFilePath);
            $updatedMigrationContent = str_replace(
                '$table->id();',
                '$table->id();' . PHP_EOL . '            $table->string(\'nome\')->nullable();',
                $migrationContent
            );
            file_put_contents($migrationFilePath, $updatedMigrationContent);
            $this->line("  <info> INFO </info> Migration updated successfully.");
        } else {
            $this->error("Migration file not found.");
        }



        // Criar views
        $viewsPath = resource_path("views/" . strtolower($crudName_snake));
        if (!file_exists($viewsPath)) {
            mkdir($viewsPath, 0777, true);
            $this->line("  <info> INFO </info> Views folder <bold>[{$viewsPath}]</bold> created successfully.");
        }

        // Adicionar o conteúdo no index.blade.php
        file_put_contents("{$viewsPath}/index.blade.php", $this->getContentIndex($crudName_singular_pascalcase, $crudName_lowercase));
        $this->line("  <info> INFO </info> View <bold>[{$viewsPath}/index.blade.php]</bold> created successfully.");

        // Adicionar o conteúdo no add-edit.blade.php
        file_put_contents("{$viewsPath}/add-edit.blade.php", $this->getContentAddEdit($crudName_singular_pascalcase, $crudName_lowercase));
        $this->line("  <info> INFO </info> View <bold>[{$viewsPath}/add-edit.blade.php]</bold> created successfully.");


        // Adicionar o conteúdo das rotas ao final do arquivo web.php
        $webRoutesPath = base_path('routes/web.php');
        $routeDirectory = base_path('routes/grupos/');
        $routeFilePath = $routeDirectory . $crudName_snake . '.php';

        // Certifique-se de que o diretório de rotas existe
        if (!file_exists($routeDirectory)) {
            mkdir($routeDirectory, 0755, true);
        }

        // Criar o arquivo de rota individual
        file_put_contents($routeFilePath, $this->criarRotas($crudName_singular_pascalcase, $crudName_snake));

        // Adicionar referência ao novo arquivo de rota no web.php
        $routeIncludeStatement = "require base_path('routes/grupos/{$crudName_snake}.php');" . PHP_EOL;
        $webRoutesContent = file_get_contents($webRoutesPath);

        if (strpos($webRoutesContent, $routeIncludeStatement) === false) {
            $middlewareGroup = "\nRoute::middleware('web')
    ->group(function() {
        {$routeIncludeStatement}
});";

            // Adicionar o middleware group se não existir
            file_put_contents($webRoutesPath, PHP_EOL . $middlewareGroup, FILE_APPEND);
        }

        $this->line("  <info> INFO </info> Routes created successfully.");
    }


    private function getContentAddEdit($crudNameSingularPascalCase, $crudNameLowercase)
    {
        $newContent = <<<CONTENT
    @extends('adminlte::page')

    @section('title', \$titulo)

    @section('content_header')
        <x-breadcrumb :breadcrumbs="\$breadcrumb" />
        <x-page-title :title="\$titulo" />
    @stop

    @section('content')
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8 col-xs-12 card p-2">
            <div class="card-body">
                <x-form method="{{ \$metodo }}" action="{{ \$rota }}" rowClass="row">
                    <div class="col pl-0">
                        <div class="form-group">
                            <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                label="Nome" value="{{ \${$crudNameLowercase}->nome ?? '' }}" required />
                        </div>
                    </div>
                    <div class="col pr-0">
                        <div class="form-group">
                            <x-form-select name="ativo" label="Status" :options="\$status" placeholder="Selecione um valor"
                                :selected="isset(\${$crudNameLowercase}->ativo) && \${$crudNameLowercase}->ativo ? \${$crudNameLowercase}->ativo : null"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-xs col-12">
                        Salvar
                    </button>
                </x-form>
            </div>
        </div>
    @stop

    @section('js')
        <script></script>
    @stop
    CONTENT;
        return $newContent;
    }
    private function getContentIndex($crudNameSingularPascalCase, $crudNameLowercase)
    {
        $newContent = <<<CONTENT
@extends('adminlte::page')

@section('title', \$titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="\$breadcrumb" />
    <x-page-title :title="\$titulo" :createUrl="route('{$crudNameLowercase}.create')" />
@stop

@section('content')
    @if (isset(\$filters) && is_array(\$filters) && count(\$filters) > 0)
        <x-filter :additionalFilters="\$additionalFilters" :filters="\$filters" :itensFiltered="\$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ \${$crudNameLowercase}s->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('{$crudNameLowercase}.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="\$perPages" :selectedPerPage="\$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ \${$crudNameLowercase}s->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (\${$crudNameLowercase}s as \${$crudNameLowercase})
                        <tr>
                            <td>{{ \${$crudNameLowercase}->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ \${$crudNameLowercase}->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ \${$crudNameLowercase}->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('{$crudNameLowercase}.edit', \${$crudNameLowercase}->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('{$crudNameLowercase}.destroy', \${$crudNameLowercase}->id)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script></script>
@stop
CONTENT;
        return $newContent;
    }

    private function criarRotas($crudNameSingularPascalCase, $crudNameLowercase)
    {
        $newContent = <<<CONTENT
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\\{$crudNameSingularPascalCase}Controller;

Route::prefix('{$crudNameLowercase}')->name('{$crudNameLowercase}.')->group(function () {
    Route::post('setperpage', [{$crudNameSingularPascalCase}Controller::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [{$crudNameSingularPascalCase}Controller::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [{$crudNameSingularPascalCase}Controller::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [{$crudNameSingularPascalCase}Controller::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [{$crudNameSingularPascalCase}Controller::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('{$crudNameLowercase}', {$crudNameSingularPascalCase}Controller::class);
CONTENT;

        return $newContent;
    }
    private function getValorController($crudNameSingularPascalCase, $crudNameLowercase)
    {
        $newContent = <<<CONTENT
    <?php

    namespace App\Http\Controllers;

    use App\Http\Controllers\Interfaces\FiltroInterface;
    use App\Http\Controllers\Interfaces\ResourceInterface;
    use App\Http\Requests\\{$crudNameSingularPascalCase}\Store{$crudNameSingularPascalCase}Request;
    use App\Http\Requests\\{$crudNameSingularPascalCase}\Update{$crudNameSingularPascalCase}Request;
    use App\Services\BreadcrumbService;
    use App\Services\\{$crudNameSingularPascalCase}Service;
    use App\Traits\AtualizaFiltrosTrait;
    use Illuminate\Http\Request;
    use App\Traits\FiltroTrait;

    class {$crudNameSingularPascalCase}Controller extends BaseController implements FiltroInterface, ResourceInterface
    {
        use FiltroTrait;
        use AtualizaFiltrosTrait;
        protected \${$crudNameLowercase}Service;
        protected \$breadcrumbService;

        public function __construct({$crudNameSingularPascalCase}Service \${$crudNameLowercase}Service, BreadcrumbService \$breadcrumbService)
        {
            \$this->titulo = "{$crudNameSingularPascalCase}s";
            \$this->nomeFiltro = '{$crudNameLowercase}';
            \$this->{$crudNameLowercase}Service = \${$crudNameLowercase}Service;
            \$this->breadcrumbService = \$breadcrumbService;
        }

        public function index()
        {
            \$this->configurarSessao();

            \$itensFiltered = session("\$this->nomeFiltro.filtro", []);
            \$perPage = session()->get("\$this->nomeFiltro.perPage", 10);

            \${$crudNameLowercase}s = \$this->{$crudNameLowercase}Service->getAllWithFiltersAndPagination(\$itensFiltered, \$perPage);

            \$breadcrumb = \$this->breadcrumbService->prepararBreadcrumbIndex('{$crudNameSingularPascalCase}s');
            \$filters = \$this->obterFiltrosPrincipais();
            \$additionalFilters = \$this->obterFiltrosSecundarios();

            \$this->atualizarFiltros(\$itensFiltered, \$filters);
            \$this->atualizarFiltros(\$itensFiltered, \$additionalFilters);

            \$perPages = [5, 10, 15, 25, 50, 100];

            return view('{$crudNameLowercase}.index', compact(
                '{$crudNameLowercase}s',
                'breadcrumb',
                'filters',
                'itensFiltered',
                'additionalFilters',
                'perPages',
                'perPage'
            ))->with('titulo', \$this->titulo);
        }

        public function create(){
            \$status = [
                'S' => 'Ativo',
                'N' => 'Inativo'
            ];
            \$breadcrumb = \$this->breadcrumbService->prepararBreadcrumbCreate('{$crudNameSingularPascalCase}s','{$crudNameLowercase}.index','Criar');

            [\$metodo,\$rota] = \$this->getFormMethodAndRoute();
            return view('{$crudNameLowercase}.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', \$this->titulo);
        }

        public function store(Store{$crudNameSingularPascalCase}Request \$request)
        {
            \$this->{$crudNameLowercase}Service->create(\$request->all());

            return \$this->redirecionaRotaRedirect(\$this->nomeFiltro);
        }

        public function edit(\$id)
        {
            \${$crudNameLowercase} = \$this->{$crudNameLowercase}Service->getById(\$id);
            \$status = [
                'S' => 'Ativo',
                'N' => 'Inativo'
            ];
            \$breadcrumb = \$this->breadcrumbService->prepararBreadcrumbEdit('{$crudNameSingularPascalCase}s','{$crudNameLowercase}.index',"Editar: \${$crudNameLowercase}->nome");
            [\$metodo,\$rota] = \$this->getFormMethodAndRoute(\${$crudNameLowercase});

            return view('{$crudNameLowercase}.add-edit', compact('{$crudNameLowercase}','breadcrumb','status','metodo','rota'))->with('titulo', \$this->titulo);
        }

        public function update(Update{$crudNameSingularPascalCase}Request \$request, \$id)
        {
            \$success = \$this->{$crudNameLowercase}Service->update(\$id,\$request->all());
            return \$this->redirecionaRotaRedirect(\$this->nomeFiltro);
        }

        public function destroy(\$id)
        {
            \$success = \$this->{$crudNameLowercase}Service->delete(\$id);
            return \$this->redirecionaRotaRedirect(\$this->nomeFiltro);
        }

        public function filtro(Request \$request)
        {
            \$this->criarFiltros(\$request, \$this->nomeFiltro);
            return \$this->redirecionaRotaRedirect(\$this->nomeFiltro);
        }

        public function removerFiltro(Request \$request)
        {
            \$atributo = \$request->input('atributo') ?? null;
            \$this->removeSessao(\$this->nomeFiltro, \$atributo);
            return \$this->redirecionaRotaRedirect(\$this->nomeFiltro);
        }

        public function setPerPage(Request \$request)
        {
            // Salva o valor de paginação na sessão
            \$request->session()->put("\$this->nomeFiltro.perPage", \$request->input('perPage'));

            // Redireciona de volta para a página de listagem
            return \$this->redirecionaRotaRedirect(\$this->nomeFiltro);
        }

        public function configurarSessao()
        {
            session()->put("\$this->nomeFiltro.urlPadrao", '{$crudNameLowercase}');
            session()->put("\$this->nomeFiltro.rotaRedirect", '{$crudNameLowercase}.index');
        }

        public function obterFiltrosPrincipais()
        {
            return [
                ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
                ['name' => 'ativo', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                    ['value' => '', 'label' => 'Selecione o status', 'selected' => false],
                    ['value' => 'S', 'label' => 'Ativo', 'selected' => false],
                    ['value' => 'N', 'label' => 'Inativo', 'selected' => false]
                ]]
            ];
        }

        public function obterFiltrosSecundarios()
        {
            return [
                // Adicione filtros secundários aqui, se necessário
            ];
        }

        /**
         * Função para obter o método e a rota para o formulário.
         *
         * @param  {$crudNameSingularPascalCase}|null  \${$crudNameLowercase}
         * @return array
         */
        private function getFormMethodAndRoute(\${$crudNameLowercase} = null)
        {
            if (\${$crudNameLowercase}) {
                return [
                    'PUT',
                    route('{$crudNameLowercase}.update', \${$crudNameLowercase}->id),
                ];
            }

            return [
                'POST',
                route('{$crudNameLowercase}.store'),
            ];
        }
    }
    CONTENT;
        return $newContent;
    }
    private function getValorService($crudNameSingularPascalCase, $crudNameLowercase)
    {
        $newContent = <<<CONTENT
    <?php

    namespace App\Services;

    use App\Models\\{$crudNameSingularPascalCase};
    use Illuminate\Database\Eloquent\Model;
    use App\Services\AbstractBaseService;

    class {$crudNameSingularPascalCase}Service extends AbstractBaseService
    {
        protected Model \$model;

        public function __construct({$crudNameSingularPascalCase} \${$crudNameLowercase})
        {
            \$this->model = \${$crudNameLowercase};
        }

        public function setModel(Model \$model): void
        {
            if (!\$model instanceof {$crudNameSingularPascalCase}) {
                throw new \\InvalidArgumentException("Modelo esperado App\\\\Models\\\\{$crudNameSingularPascalCase}");
            }
            \$this->model = \$model;
        }

        public function getModel(): Model
        {
            return \$this->model;
        }
    }
    CONTENT;
        return $newContent;
    }
}
