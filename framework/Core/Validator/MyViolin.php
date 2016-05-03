<?php

namespace Dore\Core\Validator;


use Violin\Support\MessageBag;
use Violin\Violin;

/**
 * https://github.com/alexgarrett/violin
 */
class MyViolin extends Violin
{

    /**
     * Kick off the validation using input and rules.
     *
     * @param  array $data
     * @param  array $rules
     *
     * @return Violin
     */
    public function validate(array $data, $rules = [])
    {
        $this->clearErrors();
        $this->clearFieldAliases();

        $data = $this->extractFieldAliases($data);

        // If the rules array is empty, then it means we are
        // receiving the rules directly with the input, so we need
        // to extract the information.
        if (empty($rules)) {
            $rules = $this->extractRules($data);
            $data = $this->extractInput($data);
        }

        $this->input = $data;

        foreach ($data as $field => $value) {
            $fieldRules = explode('|', $rules[$field]);

            foreach ($fieldRules as $rule) {
                if ($this->validateAgainstRule(
                    $field,
                    $value,
                    $this->getRuleName($rule),
                    $this->getRuleArgs($rule)
                )) {
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Validates value against a specific rule and handles
     * errors if the rule validation fails.
     *
     * @param  string $field
     * @param  string $value
     * @param  string $rule
     * @param  array  $args
     *
     * @return bool
     */
    protected function validateAgainstRule($field, $value, $rule, array $args)
    {
        $ruleToCall = $this->getRuleToCall($rule);

        if ($this->canSkipRule($ruleToCall, $value)) {
            return false;
        }

        $passed = call_user_func_array($ruleToCall, [
            $value,
            $this->input,
            $args
        ]);

        if (!$passed) {
            $this->handleError($field, $value, $rule, $args);
        }

        return !$passed;
    }

    /**
     * Gather errors, format them and return them.
     *
     * @return MessageBag
     */
    public function errors()
    {
        return parent::errors();
    }
}