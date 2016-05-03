<?php

namespace Dore\Core\Foundation;

use Violin\Contracts\ValidatorContract;
use Dore\Core\Exception\EnvironmentExceptions\NotExistsException;

/**
 * Class Model
 * @package Dore\Core\Foundation
 */
abstract class Model
{

    const SCENARIO_DEFAULT = 'default';

    protected $validate;
    protected $scenario = self::SCENARIO_DEFAULT;

    /**
     * BaseModel constructor.
     *
     * @param ValidatorContract $validate
     */
    public function __construct(ValidatorContract $validate)
    {
        $this->validate = $validate;
    }

    public function getScenario()
    {
        return $this->scenario;
    }

    public function setScenario($value)
    {
        $this->scenario = $value;
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => []
        ];
    }

    public function rules()
    {
        return [
            self::SCENARIO_DEFAULT => []
        ];
    }

    /**
     * @param array $data
     * @param type  $formName
     *
     * @return boolean
     */
    public function load(array $data, $formName = null)
    {
        $scope = $formName === null ? '' : $formName;
        if ($scope === '' && !empty($data)) {
            $this->setAttributes($data);
            return true;
        } elseif (isset($data[$scope])) {
            $this->setAttributes($data[$scope]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns attribute values.
     *
     * @param array $names  list of attributes whose value needs to be returned.
     *                      Defaults to null, meaning all attributes listed in [[attributes()]] will be returned.
     *                      If it is an array, only the attributes in the array will be returned.
     * @param array $except list of attributes whose value should NOT be returned.
     *
     * @return array attribute values (name => value).
     */
    public function getAttributes($names = null, $except = [])
    {
        $values = [];
        if ($names === null) {
            $names = $this->attributes();
        }
        foreach ($names as $name) {
            $values[$name] = $this->$name;
        }
        foreach ($except as $name) {
            unset($values[$name]);
        }
        return $values;
    }

    /**
     * Sets the attribute values in a massive way.
     *
     * @param array $values attribute values (name => value) to be assigned to the model.
     *
     * @see attributes()
     */
    public function setAttributes($values)
    {
        if (is_array($values)) {
            $attributes = $this->attributes();
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    $this->$name = $value;
                }
            }
        }
    }

    /**
     * @return bool
     * @throws NotExistsException
     */
    public function validate()
    {
        $this->beforeValidate();
        $scenarios = $this->scenarios();
        $scenario = $this->getScenario();
        if (!isset($scenarios[$scenario])) {
            throw new NotExistsException("Unknown scenario [$scenario]");
        }
        //
        $rules = $this->rules();
        $input = [];
        foreach ($scenarios[$scenario] as $key) {
            if (isset($rules[$key])) {
                $input[$key] = $rules[$key];
                $input[$key][0] = $this->{$input[$key][0]};
            } else {
                throw new NotExistsException("Unknown rule [$key]");
            }
        }

        $this->validate->validate($input);
        return $this->validate->passes();
    }

    /**
     * Running before validation
     * Allows you to add new rules for validation
     */
    public function beforeValidate()
    {
        // Default is empty
    }

    /**
     * @param $name
     *
     * @return null|string
     */
    public function errorFirst($name)
    {
        return $this->validate->errors()->first($name);
    }

    /**
     * @return array
     */
    public function errors()
    {
        $arr = [];
        $keys = $this->validate->errors()->keys();
        foreach ($keys as $key) {
            $arr[$key] = $this->errorFirst($key);
        }

        return $arr;
    }

    /**
     * @return array
     */
    public function attributes()
    {
        $class = new \ReflectionClass($this);
        $names = [];
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $names[] = $property->getName();
            }
        }
        return $names;
    }

    /**
     * @param $name
     *
     * @return mixed
     * @throws NotExistsException
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new NotExistsException(get_class($this) . "::$name method is only for installation");
        } else {
            throw new NotExistsException(get_class($this) . "::$name method to read unknown");
        }
    }

    /**
     * @param $name
     * @param $value
     *
     * @throws NotExistsException
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new NotExistsException(get_class($this) . "::$name method read-only");
        } else {
            throw new NotExistsException(get_class($this) . "::$name method to install unknown");
        }
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } else {
            return false;
        }
    }

    /**
     * @param $name
     *
     * @throws NotExistsException
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new NotExistsException(get_class($this) . "::$name disabling read-only properties");
        }
    }

    /**
     * @param $name
     * @param $params
     *
     * @throws NotExistsException
     */
    public function __call($name, $params)
    {
        throw new NotExistsException(get_class($this) . "::$name call of unknown method");
    }

}
