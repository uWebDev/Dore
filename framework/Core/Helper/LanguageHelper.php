<?php

//TODO language delete
namespace Dore\Core\Helper;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class LanguageHelper implements ExtensionInterface
{

    protected $languages;

    public function __construct($languages)
    {
        $this->languages = $languages;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('lng', [$this, 'lngString']);
    }

    /**
     * @param string $key
     * @param array $data
     * @param bool   $system
     *
     * @return string
     */
    public function lngString($key, $data = null, $system = false)
    {
        return $key;
//        return $this->languages->getPhrase($key, $data, $system);
    }

}
