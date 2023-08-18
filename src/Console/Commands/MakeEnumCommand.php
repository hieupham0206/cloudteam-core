<?php

namespace Cloudteam\Core\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeEnumCommand extends GeneratorCommand
{
    protected $signature = 'make:enum
						{name : Tên file enum}
						{--option= : Option của enum (VD: COMPANY,1;BRANCH,2)}
						{--force=}';

    protected $description = 'Tạo file enum';

    protected $type = 'Enum';

    protected function buildClass($name)
    {
        $option      = $this->option('option');//COMPANY,1;BRANCH,2
        $constOption = $constDescription = '';
        if ($option) {
            $options = explode(';', $option);

            foreach ($options as $option) {
                [$option, $value] = explode(',', $option);

                $optionDesc = ucfirst(camel2words($option));

                $constOption      .= "public const $option = $value;" . "\n";
                $constDescription .= "if (self::$option === ".'(int) $value'.") {
										return __('{$optionDesc}');
									}" . "\n";
            }
        }
        $stub = $this->files->get($this->getStub());

        return $this->replaceConstDescription($stub, $constDescription)
                    ->replaceConstOption($stub, $constOption)
                    ->replaceClass($stub, $name);
    }

    protected function getStub()
    {
        return __DIR__ . '/../stubs/enum.stub';
    }

    protected function replaceNamespace(&$stub, $name)
    {
        return $this;
    }

    protected function replaceConstOption(&$stub, $desc): self
    {
        $stub = str_replace('{$constOption}', $desc, $stub);

        return $this;
    }

    protected function replaceConstDescription(&$stub, $desc): self
    {
        $stub = str_replace('{$constDescription}', $desc, $stub);

        return $this;
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
        return $rootNamespace . '\Enums';
    }
}
