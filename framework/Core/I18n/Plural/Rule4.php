<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule4
 *
 * @package Dore\Core\I18n\Plural
 */
class Rule4
{
    /**
     * Valid fol locales: cs, sk
     *
     * @param int $number
     * @return int
     */
    public static function getPosition($number)
    {
        return $number == 1
            ? 0
            : $number >= 2 && $number <= 4
                ? 1
                : 2;
    }
}
