<?php

namespace Cloudteam\Core\Enums;

final class VATRate extends BaseEnum
{
    public const NO_TAX = -1;
    public const TAX_0 = 0;
    public const TAX_5 = 5;
    public const TAX_10 = 10;
    public const TAX_8 = 8;

    public static function getDescription($value, $fromApi = false): string
    {
        //note: nếu $value là null => Không thuế
        if (is_null($value) || $value === 'null') {
            return __('No VAT');//Không thuế (hóa đơn bán hàng)
        }

        $value = intval($value);
        if ($value === self::NO_TAX) {
            if ($fromApi) {
                return 'KCT';
            }

            return __('Non-taxable');//Không chịu thuế
        }
        if ((int) $value === self::TAX_0) {
            return __('0%');
        }
        if ((int) $value === self::TAX_5) {
            return __('5%');
        }
        if ((int) $value === self::TAX_10) {
            return __('10%');
        }
        if ((int) $value === self::TAX_8) {
            return __('8%');
        }

        return parent::getDescription($value);
    }

    /**
     * Truyền vào giá trị Enum để lấy giá trị tính số tiền sau thuế
     *
     * @param $value : Giá trị VATRate Enum
     * @return float|int
     */
    public static function getTaxValue($value): float|int
    {
        if ($value == self::TAX_5) {
            return 1.05;
        }
        if ($value == self::TAX_10) {
            return 1.1;
        }
        if ($value == self::TAX_8) {
            return 1.08;
        }

        return 1;
    }

    /**
     * Truyền vào thuế suất để lấy giá trị Enum
     *
     * @param int|string|null $key : Thuế suất [0,5,8,10]
     */
    public static function getValue(int|string|null $key): int|string|null
    {
        if (is_null($key) || $key === '' || $key === 'null') {
            return null;
        }
        $key = str_replace('%', '', $key);
        if ($key == 5) {
            return self::TAX_5;
        }
        if ($key == 10) {
            return self::TAX_10;
        }
        if ($key == 8) {
            return self::TAX_8;
        }
        if ($key == 0) {
            return self::TAX_0;
        }
        if ($key == 'KCT') {
            return self::NO_TAX;
        }

        return $key;
    }
}
