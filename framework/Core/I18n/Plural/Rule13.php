<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule13
 *
 * @package Dore\Core\I18n\Plural
 */
class Rule13
{
    /**
     * Valid fol locales: ro
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
        return $number == 0 || ($number % 100 > 0 && $number % 100 < 20)
            ? 1
            : 2;
    }
}
