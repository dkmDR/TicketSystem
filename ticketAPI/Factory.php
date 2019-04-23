<?php

use lib\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Auxiliary class for different functions within the framework, you can call this in anywhere
 *
 * PHP version 5.4
 *
 * LICENSE: This source file is subject to the MIT license
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.
 *
 * @category   General
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 1.0
 */
class Factory
{
    /**
     * @var object reference application
     */
    private static $_application = null;

    /**
     * @var stdClass session variables
     */
    private static $_session = null;

    /**
     * @var array
     */
    private static $_parameters = null;

    /**
     * @var null
     */
    private static $_logger = null;

    /**
     * set application controller
     * @param string $app give 'Module/Controller's name'
     * @deprecated since version 2.1
     */
    public static function set($app)
    {
        $clearNS = str_replace("\\", "/", $app);
        $split = explode("/", $clearNS);
        $application = $app;
        $model = "";
        //control just module + reference
        if (count($split) == 2) {
            $application = $split[0] . '\\Controllers\\' . $split[1] . 'Controller';
            $model = $split[0] . '\\Models\\' . $split[1] . 'Model';
        }

        self::$_application = new $application();
    }

    /**
     * set application
     * @version 2.1
     * @param \abstracts\Acontroller $controller
     */
    public static function setController(\abstracts\Acontroller $controller)
    {
        self::$_application = $controller;
    }

    /**
     * @return mixed object controller
     */
    public static function get()
    {
        return self::$_application;
    }

    /**
     * define object by session variables
     * @return void
     */
    public static function setSession()
    {

        self::$_session = new stdClass();

        if (isset($_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                self::$_session->$key = $value;
            }
        }
    }

    /**
     * @return stdClass get session var by key
     */
    public static function getSession()
    {
        return self::$_session;
    }

    /**
     * @param $key
     * @return bool
     */
    public static function isSession($key)
    {

        if (isset($_SESSION[$key])) {
            return TRUE;
        }
        return FALSE;

    }

    /**
     * verify if request variable was defined
     * @param string $name
     * @return boolean
     */
    private static function isRequest($name)
    {
        if (isset($_REQUEST[$name])) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * get Variable Request
     * @param string $name
     * @return mixed|null
     */
    public static function getInput($name)
    {

        $validRequest = self::isRequest($name);

        if ($validRequest) {
            return $_REQUEST[$name];
        }

        return NULL;

    }

    /**
     * redirect to main directory
     * @return string
     */
    public static function redirectTo()
    {
        return _HOST_ . _DIRECTORY_;
    }

    /**
     * scape html
     * @param string $escapestring
     * @param flag $flags
     * @param string $charset
     * @param boolean $double_encode
     * @return string
     */
    public static function escapeHtml($escapestring, $flags = ENT_QUOTES, $charset = 'UTF-8', $double_encode = TRUE)
    {

        return htmlentities($escapestring, $flags, $charset, $double_encode);

    }

    /**
     * log error file
     * @param string $string
     * @return void
     */
    public static function loggerError($string)
    {
        self::$_logger->error($string);
    }

    /**
     * log warning file
     * @param string $string
     * @return void
     */
    public static function loggerWarning($string)
    {
        self::$_logger->warning($string);
    }

    /**
     * log info file
     * @param string $string
     * @return void
     */
    public static function loggerInfo($string)
    {
        self::$_logger->info($string);
    }

    /**
     * log notice file
     * @param string $string
     * @return void
     */
    public static function loggerNotice($string)
    {
        self::$_logger->notice($string);
    }

    /**
     * log debug file
     * @param string $string
     * @return void
     */
    public static function loggerDebug($string)
    {
        self::$_logger->debug($string);
    }

    /**
     * log critical file
     * @param string $string
     * @return void
     */
    public static function loggerCritical($string)
    {
        self::$_logger->critical($string);
    }

    /**
     * start library logger..
     */
    public static function bootstrapLogger()
    {
        $path_log = _BASE_ . _DS_ . Config::$_LOGS . _DS_ . 'error_' . date("d_m_Y") . '.text';
        self::$_logger = new Logger("yaroa_logger");
        $streamHandler = new StreamHandler($path_log, Logger::DEBUG);
        self::$_logger->pushHandler($streamHandler);
        self::$_logger->addInfo("==============...start write logs...========================");
    }

    /**
     * @param mixed $params set parameters from view
     */
    public static function setParametersView($params)
    {
        self::$_parameters = $params;
    }

    /**
     *
     * @return array get parameters from view
     * array [ 0 => ??, 1 => 1 ]
     */
    public static function getParametersView()
    {
        return self::$_parameters;
    }

    /**
     * render another view from view
     * @param string $url url defined
     * @param array $params
     * @return string
     * @deprecated since version 2.1
     */
    public static function renderView($url, $params = array())
    {
        return Route::_get($url, $params);
    }

    /**
     * render view with annotation
     * @param $url
     * @return mixed
     * @version 2.1
     */
    public static function getView($url)
    {
        return Route::readModules($url);
    }

    /**
     * @param $content mixed
     * @return void;
     */
    public static function printDie($content)
    {
        echo "<pre>";
        print_r($content);
        echo "</pre>";
        die;
    }

    /**
     * @param $content mixed
     * @return void;
     */
    public static function printer($content)
    {
        echo "<pre>";
        print_r($content);
        echo "</pre>";
    }

}
