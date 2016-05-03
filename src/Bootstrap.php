<?php

use App\Module\Admin\AdminModule;
use App\Module\Forum\ForumModule;
use App\Module\User\UserModule;
use App\Module\Home\HomeModule;
use Dore\Core\Foundation\App;
use Dore\Core\Foundation\Provider;
use Dore\Core\Firewall\Firewall;
use Dore\Core\Firewall\FirewallMessageException;
use Dore\Core\Handler\ErrorHandler;
use Dore\Core\Http\Request;
use Sinergi\Config\Config;
use \Symfony\Component\HttpFoundation\Response;

/**
 * Define some PHP settings
 */
date_default_timezone_set('Europe/Moscow');
mb_internal_encoding('UTF-8');

/**
 * Константа (DIRECTORY_SEPARATOR)
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * @return string
 */
define('ROOT_PATH', __DIR__);

/**
 * //TODO
 */
define('DORE_ASSETS_PATH', dirname(__DIR__) . DS . 'framework' . DS . 'Assets' . DS);
/**
 * @return string ../Assets/
 */
define('ASSETS_PATH', ROOT_PATH . DS . 'Assets' . DS);

/**
 * @return string ..'/Assets/Cache/'
 */
define('CACHE_PATH', DORE_ASSETS_PATH . 'Cache' . DS);

/**
 * @return string ..'/Assets/Config/'
 */
define('CONFIG_PATH', ASSETS_PATH . 'Config' . DS);

/**
 * @return string ..'/Assets/Template/'
 */
define('TEMPLATE_PATH', ASSETS_PATH . 'Template' . DS);

/**
 * @return string ..'/Assets/Locale/'
 */
define('LOCALE_PATH', DORE_ASSETS_PATH . 'Locale' . DS);

/**
 * @return string ..'/Module/'
 */
define('MODULE_PATH', ROOT_PATH . DS . 'Module' . DS);

/**
 * @return string ..'/Assets/Fonts/'
 */
define('FONTS_PATH', DORE_ASSETS_PATH . 'Fonts' . DS);

/**
 * @return string ..'/Assets/Log'
 */
define('LOG_PATH', DORE_ASSETS_PATH . 'Log' . DS);

/**
 * Configuration for: Email server credentials
 * Here you can define how you want to send emails.
 * define("EMAIL_USE_SMTP", true);
 * define("EMAIL_SMTP_HOST", "ssl://smtp.gmail.com");
 * define("EMAIL_SMTP_AUTH", true);
 * define("EMAIL_SMTP_USERNAME", "xxxxxxxxxx@gmail.com");
 * define("EMAIL_SMTP_PASSWORD", "xxxxxxxxxxxxxxxxxxxx");
 * define("EMAIL_SMTP_PORT", 465);
 * define("EMAIL_SMTP_ENCRYPTION", "ssl");
 * It's really recommended to use SMTP!
 */
define('EMAIL_USE_SMTP', true);
define('EMAIL_SMTP_HOST', '');
define('EMAIL_SMTP_AUTH', true);
define('EMAIL_SMTP_USERNAME', '');
define('EMAIL_SMTP_PASSWORD', '');
define('EMAIL_SMTP_PORT', 465);
define('EMAIL_SMTP_ENCRYPTION', 'ssl');

/**
 * Configuring Session
 */
ini_set('session.use_trans_sid', '0');
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', true);
ini_set('session.gc_probability', '1');
ini_set('session.gc_divisor', '100');
ini_set('session.gc_maxlifetime', 60 * 60 * 24);

/**
 * Translate a message
 * @param string $message
 * @param string $domain
 *
 * @return string
 */
function _s($message, $domain = 'default')
{
    return App::i18n()->translateSystem($message, $domain);
}

/**
 * The plural version of _s()
 * @param string $singular
 * @param string $plural
 * @param int    $count
 * @param string $domain
 *
 * @return string
 */
function _sp($singular, $plural, $count, $domain = 'default')
{
    return App::i18n()->translateSystemPlural($singular, $plural, $count, $domain);
}

/**
 * Translate module
 * @param string $message
 * @param string $domain
 *
 * @return string
 */
function _m($message, $domain = 'default')
{
    return App::i18n()->translateModule($message, $domain);
}

/**
 * Plural version of _m()
 * @param string      $singular
 * @param string      $plural
 * @param string      $count
 * @param null|string $domain
 *
 * @return string
 */
function _mp($singular, $plural, $count, $domain = 'default')
{
    return App::i18n()->translateModulePlural($singular, $plural, $count, $domain);
}

try {
    //Error Handler
    $handler = new ErrorHandler(-1);
    $handler->displayError(DEBUG);
    $handler->logFile(true);

    // Register Request instance
    $request = Request::createFromGlobals();

    // Starting the Firewall
    (new Firewall())->run($request->getClientIps()[0]);

    // Register Response instance
    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'text/html');

    // Add config
    App::registerInstance(new Config(CONFIG_PATH), 'config');

    //Start application
    $app = new App($request, $response);
    $app->setModule([
        HomeModule::class,
        UserModule::class
    ]);
    $app->register(new Provider());

    $app->run();
} catch (FirewallMessageException $e) {
    echo '<h1>' . $e->getMessage() . '</h1>';
}
