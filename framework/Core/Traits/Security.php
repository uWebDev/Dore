<?php

namespace Dore\Core\Traits;

/**
 * Class Security
 * @package Dore\Core\Traits
 */
trait Security
{

    /**
     * Filtering rows
     *
     * @param string $string
     *
     * @return string
     */
    public function checkin($string)
    {
        if (function_exists('iconv')) {
            $string = iconv("UTF-8", "UTF-8", $string);
        }
        // Удалить все невидимые символы с UTF-8 строки,  кроме символа новой строки
        // http://stackoverflow.com/questions/12543476/utf-8-string-remove-all-invisible-characters-except-newline
        $string = preg_replace('/[^\P{C}\n]+/u', '', $string);
        return trim($string);
    }
}
