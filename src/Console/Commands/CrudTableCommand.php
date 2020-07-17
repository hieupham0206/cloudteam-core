<?php

namespace Cloudteam\Core\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

/**
 * Class CrudControllerCommand
 *
 * php artisan crud:table BrandTable --crud=brands --model=Brand --namespace=Business --fields=name;email
 *
 * @package Cloudteam\Core\Console\Commands
 */
class CrudTableCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:table
                            {name : The name of the table.}
                            {--crud= : Tên của table trong database.}
                            {--model= : The name of the Model.}
                            {--namespace= : The table namespace.}
                            {--fields= : Tên các column để hiện trong view.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Custom Table Command';

    protected $type = 'Table';

    protected $modelName = '';

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
        $stub               = $this->files->get($this->getStub());
        $crudName           = strtolower($this->option('crud'));
        $crudNameSingular   = lcfirst(Str::studly(Str::singular($this->option('crud'))));
        $this->modelName    = $this->option('model');
        $namespace          = $this->option('namespace');
        $tableNamespace     = "App\Tables";
        $dataTableNamespace = '';
        if ($namespace) {
            $tableNamespace     = "App\Tables\\" . $namespace;
            $dataTableNamespace = "use Cloudteam\Core\Tables\DataTable;";
        }

        $tableValue  = $tableSort = '';
        $fields      = $this->option('fields');
        $fieldsArray = explode(';', $fields);
        foreach ($fieldsArray as $key => $fieldOptions) {
            $items       = explode('#', $fieldOptions);
            $columnName  = $items[0];
            $placeholder = '$' . $crudNameSingular . '->';

            $tableValue .= $placeholder . $columnName . ',' . "\n";
            if (strpos($columnName, '_id') !== false) {
                $relationName = Str::camel(str_replace('_id', '', $columnName));
                $tableValue   .= "optional({$placeholder}{$relationName})" . '->name,' . "\n";
            }

            $tableSort .= "'$crudName.{$columnName}', ";
        }

        return $this
            ->replaceTableNamespace($stub, $tableNamespace, $dataTableNamespace)
            ->replaceCrudName($stub, $crudName)
            ->replacePermissionName($stub, Str::singular($crudName))
            ->replaceCrudNameSingular($stub, $crudNameSingular)
            ->replaceModelName($stub, $this->modelName)
            ->replaceTableValue($stub, $tableValue)
            ->replaceTableSort($stub, $tableSort)
            ->replaceClass($stub, $name);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../stubs/table.stub';
    }

    protected function getPath($name)
    {
        $name      = Str::replaceFirst($this->rootNamespace(), '', $name);
        $namespace = $this->option('namespace');
        $tablePath = '/App/Tables/';
        if ($namespace) {
            $tablePath = "/App/Tables/{$namespace}/";
        }

        return base_path() . $tablePath . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * @param string $stub
     * @param string $name
     *
     * @param $dataTableNamespace
     *
     * @return $this|GeneratorCommand
     */
    protected function replaceTableNamespace(&$stub, $name, $dataTableNamespace)
    {
        $stub = str_replace(['DummyNamespace', 'TableNamespace'], [$name, $dataTableNamespace], $stub);

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
        $stub = str_replace('{{crudName}}', $crudName, $stub);

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
    protected function replacePermissionName(&$stub, $crudName): self
    {
        $stub = str_replace('{{permissionName}}', $crudName, $stub);

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
        $stub = str_replace('{{crudNameSingular}}', $crudNameSingular, $stub);

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
     * Replace the modelName for the given stub.
     *
     * @param string $stub
     * @param string $tableValue
     *
     * @return $this
     */
    protected function replaceTableValue(&$stub, $tableValue): self
    {
        $stub = str_replace('{{tableValue}}', $tableValue, $stub);

        return $this;
    }

    /**
     * Replace the modelName for the given stub.
     *
     * @param string $stub
     * @param string $tableValue
     *
     * @return $this
     */
    protected function replaceTableSort(&$stub, $tableValue): self
    {
        $stub = str_replace('{{tableSort}}', $tableValue, $stub);

        return $this;
    }
}
