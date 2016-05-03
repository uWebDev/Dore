<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule10
 *
 * @package Dore\Core\I18n\Plural
 */
class Rule10
{
    /**
     * Valid fol locales: lv
     *
     * @param int $number
     * @return int
     */
    public static function getPosition($number)
    {
        return $number == 0
            ? 0
            : $number % 10 == 1 && $number % 100 != 11
                ? 1
                : 2;
    }
}
