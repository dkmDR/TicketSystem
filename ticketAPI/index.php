<?php

//define all necessary for framework..
require_once 'Autoloader.php';

//define static variables
require_once 'bootstrap.php';

//handle request
require_once 'Route.php';

use lib\Config;
use Minimizer\Minimizer;
use lib\routes\Layout;
use lib\http\middleware\CheckMiddleware;

class Main
{
    /**
     * Exclude request value
     * @var array request variables
     */

    private static $_exclude_request_variable = array("content", "url", "layout");

    /**
     * add www to http request
     * @param string $URL_request
     * @throws Exception
     * @deprecated since version 2.1
     */
    public static function reviewServer($URL_request)
    {

        if (!in_array($_SERVER['HTTP_HOST'], Config::$_LOCAL_SERVERS)) {
            if (
                strpos(Config::$_HOST_, "www") !== FALSE
            ) {
                if (strpos($_SERVER['HTTP_HOST'], "www") === FALSE) {
                    $render = Config::$_HOST_ . Config::$_MAIN_DIRECTORY . $URL_request;
                    header("Location: $render");
                }

            } else {
                throw new Exception('Error in request to server, please call the administrator');
            }
        }
    }

    /**
     * handle application...
     * @param string $URL_request
     * @deprecated since version 2.1
     */
    public static function run($URL_request)
    {

        $url = "";
        $content__ = "";

        try {

            $url = $URL_request;

            $content = (isset($_REQUEST['content'])) ? $_REQUEST['content'] : "";

            $headerLayout = "Header";
            $footerLayout = "Footer";

            ob_start();

            $params = array();
            $_VARIABLE_REQUEST = null;
            if (!empty($_POST)) {
                $_VARIABLE_REQUEST = $_POST;
            } else {
                $_VARIABLE_REQUEST = $_GET;
            }

            foreach ($_VARIABLE_REQUEST as $key => $value) {
                $add = TRUE;
                for ($i = 0; $i < count(self::$_exclude_request_variable); $i++) {
                    if (self::$_exclude_request_variable[$i] == $key) {
                        $add = FALSE;
                        break;
                    }
                }
                if ($add) {
                    array_push($params, $value);
                }
            }

            echo Route::_get($url, $params, $content);

            $content__ = ob_get_contents();

            ob_end_clean();

            $_LAST_CONTENT_ = "";

            ob_start();

            if (empty($content)) {

                $ocurrencies = Route::returnOcurrence($url, "html");

                $ocurrencie_found = Route::searchOcurrence($ocurrencies, $url);

                $getKey = key($ocurrencie_found);

                if (isset(Layout::$_layouts[$getKey])) {
                    $layouts = Layout::$_layouts[$getKey];

                    $headerLayout = (isset($layouts["header"])) ? $layouts["header"] : "";
                    $footerLayout = (isset($layouts["footer"])) ? $layouts["footer"] : "";
                }

                Route::header($headerLayout);

                $_LAST_CONTENT_ = ob_get_contents();

            }

            echo $content__;

            $_LAST_CONTENT_ = ob_get_contents();

            if (empty($content)) {

                Route::footer($footerLayout);

                $_LAST_CONTENT_ = ob_get_contents();
            }

            ob_end_clean();

            /**
             * Minify all html, js and css code
             */
            if ((empty($content) || $content == "html") && Config::$_DEVELOPING_ == FALSE) {
                $p = new Minimizer();
                $_LAST_CONTENT_ = $p->minifyHtml($_LAST_CONTENT_);
            }

            echo $_LAST_CONTENT_;

        } catch (\Exception $ex) {
            $error = (!Config::$_DEVELOPING_) ? "
                            <p>Sorry, it was found the internal error.</p> 
                            <p>Please refresh the browser or exit of system.</p> 
                            <p>If the problem persist, please call the administrator</p>
                            " : $ex->getMessage();

            \Factory::loggerError($ex->getMessage());

            $path_error_file = _BASE_ . _DS_ . _LAYOUT_ . _DS_ . Config::$_ERROR_LAYOUT . '.php';
            if (file_exists($path_error_file)) {
                require_once $path_error_file;
            } else {
                echo $error;
            }
        }
    }

    /**
     * Run Application
     */

    public static function mainInit()
    {
        $url = (isset($_REQUEST['url'])) ? $_REQUEST['url'] : Config::$_ROUTES_ROOT_KEY;

        /*Check Middleware library*/

            $middlewareResponse = new CheckMiddleware($url);

            $response = (object) $middlewareResponse->valid();

            if ( !$response->allowed ) {
                echo json_encode($response);
                return;
            }

        /***************************/

        self::annotationRouting($url);
    }

    /**
     * using routing by annotation
     * @param $url
     */
    private static function annotationRouting($url)
    {
        try {
            echo Route::readModules($url);
        } catch (\Exception $ex) {
            $error = (!Config::$_DEVELOPING_) ? "An internal error has been found, please check the logs" : $ex->getMessage();

            \Factory::loggerError($ex->getMessage());

            $path_error_file = _BASE_ . _DS_ . _LAYOUT_ . _DS_ . Config::$_ERROR_LAYOUT . '.php';
            if (file_exists($path_error_file)) {
                require_once $path_error_file;
            } else {
                echo $error;
            }
        }
    }
}

/**
 * Execute application...
 */
Main::mainInit();
