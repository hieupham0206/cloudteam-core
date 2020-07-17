<?php

namespace Cloudteam\Core\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

/**
 * Class CrudTestCommand
 * php artisan crud:test BrandTest --crud=brand --model=Brand
 * @package Cloudteam\Core\Console\Commands
 */
class CrudTestCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:test
                            {name : The name of the Test.}
                            {--crud= : Tên của table trong database.}
                            {--model= : The name of the Model.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Custom Test Command';

    protected $type = 'Test';

    /**
     * Build the model class with the given name.
     *
     * @param  string $name
     *
     * @return string
     * @throws FileNotFoundException
     */
    public function buildClass($name)
    {
        $stub             = $this->files->get($this->getStub());
        $crudName         = strtolower($this->option('crud'));
        $crudNameSingular = Str::singular($this->option('crud'));
        $this->modelName  = $this->option('model');
        $viewName         = $this->option('crud');

        return $this
            ->replaceViewName($stub, $viewName)
            ->replaceCrudName($stub, $crudName)
            ->replaceCrudNameSingular($stub, $crudNameSingular)
            ->replaceModelName($stub, $this->modelName)
            ->replaceClass($stub, $name);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../stubs/test.stub';
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return base_path() . '/Tests/Feature/' . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Replace the viewName fo the given stub.
     *
     * @param string $stub
     * @param string $viewName
     *
     * @return $this
     */
    protected function replaceViewName(&$stub, $viewName)
    {
        $stub = str_replace('{{viewName}}', $viewName, $stub);

        return $this;
    }

    /**
     * Replace the crudName for the given stub.
     *
     * @param  string $stub
     * @param  string $crudName
     *
     * @return $this
     */
    protected function replaceCrudName(&$stub, $crudName)
    {
        $stub = str_replace('{{crudName}}', $crudName, $stub);

        return $this;
    }

    /**
     * Replace the crudNameSingular for the given stub.
     *
     * @param  string $stub
     * @param  string $crudNameSingular
     *
     * @return $this
     */
    protected function replaceCrudNameSingular(&$stub, $crudNameSingular)
    {
        $stub = str_replace('{{crudNameSingular}}', $crudNameSingular, $stub);

        return $this;
    }

    /**
     * Replace the modelName for the given stub.
     *
     * @param  string $stub
     * @param  string $modelName
     *
     * @return $this
     */
    protected function replaceModelName(&$stub, $modelName)
    {
        $stub = str_replace('{{modelName}}', $modelName, $stub);

        return $this;
    }
}
