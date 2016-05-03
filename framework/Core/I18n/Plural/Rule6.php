<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule6
 *
 * @package Dore\Core\I18n\Plural
 */
class Rule6
{
    /**
     * Valid fol locales: lt
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
        return $number % 10 >= 2 && ($number % 100 < 10 || $number % 100 >= 20)
            ? 1
            : 2;
    }
}
