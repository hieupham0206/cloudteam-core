<?php

namespace Cloudteam\Core\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeModelRelationshipCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:relationship';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model relationship class';

    protected $type = 'Relationship Trait';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../stubs/relationship.stub';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name') . 'Relationship');
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
        return $rootNamespace . '\Models\Traits\Relationships';
    }
}
