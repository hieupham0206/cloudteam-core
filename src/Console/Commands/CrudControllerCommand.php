<?php

namespace Cloudteam\Core\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * Class CrudControllerCommand
 *
 * php artisan crud:controller BrandsController --crud=brands --model=Brand --namespace=Business --validations=name#required
 *
 * @package Cloudteam\Core\Console\Commands
 */
class CrudControllerCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:controller
                            {name : The name of the controller.}
                            {--crud= : Tên của table trong database.}
                            {--model= : The name of the Model.}
                            {--namespace= : Tên namespace của controller.}
                            {--validations= : Khai báo field validation trong controller. (Default: --validations=name#required).}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Custom Controller Command';

    protected $type = 'Controller';

    protected $modelName = '';

    protected $namespace = 'App\\Http\\Controllers\\Modules\\Backend\\';

    protected $crudName = '';

    /**
     * Build the model class with the given name.
     *
     * @param string $name
     *
     * @return string
     * @throws FileNotFoundException
     */
    public function buildClass($name)
    {
        $stub            = $this->files->get($this->getStub());
        $this->crudName  = $this->option('crud');
        $this->modelName = $this->option('model');
        $viewName        = Str::plural($this->crudName);
        $routeName       = Str::plural($this->crudName);

        //optional
        $namespace = $this->option('namespace');

        $controllerNamespace = $this->namespace;

        if ($namespace !== '') {
            $controllerNamespace .= $namespace;
            $viewName            = 'backend.modules.' . strtolower($namespace) . ".$viewName";
        }

        $validations = rtrim($this->option('validations'), ';');

        $validations = trim($validations);
        if ($validations == '') {
            $validations = 'name#required;';
        }
        $validationRules = '$this->validate($request, [';
        $rules           = explode(';', $validations);
        foreach ($rules as $v) {
            if (trim($v) == '') {
                continue;
            }
            // extract field name and args
            $parts           = explode('#', $v);
            $fieldName       = trim($parts[0]);
            $rules           = trim($parts[1]);
            $validationRules .= "\n\t\t\t'$fieldName' => '$rules',";
        }
        $validationRules = substr($validationRules, 0, -1); // lose the last comma
        $validationRules .= "\n\t\t]);";

        $baseController = '';
        if ($namespace !== '') {
            $baseController = 'use App\Http\Controllers\Controller;';
        }

        $tableNamespace = "use App\Tables\\" . $this->modelName . 'Table;';
        if ($namespace !== '') {
            $tableNamespace = "use App\Tables\\" . $namespace . "\\" . $this->modelName . 'Table;';
        }

        return $this
            ->replaceBaseController($stub, $baseController)
            ->replaceTableNamespace($stub, $tableNamespace)
            ->replaceNamespace($stub, $controllerNamespace)
            ->replaceViewName($stub, $viewName)
            ->replaceRouteName($stub, $routeName)
            ->replaceCrudName($stub, $this->crudName)
            ->replaceCrudNameSingular($stub, Str::singular(Str::studly($this->crudName)))
            ->replaceTableNameSingular($stub, Str::singular($this->crudName))
            ->replaceModelName($stub, $this->modelName)
            ->replaceValidationRules($stub, $validationRules)
            ->replaceClass($stub, $name);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../stubs/controller.stub';
    }

    protected function getPath($name)
    {
        $namespace = $this->option('namespace');
        $name      = Str::replaceFirst($this->rootNamespace(), '', $name);

        $controllerNamespace = str_replace('\\', '/', '\\' . $this->namespace);

        $controllerNamespace .= $namespace !== '' ? "//$namespace/" : '//';

        $file = new Filesystem();
        if ( ! is_dir(__DIR__ . $controllerNamespace)) {
            $file->makeDirectory(__DIR__ . $controllerNamespace, 0777, true);
        }

        return base_path() . $controllerNamespace . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Replace the viewName fo the given stub.
     *
     * @param string $stub
     * @param string $viewName
     *
     * @return $this
     */
    protected function replaceViewName(&$stub, $viewName): self
    {
        $stub = str_replace('{{viewName}}', $viewName, $stub);

        return $this;
    }

    /**
     * Replace the viewName fo the given stub.
     *
     * @param string $stub
     * @param string $viewName
     *
     * @return $this
     */
    protected function replaceRouteName(&$stub, $viewName): self
    {
        $stub = str_replace('{{routeName}}', $viewName, $stub);

        return $this;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace('DummyNamespace', $name, $stub);

        return $this;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceTableNamespace(&$stub, $name): self
    {
        $stub = str_replace('TableNamespace', $name, $stub);

        return $this;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceBaseController(&$stub, $name): self
    {
        $stub = str_replace('BaseController', $name, $stub);

        return $this;
    }

    /**
     * Replace the viewPath for the given stub.
     *
     * @param string $stub
     * @param string $viewPath
     *
     * @return $this
     */
    protected function replaceViewPath(&$stub, $viewPath): self
    {
        $stub = str_replace('{{viewPath}}', $viewPath, $stub);

        return $this;
    }

    /**
     * Replace the crudName for the given stub.
     *
     * @param string $stub
     * @param string $crudName
     *
     * @return $this
     */
    protected function replaceCrudName(&$stub, $crudName): self
    {
        $stub = str_replace('{{crudName}}', lcfirst(Str::studly($crudName)), $stub);

        return $this;
    }

    /**
     * Replace the crudNameSingular for the given stub.
     *
     * @param string $stub
     * @param string $crudNameSingular
     *
     * @return $this
     */
    protected function replaceCrudNameSingular(&$stub, $crudNameSingular): self
    {
        $stub = str_replace('{{crudNameSingular}}', lcfirst($crudNameSingular), $stub);

        return $this;
    }

    /**
     * Replace the crudNameSingular for the given stub.
     *
     * @param string $stub
     * @param string $crudNameSingular
     *
     * @return $this
     */
    protected function replaceTableNameSingular(&$stub, $crudNameSingular): self
    {
        $stub = str_replace('{{tableNameSingular}}', lcfirst($crudNameSingular), $stub);

        return $this;
    }

    /**
     * Replace the modelName for the given stub.
     *
     * @param string $stub
     * @param string $modelName
     *
     * @return $this
     */
    protected function replaceModelName(&$stub, $modelName): self
    {
        $stub = str_replace('{{modelName}}', $modelName, $stub);

        return $this;
    }

    /**
     * Replace the validationRules for the given stub.
     *
     * @param string $stub
     * @param string $validationRules
     *
     * @return $this
     */
    protected function replaceValidationRules(&$stub, $validationRules): self
    {
        $stub = str_replace('{{validationRules}}', $validationRules, $stub);

        return $this;
    }
}
