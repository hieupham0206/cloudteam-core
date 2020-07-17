<?php
/**
 * Created by PhpStorm.
 * User: ADMIN
 * Date: 11/5/2018
 * Time: 1:43 PM
 */

namespace Cloudteam\Core\Traits;

use function get_class;
use Illuminate\Support\Str;

trait Enumerable
{
    private $enumAttribute;

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        if ($this->isEnumAttribute($key)) {
            $class = $this->getEnumClass($this->enumAttribute);

            if (strpos($key, '_name') !== false) {
                return $class::getDescription($this->{$this->enumAttribute});
            }

            return $class::toSelectArray();
        }

        return parent::getAttributeValue($key);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        if ($this->isEnumAttribute($key)) {
            return $this->getAttributeValue($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Returns whether the attribute was marked as enum
     *
     * @param $key
     *
     * @return bool
     */
    protected function isEnumAttribute($key)
    {
        if ($this->isEnumPropertyExist() && $this->enums) {
            $filtered   = collect($this->enums)->filter(static function ($enum, $enumAttribute) use ($key) {
                return $key === Str::plural($enumAttribute) || $key === "{$enumAttribute}_name";
            });
            $isNotEmpty = $filtered->isNotEmpty();

            if ($isNotEmpty) {
                $this->enumAttribute = $filtered->keys()->first();
            }

            return $isNotEmpty;
        }

        return false;
    }

    /**
     * Returns the enum class. Supports 'FQCN\Class@method()' notation
     *
     * @param $key
     *
     * @return mixed
     */
    private function getEnumClass($key)
    {
        if (! $this->isEnumPropertyExist()) {
            return null;
        }

        $result = $this->enums[$key];
        if (strpos($result, '@')) {
            $class  = Str::before($result, '@');
            $method = Str::after($result, '@');
            // If no namespace was set, prepend the Model's namespace to the
            // class that resolves the enum class. Prevent this behavior,
            // by setting the resolver class with a leading backslash
            if (class_basename($class) === $class) {
                $class =
                    Str::replaceLast(
                        class_basename(get_class($this)),
                        $class,
                        self::class
                    );
            }
            $result = $class::$method();
        }

        return $result;
    }

    private function isEnumPropertyExist()
    {
        return property_exists(get_class($this), 'enums');
    }
}
