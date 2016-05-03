<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule8
 *
 * @package Dore\Core\I18n\Plural
 */
class Rule8
{
    /**
     * Valid fol locales: mk
     *
     * @param int $number
     * @return int
     */
    public static function getPosition($number)
    {
        return $number % 10 == 1
            ? 0
            : 1;
    }
}
