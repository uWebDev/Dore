<?php

namespace Dore\Core\Validator;

/**
 * Class Validator
 * @package Dore\Core\Validator
 */
class Validator extends MyViolin
{

    /**
     * Validator constructor.
     */
    public function __construct()
    {
        //Добавление сообщений правил оптом
        $this->addRuleMessages([
            'required' => _s('Field is not filled'),
            'int' => _s('Must be a number'),
            'email' => _s('Valid email address'),
            'min' => _s('Must be a minimum {$0}'),
            'max' => _s('Must be a maximum {$0}'),
            'latinNumeric' => _s('Only letters (A-Z a-z) and numbers (0-9)'),
            'notNumeric' => _s('Must not contain only numbers'),
            'matches' => _s('Must match'),
        ]);
    }

    /**
     * The rule to check only the Latin alphabet and numbers
     *
     * @param $value
     * @param $input
     * @param $args
     *
     * @return bool
     */
    public function validate_latinNumeric($value, $input, $args)
    {
        return (bool)preg_match('#^[a-zA-Z0-9]+$#', $value);
    }

    /**
     * Rule for inspection but not from the numbers
     *
     * @param $value
     * @param $input
     * @param $args
     *
     * @return bool
     */
    public function validate_notNumeric($value, $input, $args)
    {
        return (bool)!preg_match('#^[0-9]*$#', $value);
    }

    /**
     * The rule to check only a positive integer
     *
     * @param $value
     * @param $input
     * @param $args
     *
     * @return bool
     */
    public function validate_int($value, $input, $args)
    {
        return (bool)preg_match('/^\+?\d+$/', $value);
    }
}
