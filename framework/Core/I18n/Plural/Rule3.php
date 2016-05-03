<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule3
 * @package Dore\Core\I18n\Plural
 */
class Rule3
{
    /**
     * Valid fol locales: be, bs, hr, ru, sr, uk
     *
     * @param int $number
     * @return int
     */
    public static function getPosition($number)
    {
        return $number % 10 == 1 && $number % 100 != 11
            ? 0
            : self::checkMultiple($number);
    }

    private static function checkMultiple($number)
    {
        return self::expression($number)
            ? 1
            : 2;
    }

    private static function expression($number)
    {
        return $number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 10 || $number % 100 >= 20);
    }
}
