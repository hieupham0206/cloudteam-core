<?php

namespace Cloudteam\Core\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeModelMethodCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:method';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model method class';

    protected $type = 'Method Trait';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../stubs/method.stub';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name') . 'Method');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Models\Traits\Methods';
    }
}
