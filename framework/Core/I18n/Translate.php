<?php

namespace Dore\Core\I18n;

use Dore\Core\User\Facade;
use RecursiveDirectoryIterator as DirIterator;
use RecursiveIteratorIterator as Iterator;
use Dore\Core\I18n\Plural\Pluralization;
use Dore\Core\I18n\Loader\GettextPo;
use Dore\Core\Http\Request;
use Dore\Core\Http\Session;

/**
 * Class Translate
 */
class Translate extends Locales
{
    /**
     * @var \ArrayObject Instances of loaded Domains
     */
    private $domains = [];

    private $module;
    private $currentLocale;
    private $cachePath;

    public function __construct(Request $request, Session $session, Facade $user)
    {
        parent::__construct($request, $user);
        // Get user defined locale
        $this->currentLocale = $this->getCurrentLocale($session);
        $this->cachePath = CACHE_PATH . 'locale';
    }

    /**
     * Translate a message
     *
     * @param string $message
     * @param string $domain
     * @return string
     */
    public function translateSystem($message, $domain = 'default')
    {
        return $this->getMessage($message, $domain, 'system');
    }

    /**
     * The plural version of translate()
     *
     * @param string $singular
     * @param string $plural
     * @param int    $count
     * @param string $domain
     * @return string
     */
    public function translateSystemPlural($singular, $plural, $count, $domain = 'default')
    {
        return $this->getPluralMessage($singular, $plural, $count, $domain, 'system');
    }

    /**
     * Translate a message with override the current domain
     *
     * @param string $message
     * @param string $domain
     * @return string
     */
    public function translateModule($message, $domain = 'default')
    {
        return $this->getMessage($message, $domain, $this->module);
    }

    /**
     * Plural version of $this->dgettext();
     *
     * @param string $singular
     * @param string $plural
     * @param int    $count
     * @param string $domain
     * @return string
     */
    public function translateModulePlural($singular, $plural, $count, $domain = 'default')
    {
        return $this->getPluralMessage($singular, $plural, $count, $domain, $this->module);
    }

    /**
     * Set module
     *
     * @param $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * Get Domain
     *
     * @param $domain
     * @param $module
     * @return \ArrayObject
     */
    public function getDomain($domain, $module)
    {
        if (!isset($this->domains[$module][$domain])) {
            $cacheFile = $this->cachePath . DS . $this->currentLocale . '.' . $module . '.' . $domain . '.cache';

            if (!is_file($cacheFile)) {
                $this->writeCache($cacheFile, $domain, $module);
            }

            $this->domains[$module][$domain] = new \ArrayObject(include $cacheFile);
        }

        return $this->domains[$module][$domain];
    }

    /**
     * Clear locales cache
     */
    public function clearCache()
    {
        if (is_dir($this->cachePath)) {
            $scan = new Iterator(new DirIterator($this->cachePath, DirIterator::SKIP_DOTS), Iterator::CHILD_FIRST);

            foreach ($scan as $fileinfo) {
                $action = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $action($fileinfo->getRealPath());
            }

            rmdir($this->cachePath);
        }
    }

    /**
     * Write locales cache
     *
     * @param string $cacheFile
     * @param string $domain
     * @param $module
     */
    private function writeCache($cacheFile, $domain, $module)
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath);
        }

        if ($module === 'system') {
            $path = LOCALE_PATH;
        } else {
            $path = MODULE_PATH . $module . DS . 'Assets' . DS . 'Locale' . DS;
        }

        $poFile = $path . $this->currentLocale . DS . $domain . '.po';
        file_put_contents($cacheFile, '<?php' . PHP_EOL . 'return '
            . var_export((new GettextPo)->parse($poFile), true) . ';' . PHP_EOL);
    }

    /**
     * Get translated message
     *
     * @param string $message
     * @param string $domain
     * @param $module
     * @return string
     */
    private function getMessage($message, $domain, $module)
    {
        $msgObj = $this->getDomain($domain, $module);

        return $msgObj->offsetExists($message) ? $msgObj->offsetGet($message) : $message;
    }

    /**
     * Plural version of $this->getMessage()
     *
     * @param string $singular
     * @param string $plural
     * @param int    $count
     * @param string $domain
     * @param $module
     * @return string
     */
    private function getPluralMessage($singular, $plural, $count, $domain, $module)
    {
        $msgObj = $this->getDomain($domain, $module);

        if (!$msgObj->offsetExists($plural)) {
            return ($count != 1) ? $plural : $singular;
        }

        $list = explode(chr(0), $msgObj->offsetGet($plural));

        return $list[Pluralization::get($count, $this->currentLocale)];
    }
}
