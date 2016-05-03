<?php

namespace Dore\Core\I18n;

use Dore\Core\Foundation\App;
use Dore\Core\User\Facade;
use Dore\Core\Http\Request;

/**
 * Class Locales
 */
class Locales
{
    /**
     * @var Request
     */
    private $request;

    private $availableLocales;
    private $userLocale;
    private $config = [];

    public function __construct(Request $request, Facade $user)
    {
        $this->request = $request;
        $this->userLocale = $user->get()->config()->lng;
        $this->config = App::config()->get('system.default');
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Receive the list of languages together with names and flags
     *
     * @return array Locale => (html) Language name with Flag
     */
    public function getLocalesList()
    {
        $list = $this->getLocalesDescriptions();

        if (!array_key_exists('en', $list)) {
            $list['en'] = 'English';
        }

        ksort($list);

        return $list;
    }

    /**
     * Automatic detection of language
     * @param $session
     * @return string
     */
    protected function getCurrentLocale($session)
    {
        if (!$this->config['lngSwitch']) {
            return $this->config['lng'];
        }

        if (isset($session->lng)) {
            return $session->lng;
        }

        $locale = $this->getUserLocale();
        $session->lng = $locale;

        return $locale;
    }

    /**
     * Get the user-selected language
     *
     * @return string
     */
    private function getUserLocale()
    {
        if ($this->userLocale !== '#' && in_array($this->userLocale, $this->getAvailableLocales())) {
            return $this->userLocale;
        }

        return $this->getBrowserLocale();
    }

    /**
     * Detect language by browser headers
     *
     * @return string
     */
    private function getBrowserLocale()
    {
        $locales = $this->request->getLanguages();

        if (!empty($locales)) {
            foreach ($locales as $value) {
                $lng = substr($value, 0, 2);
                if (in_array($lng, $this->getAvailableLocales())) {
                    return $lng;
                }
            }
        }

        return $this->config['lng'];
    }

    /**
     * Read descriptions fom .ini
     *
     * @return array
     */
    private function getLocalesDescriptions()
    {
        $description = [];
        $list = $this->getAvailableLocales();

        foreach ($list as $iso) {
            $file = LOCALE_PATH . $iso . DS . 'lng.ini';

            if (is_file($file) && ($desc = parse_ini_file($file)) !== false) {
                $description[$iso] = $this->getFlag($iso) . $desc['name'];
            }
        }

        return $description;
    }

    /**
     * Receive the list of available locales
     *
     * @return array
     */
    private function getAvailableLocales()
    {
        if ($this->availableLocales === null) {
            $list = glob(LOCALE_PATH . '*', GLOB_ONLYDIR);

            foreach ($list as $val) {
                $this->availableLocales[] = basename($val);
            }
        }

        return $this->availableLocales;
    }

    /**
     * Get language Flag
     *
     * @param string $locale
     * @return string
     */
    private function getFlag($locale)
    {
        $file = LOCALE_PATH . $locale . DS . 'lng.png';
        $flag = is_file($file) ? 'data:image/png;base64,' . base64_encode(file_get_contents($file)) : '';

        return $flag !== false ? '<img src="' . $flag . '" style="margin-right: 8px; vertical-align: middle">' : '';
    }
}
