<?php

namespace Dore\Core\I18n\Plural;

/**
 * Class Rule1
 * @package Dore\Core\I18n\Plural
 */
class Rule1
{
    /**
     * Valid fol locales: af, bn, bg, ca, da, de, el, en, eo, es, et, eu, fa, fi,
     *                    fo, fur, fy, gl, gu, ha, he, hu, is, it, ku, lb, ml, mn,
     *                    mr, nah, nb, ne, nl, nn, no, om, or, pa, pap, ps, pt,
     *                    so, sq, sv, sw, ta, te, tk, ur, zu
     *
     * @param int $number
     * @return int
     */
    public static function getPosition($number)
    {
        return $number == 1
            ? 0
            : 1;
    }
}
