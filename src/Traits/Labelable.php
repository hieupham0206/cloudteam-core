<?php

namespace Cloudteam\Core\Traits;

use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use function get_class;

trait Labelable
{
    /**
     * @param string $field
     * @param bool $capitalize
     *
     * @return null|string
     */
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

    /**
     * @param bool $lcfirst
     *
     * @return null|string
     * @throws ReflectionException
     */
    public function classLabel($lcfirst = false)
    {
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
    }

    /**
     * @param string $text
     * @param string $context
     * @param string $size
     *
     * @return string
     */
    public function contextLabel($text, $context = 'success', $size = 'sm'): string
    {
        return '<span class="font-weight-bold kt-font-' . $context . ' kt-badge--' . $size . '">' . $text . '</span>';
    }

    /**
     * @param string $text
     * @param string $context
     * @param string $size
     *
     * @return string
     */
    public function contextBadge($text, $context = 'success', $size = 'sm'): string
    {
        return '<span class="font-weight-bold kt-badge kt-badge--inline kt-badge--rounded kt-badge--' . $context . ' kt-badge--' . $size . '">' . $text . '</span>';
    }

    /**
     * @param $text
     * @param string $context
     * @param string $size
     *
     * @return string
     */
    public function contextBadgeUnified($text, $context = 'success', $size = 'sm'): string
    {
        return '<span class="font-weight-bold kt-badge kt-badge--inline kt-badge--rounded kt-badge--unified-' . $context . ' kt-badge--' . $size . '">' . $text . '</span>';
    }

    /**
     * @return string
     */
    public function getModelDisplayTextAttribute()
    {
        $displayAttribute = $this->displayAttribute;

        return $this->{$displayAttribute};
    }

    /**
     * @return string|null
     */
    public function getModelTitleAttribute()
    {
        $displayText = $this->model_display_text;

        try {
            $displayText = $displayText ?: $this->classLabel(true);
        } catch (\ReflectionException $e) {
            $displayText = '';
        }

        return $displayText;
    }
}
