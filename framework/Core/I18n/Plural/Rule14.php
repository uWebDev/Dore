<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule14
 *
 * @package Dore\Core\I18n\Plural
 */
class Rule14
{
    /**
     * Valid fol locales: ar
     *
     * @param int $number
     * @return int
     */
    public static function getPosition($number)
    {
        return $number == 0
            ? 0
            : self::checkSingle($number);
    }

    private static function checkSingle($number)
    {
        return $number == 1
            ? 1
            : self::checkDouble($number);
    }

    private static function checkDouble($number)
    {
        return $number == 2
            ? 2
            : self::checkMultiple($number);
    }

    private static function checkMultiple($number)
    {
        return $number % 100 >= 3 && $number % 100 <= 10
            ? 3
            : self::checkOther($number);
    }

    private static function checkOther($number)
    {
        return $number % 100 >= 11 && $number % 100 <= 99
            ? 4
            : 5;
    }
}
