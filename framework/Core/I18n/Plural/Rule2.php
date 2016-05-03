<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule2
 * @package Dore\Core\I18n\Plural
 */
class Rule2
{
    /**
     * Valid fol locales: am, bh, fil, fr, gun, hi, ln, mg, nso, xbr, ti, wa
     *
     * @param int $number
     * @return int
     */
    public static function getPosition($number)
    {
        return $number == 0 || $number == 1
            ? 0
            : 1;
    }
}
