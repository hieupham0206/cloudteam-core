<?php

namespace Cloudteam\Core\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeModelAttributeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:attribute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model attribute class';

    protected $type = 'Attribute trait';
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../stubs/attribute.stub';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name') . 'Attribute');
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
        return $rootNamespace . '\Models\Traits\Attributes';
    }
}
