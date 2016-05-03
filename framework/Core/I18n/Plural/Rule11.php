<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule11
 *
 * @package Dore\Core\I18n\Plural
 */
class Rule11
{
    /**
     * Valid fol locales: pl
     *
     * @param int $number
     * @return int
     */
    public static function getPosition($number)
    {
        return $number == 1
            ? 0
            : self::checkMultiple($number);
    }

    private static function checkMultiple($number)
    {
        return self::checkCondition($number) ? 1 : 2;
    }

    private static function checkCondition($number)
    {
        return $number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 12 || $number % 100 > 14);
    }
}
