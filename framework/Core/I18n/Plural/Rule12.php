<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule12
 *
 * @package Dore\Core\I18n\Plural
 */
class Rule12
{
    /**
     * Valid fol locales: cy
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
        return $number == 2
            ? 1
            : $number == 8 || $number == 11
                ? 2
                : 3;
    }
}
