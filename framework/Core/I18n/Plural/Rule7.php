<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule7
 *
 * @package Dore\Core\I18n\Plural
 */
class Rule7
{
    /**
     * Valid fol locales: sl
     *
     * @param int $number
     * @return int
     */
    public static function getPosition($number)
    {
        return $number % 100 == 1
            ? 0
            : self::checkMultiple($number);
    }

    private static function checkMultiple($number)
    {
        return $number % 100 == 2
            ? 1
            : $number % 100 == 3 || $number % 100 == 4
                ? 2
                : 3;
    }
}
