<?php

namespace Dore\Core\Foundation;

use Dore\Core\Exception\SemanticExceptions\PageForbiddenException;
use Dore\Core\I18n\Translate;
use Dore\Core\Logger\Logger;
use Pimple\Container;
use Sinergi\Config\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Dore\Core\Exception\SemanticExceptions\InvalidArgumentException;
use Dore\Core\Exception\SemanticExceptions\PageNotFoundException;

/**
 * Class App
 * @package Dore\Core
 * @method static Logger log()
 * @method static Config config()
 */
class App extends Container
{

    const VERSION = '1.0.0';

    /**
     * @var array Object Pool
     */
    private static $instances = [];

    /**
     * @var array Multi instance Service Locator (alias => service)
     */
    private static $services = [];

    /**
     * @var array Single instance Service Locator (alias => service)
     */
    private static $singleInstanceServices = [
        'log' => Logger::class
    ];
    /**
     * @var $this
     */
    private static $app;

    /**
     * @var array
     */
    private $modules = [];

    /**
     * @param Request  $request
     * @param Response $response
     * @param          array
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct();
        self::setApplication($this);
        $this['request'] = $request;
        $this['response'] = $response;
    }

    /**
     * @param array $module
     *
     * @return $this
     */
    public function setModule(array $module)
    {
        $this->modules = $module;

        return $this;
    }

    /**
     * Factory
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     * @throws InvalidArgumentException
     * @throws
     */
    public static function __callStatic($name, array $args = [])
    {
        if (isset(self::$services[$name])) {
            return new self::$services[$name]($args);
        } elseif (isset(self::$instances[$name])) {
            return self::$instances[$name];
        } elseif (isset(self::$singleInstanceServices[$name])) {
            return self::$instances[$name] = new self::$singleInstanceServices[$name]($args);
        } else {
            throw new InvalidArgumentException("[$name] method not found");
        }
    }

    /**
     * Saves the application instance in a static field class.
     * @param $app
     *
     * @throws InvalidArgumentException
     */
    private static function setApplication($app)
    {
        if (self::$app === null) {
            self::$app = $app;
        } else {
            throw new InvalidArgumentException('Application can only be created once');
        }
    }

    /**
     * Add a new instance of an object
     * @param object $instance
     * @param string $alias
     *
     * @throws InvalidArgumentException
     */
    public static function registerInstance($instance, $alias)
    {
        if (!is_object($instance)) {
            throw new InvalidArgumentException("[$instance] must be the object");
        }

        if (isset(self::$instances[$alias], self::$services[$alias], self::$singleInstanceServices[$alias])) {
            throw new InvalidArgumentException("The instance [$alias] already exists");
        }

        self::$instances[$alias] = $instance;
    }

    /**
     * @return Translate
     */
    public static function i18n()
    {
        return self::$app['i18n'];
    }

    /**
     * Run the application
     */
    public function run()
    {
        try {
            $this->addRoutesModules();
            $this['dispatcher']->dispatch($this);
            $this['response']->prepare($this['request'])->send();
        } catch (PageNotFoundException $e) {
            $this['response']->setContent($this['view']->render('404'))
                ->setStatusCode(Response::HTTP_NOT_FOUND)->send();
        } catch (PageForbiddenException $e) {
            //TODO: добавить запись в лог (добавить ip кто пытался войти и т.д.)
            //TODO: изменить страницу на 404 или 403
            $this['response']->setContent($this['view']->render('404'))
                ->setStatusCode(Response::HTTP_FORBIDDEN)->send();
        }
    }

    /**
     * Build modules.
     */
    private function addRoutesModules()
    {
        foreach ($this->modules as $class) {
            /** @var $obj BaseModule */
            $obj = new $class();
            $this['router']->addRoutes($obj->getRoutes());
        }
    }

    /**
     * The user is redirected to another URL.
     * @param string $url    URL-address for redirection
     * @param int    $status Status code (302 defaults)
     *
     * @return RedirectResponse
     */
    public function redirect($url, $status = 302)
    {
        $redirect = new RedirectResponse($url, $status);
        $redirect->send();
    }

    /**
     * Convert some of the data in json-response.
     * @param mixed $data    Ответ данные
     * @param int   $status  Код состояния ответа
     * @param array $headers Массив заголовков ответа
     *
     * @return JsonResponse
     */
    public static function json($data = null, $status = 200, array $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }
}
