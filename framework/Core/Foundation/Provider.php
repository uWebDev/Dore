<?php

namespace Dore\Core\Foundation;

use Desarrolla2\Cache\Adapter\File;
use Desarrolla2\Cache\Adapter\NotCache;
use Desarrolla2\Cache\Cache;
use Dore\Core\Foundation\App;
use Dore\Core\Captcha\Captcha;
use Dore\Core\I18n\Translate;
use Dore\Core\Paginator\Paginator;
use Dore\Core\Database\PDOmysql;
use Dore\Core\Dispatcher\Dispatcher;
use Dore\Core\Helper\CounterHelper;
use Dore\Core\Helper\LanguageHelper;
use Dore\Core\Helper\RouterHelper;
use Dore\Core\Http\Request;
use Dore\Core\Http\Token;
use Dore\Core\Mail\Mail;
use Dore\Core\User\Facade;
use Dore\Core\Validator\Validator;
use League\Plates\Engine;
use League\Plates\Extension\URI;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Dore\Core\Http\PdoSessionHandler;
use Dore\Core\Http\NativeSessionStorage;
use Dore\Core\Http\Session;

/**
 * Class Provider
 * @property Session $container['session']
 * @package App\Core
 */
class Provider implements ServiceProviderInterface
{

    /**
     * @param Container $container
     */
    public function register(Container $container)
    {
        $container['session'] = function ($c) {
            $session = new Session(new NativeSessionStorage($c['sessionHandler']));
            $session->setName('SID');
            $session->start();
            return $session;
        };

        $container['sessionHandler'] = function ($c) {
            return new PdoSessionHandler($c['db'], $c['request'], $c['dispatcher'], ['captcha']);
        };

        $container['lng'] = function () {
            return null;
        };

        $container['i18n'] = function ($c) {
            return (new Translate($c['request'], $c['session'], $c['user']))->setConfig(
                App::config()->get('system.default')
            );
        };

        $container['user'] = function ($c) {
            return new Facade($c['db'], $c['request'], $c['response'], $c['session']);
        };

        $container['captcha'] = function () {
            $captha = new Captcha(FONTS_PATH);
            $config = App::config()->get('captcha.default');
            $captha->setHeight($config['height']);
            $captha->setWidth($config['width']);
            $captha->setLenghtMax($config['lengthMax']);
            $captha->setLenghtMin($config['lengthMin']);

            return $captha;
        };

        $container['dispatcher'] = function () {
            return new Dispatcher();
        };

        $container['router'] = function () {
            return new \AltoRouter([]);
        };

        $container['view'] = function ($c) {
            $config = App::config()->get('system.default');
            $view = new Engine(TEMPLATE_PATH . $config['template']);
            $view->addData([
                'homeUrl' => $c['request']->getHost(), //TODO
                'title' => $config['hometitle'],
                'titlePage' => '',
                'template' => $config['template'],
                'isGuest' => $c['user']->isGuest(),
                'isAdmin' => $c['user']->isAdmin(),
                // 'userRights' => $user->getRights(),
            ]);
            $view->loadExtension(new CounterHelper($c['db'], $c['cacheFile']));
            $view->loadExtension(new LanguageHelper($c['lng'])); //TODO Language delete
            $view->loadExtension(new RouterHelper($c['router']));
            $view->loadExtension(new URI($c['request']->getPathInfo()));
            return $view;
        };

        $container['cacheFile'] = function () {
            $adapter = new File(CACHE_PATH);
            $adapter->setOption('ttl', 3600);
            $cache = new Cache(new NotCache());
            $cache->setAdapter($adapter);
            return $cache;
        };

        $container['validate'] = function () {
            return new Validator();
        };

        $container['db'] = function () {
            return new PDOmysql();
        };

        $container['token'] = function ($c) {
            return new Token($c['session']);
        };

        $container['mail'] = function () {
            return new Mail(new \PHPMailer());
        };

        $container['paginator'] = function () {
            return new Paginator();
        };

        $container['htmlpurifier'] = function () {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', CACHE_PATH . 'HTMLPurifier');
            return new \HTMLPurifier($config);
        };
    }
}
