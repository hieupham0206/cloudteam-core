<?php

namespace Cloudteam\Core\Traits;

use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use function get_class;

trait Labelable
{
    public function label($field = '', $capitalize = false)
    {
        $modelName       = $this->table_name_singular;
        $translateKey    = "{$modelName}.{$field}";
        $labelFromModule = __($translateKey);

        if ($labelFromModule === $translateKey) {
            $field = ucfirst(camel2words(strtolower($field)));
            $label = __($field);
            if ($capitalize) {
                $label = __(Str::title(camel2words(strtolower($field))));
            }

            if (is_array($label)) {
                return $field;
            }

            return $label;
        }

        if (is_array($labelFromModule)) {
            return $field;
        }

        return $labelFromModule;
    }

    public function classLabel($lcfirst = false)
    {
        try {
            $reflect = new ReflectionClass($this);

            if (property_exists(get_class($this), 'logName')) {
                $nameInModel = $reflect->getStaticPropertyValue('logName');
                $tableName   = __($nameInModel);

                if (is_array($tableName)) {
                    $tableName = $nameInModel;
                }

                return $lcfirst ? mb_strtolower($tableName) : $tableName;
            }

            return __(ucfirst(camel2words(Str::studly($reflect->getShortName()))));
        } catch (ReflectionException $e) {
            return '';
        }
    }

    public function solidLabel($text, $context = 'success', $size = 'sm'): string
    {
        return '<span class="font-weight-bold label label-inline label-rounded label-' . $context . ' label-' . $size . '">' . $text . '</span>';
    }

    public function outlineLabel($text, $context = 'success', $size = 'sm'): string
    {
        return '<span class="font-weight-bold label label-inline label-rounded label-outline-' . $context . ' label-' . $size . '">' . $text . '</span>';
    }

    public function lightLabel($text, $context = 'success', $size = 'sm'): string
    {
        return '<span class="font-weight-bold label label-inline label-rounded label-light-' . $context . ' label-' . $size . '">' . $text . '</span>';
    }

    public function getModelDisplayTextAttribute(): string
    {
        $displayAttribute = $this->displayAttribute;

        return $this->{$displayAttribute};
    }

    public function getModelTitleAttribute(): ?string
    {
        $displayText = $this->model_display_text;

        return $displayText ?: $this->classLabel(true);
    }
}
