<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule9
 *
 * @package Dore\Core\I18n\Plural
 */
class Rule9
{
    /**
     * Valid fol locales: mt
     *
     * @param int $number
     * @return int
     */
    public static function getPosition($number)
    {
        return $number == 1
            ? 0
            : self::checkSingle($number);
    }

    private static function checkSingle($number)
    {
        return $number == 0 || ($number % 100 > 1 && $number % 100 < 11)
            ? 1
            : self::checkMultiple($number);
    }

    private static function checkMultiple($number)
    {
        return $number % 100 > 10 && $number % 100 < 20
            ? 2
            : 3;
    }
}
