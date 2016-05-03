<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule5
 *
 * @package Dore\Core\I18n\Plural
 */
class Rule5
{
    /**
     * Valid fol locales: ga
     *
     * @param int $number
     * @return int
     */
    public static function getPosition($number)
    {
        return $number == 1
            ? 0
            : $number == 2
                ? 1
                : 2;
    }
}
