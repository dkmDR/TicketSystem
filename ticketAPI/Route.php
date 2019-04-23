<?php

defined('_EXEC_APP') or die('Sorry, the application stopped');

use lib\Config;
use lib\routes\Web;
use lib\routes\Post;
use Minimizer\Minimizer;

class Route
{
    /**
     *
     * @var string variable with the current controller
     */
    private static $_current_controller = "";
    /**
     *
     * @var string variable with the current model
     */
    public static $_current_model = "";
    /**
     *
     * @var string variable with the current view
     */
    public static $_current_view = "";

    /**
     *
     * @var string variable with the current namespace
     */
    private static $_current_namespace = "";

    /**
     * Run Request
     * @param $url
     * @param array $params
     * @param string $content
     * @return false|mixed|string|void|null
     * @throws Exception
     */
    public static function _get($url, array $params = array(), $content = "")
    {

        $content = (empty($content)) ? "html" : $content;

        $requestURL = $url;

        $ocurrences = self::returnOcurrence($requestURL, $content);

        $founded = self::searchOcurrence($ocurrences, $requestURL);

        if (count($founded) < 1) {
            throw new Exception('Web rule not found');
        }

        $key = key($founded);

        if (empty($key)) {
            throw new Exception('Web rule key not found');
        }

        //valid parameters
        $keyValue = explode("/", $key);
        $splitRequest = explode("/", $requestURL);

        $parameters = array();
        $classes = $founded[$key];

        if (count($keyValue) > 0 && count($splitRequest) > 0) {
            for ($i = 0; $i < count($keyValue); $i++) {
                if (strpos($keyValue[$i], "[") !== FALSE && strpos($keyValue[$i], "]") !== FALSE) {
                    array_push($parameters, $splitRequest[$i]);
                }
            }
        }

        if (count($params) > 0) {
            for ($i = 0; $i < count($params); $i++) {
                array_push($parameters, $params[$i]);
            }
        }

        $content_ = self::readClasses($classes, $parameters);

        $response = null;

        switch ($content) {
            case "json":
                $response = json_encode($content_);
                break;
            case "html":

                $response = $content_;

                if (strpos($response, '.php') !== FALSE) {
                    if (strpos($response, "View") === FALSE) {
                        $stack = explode(".", $response);
                        $response = $stack[0] . "View." . $stack[1];
                    }

                    $path = _BASE_ . _DS_ . Config::$_MODULES_ . _DS_ . self::$_current_namespace . 'Views' . _DS_ . $response;

                    $path = str_replace("\\", "/", $path);

                    if (file_exists($path)) {
                        require_once $path;
                    } else {
                        throw new Exception('view not found');
                    }
                    return;
                }

                break;
            case "text":
                $response = (string)str_replace(".php", "", $content_);
                break;
            default:
                $response = "Content Undefined";
                break;
        }

        return $response;

    }

    /**
     * create namespace for call all classes
     * @param string $module
     * @return \stdClass
     */
    private static function createNameSpace($module)
    {

//        $namespace  = Config::$_MODULES_ . "\\" . $module . "\\";
        $namespace = $module . "\\";

        $std = new stdClass();

        $std->namespace = $namespace;
        $std->namespaceController = $namespace . "Controllers\\";
        $std->namespaceModel = $namespace . "Models\\";

        return $std;

    }

    /**
     * get References class
     * @param string $classes
     * @return \stdClass
     * @throws Exception
     */
    private static function getReferences($classes)
    {

        $exec_method = "display";
        $display = TRUE;
        $setView = "";

        if (empty($classes)) {
            throw new Exception('The references Class not found');
        }

        list($module, $refMethod) = explode("@", $classes);

        $diff_ref_method = explode(".", $refMethod);
        if (count($diff_ref_method) > 1) {
            list($reference, $method_view) = $diff_ref_method;
            $array_met_vie = explode("*", $method_view);
            if (count($array_met_vie) > 1) {
                list($exec_method, $setView) = $array_met_vie;
            } else {
                list($exec_method) = $array_met_vie;
            }
            $display = FALSE;
        } else {
            list($reference) = $diff_ref_method;
        }

        if ($exec_method == "display") {
            $display = TRUE;
        }

        $ns = self::createNameSpace($module);

        $std = new stdClass();

        $std->controller = $ns->namespaceController . $reference . "Controller";
        $std->model = $ns->namespaceModel . $reference . "Model";
        $std->view = $reference . "View";
        $std->otherview = $setView;
        $std->method = $exec_method;
        $std->display = $display;

        self::$_current_controller = $std->controller;

        self::$_current_model = $std->model;

        self::$_current_view = $std->view;

        self::$_current_namespace = $ns->namespace;

        return $std;
    }

    /**
     * Read Classes
     * @param $classes
     * @param $params
     * @return mixed
     * @throws Exception
     */
    private static function readClasses($classes, $params)
    {

        try {
            $ref = self::getReferences($classes);

            if ($ref->display) {
                array_unshift($params, $ref->view);

                $stack = $params;

                $v = array_shift($stack);

                $params = array();

                array_push($params, $v);
                array_push($params, $stack);
            }

            /**
             * get class controller
             */

            $reflection_class = new ReflectionClass($ref->controller);

            /**
             * get exec method
             */

            $reflection_method = $reflection_class->getMethod($ref->method);
            $parameters = $reflection_method->getParameters();

            $class = $reflection_class->name;

            //set application or controller
            Factory::set($class);

            $method = $reflection_method;

            $required_param = count(self::requiredParameters($parameters));

            if (count($params) > 0 && (count($required_param) > count($params))) {
                throw new Exception("Specify All Params");
            }

            //set aux view
            if ($ref->method == "display") {
                //when you send the parameters to one view
                if (isset($params[1][0])) {
                    $params[0] = (!empty($ref->otherview)) ? $ref->otherview : $ref->view;
                } else {
                    $params[0] = $ref->otherview;
                }
                self::$_current_view = $params[0];
            }

            $content = (count($params) > 0) ? $method->invokeArgs(new $class(), $params) : $content = $method->invoke(new $class());

            return $content;

        } catch (ReflectionException $ex) {
            throw new Exception ($ex->getMessage() . ' on file ' . $ex->getFile() . ' line ' . $ex->getLine());
        }

    }

    /**
     * search ocurrence on rules
     * @param string $requestURL http request
     * @return array
     */
    public static function returnOcurrence($requestURL, $content)
    {

        $diff = explode("/", $requestURL);

        switch ($content) {
            case "html":
                $webInterfaces = Web::$_rules;
                break;
            default:
                $webInterfaces = Post::$_rules;
                break;
        }

        $ocurrences = array();

        //search ocurrence on rules
        for ($i = 0; $i < count($diff); $i++) {
            if (!empty($diff[$i])) {
                foreach ($webInterfaces as $key => $value) {
                    if (stristr(strtolower($key), strtolower($diff[$i])) !== FALSE) {
                        $ocurrences[$key] = $value;
                    }
                }
            }
        }

        return $ocurrences;
    }

    /**
     * search into occurences
     * @param array $ocurrences ocurrences of rules
     * @param string $requestURL http request
     * @return array
     */
    public static function searchOcurrence($ocurrences, $requestURL)
    {

        $founded = array();

        $auxFound = array();

        $diff = explode("/", $requestURL);

        $countRequest = count($diff);

        /***********************verify if ocurrency exists*************************************************************/
        $thereArePosibleOccurrency = false;

        foreach ($ocurrences as $key => $value) {
            $diff_ocurrency = explode("/", $key);
            $breakOcurrency = false;
            $thisisnotoccurrency = true;
            $counterAux = 0;

            if (count($diff_ocurrency) != count($diff)) {
                continue;
            }

            for ($i = 0; $i < count($diff_ocurrency); $i++) {
                $validParams = false;

                if (strpos($diff_ocurrency[$i], "[") !== FALSE && strpos($diff_ocurrency[$i], "]") !== FALSE) {
                    $validParams = true;
                }

                for ($p = $counterAux; $p < count($diff); $p++) {

                    if ($diff_ocurrency[$i] != $diff[$p] && $validParams == false) {
                        $breakOcurrency = true;
                        break;
                    }

                    if ($validParams && !isset($diff[$p])) {
                        $breakOcurrency = true;
                        break;
                    }

                    $counterAux = $p + 1;
                    break;
                }

                if ($breakOcurrency) {
                    $thisisnotoccurrency = false;
                    break;
                }

            }

            if ($thisisnotoccurrency) {
                $thereArePosibleOccurrency = true;
                break;
            }

        }

        if (!$thereArePosibleOccurrency) {
            return array();
        }

        /**************************************************************************************************************/

        foreach ($ocurrences as $key => $value) {
            $diff_ = explode('/', $key);
            $countOcurrence = count($diff_);

            if ($countRequest == $countOcurrence) {
                //if not found match
                if (count($auxFound) < 1)
                    $auxFound[$key] = $value;

                $found = TRUE;
                for ($c = 0; $c < count($diff); $c++) {
                    if ((strpos($diff_[$c], "[") === FALSE && strpos($diff_[$c], "]") === FALSE) && $diff[$c] !== $diff_[$c]) {
                        $found = FALSE;
                        break;
                    }
                }

                if ($found) {
                    $founded[$key] = $value;
                    return $founded;
                }
            }
        }

        return (count($founded) > 0) ? $founded : $auxFound;

    }

    /**
     * Get js
     * @param array $js
     * @param string $_MODULE module where the files will be searched (optional)
     * @param array $_DIRECTORIES_ tree directory after main module (optional)
     * @param bool $_SEARCH_ROOT flag if you want search all file into root directory or in tree directory (optional)
     */
    public static function getJs(array $js, $_MODULE = "", $_DIRECTORIES_ = array(), $_SEARCH_ROOT = TRUE)
    {

        $define_dir = Config::$_ROOT_JS . _DS_;

        if (count($_DIRECTORIES_) > 0) {

            if ($_SEARCH_ROOT == FALSE)
                $define_dir = "";

            for ($i = 0; $i < count($_DIRECTORIES_); $i++) {
                $define_dir .= $_DIRECTORIES_[$i] . _DS_;
            }
        }

        $dir = ($_SEARCH_ROOT === FALSE) ? _DIR_MODULE_ . _DS_ : "";

        $is_module = (!empty($_MODULE)) ? $_MODULE . _DS_ : "";

        $path = _HOST_ . _DIRECTORY_ . _DS_ . $dir . $is_module . $define_dir;

        $path_ = _BASE_ . _DS_ . $dir . $is_module . $define_dir;

        if (count($js)) {
            for ($i = 0; $i < count($js); $i++) {
                if (file_exists($path_ . $js[$i] . '.js')) {
                    echo '<script type="text/javascript" src="' . $path . $js[$i] . '.js"></script>';
                }
            }
        }

    }

    /**
     * Get css
     * @param array $css
     * @param string $_MODULE module where the files will be searched (optional)
     * @param array $_DIRECTORIES_ tree directory after main module (optional)
     * @param bool $_SEARCH_ROOT flag if you want search all file into root directory or in tree directory (optional)
     */
    public static function getCss(array $css, $_MODULE = "", $_DIRECTORIES_ = array(), $_SEARCH_ROOT = TRUE)
    {

        $define_dir = Config::$_ROOT_CSS . _DS_;

        if (count($_DIRECTORIES_) > 0) {

            if ($_SEARCH_ROOT == FALSE)
                $define_dir = "";

            for ($i = 0; $i < count($_DIRECTORIES_); $i++) {
                $define_dir .= $_DIRECTORIES_[$i] . _DS_;
            }
        }

        $dir = ($_SEARCH_ROOT === FALSE) ? _DIR_MODULE_ . _DS_ : "";

        $is_module = (!empty($_MODULE)) ? $_MODULE . _DS_ : "";

        $path = _HOST_ . _DIRECTORY_ . _DS_ . $dir . $is_module . $define_dir;

        $path_ = _BASE_ . _DS_ . $dir . $is_module . $define_dir;

        if (count($css)) {
            for ($i = 0; $i < count($css); $i++) {
                if (file_exists($path_ . $css[$i] . '.css')) {
                    echo '<link href="' . $path . $css[$i] . '.css" media="screen" rel="stylesheet" type="text/css" >';
                }
            }
        }

    }

    /**
     * get any library
     * @param array $libraries file name
     * @param string $_MAIN_DIRECTORY_ main directory from path root
     * @param string $_EXTENSION_ extension file (without dot) (optional)
     * @param string $_MODULE module where the files will be searched (optional)
     * @param array $_DIRECTORIES_ tree directory after main module (optional)
     * @param bool $_SEARCH_ROOT flag if you want search all file into root directory or in tree directory (optional)
     */
    public static function getLibrary(array $libraries, $_MAIN_DIRECTORY_, $_EXTENSION_ = '', $_MODULE = "", $_DIRECTORIES_ = array(), $_SEARCH_ROOT = TRUE)
    {

        $define_dir = $_MAIN_DIRECTORY_ . _DS_;

        if (count($_DIRECTORIES_) > 0) {

            if ($_SEARCH_ROOT == FALSE)
                $define_dir = "";

            for ($i = 0; $i < count($_DIRECTORIES_); $i++) {
                $define_dir .= $_DIRECTORIES_[$i] . _DS_;
            }
        }

        $dir = ($_SEARCH_ROOT === FALSE) ? _DIR_MODULE_ . _DS_ : "";

        $is_module = (!empty($_MODULE)) ? $_MODULE . _DS_ : "";

        $path = _HOST_ . _DIRECTORY_ . _DS_ . $dir . $is_module . $define_dir;

        $path_ = _BASE_ . _DS_ . $dir . $is_module . $define_dir;

        if (count($libraries)) {
            for ($i = 0; $i < count($libraries); $i++) {
                switch ($_EXTENSION_) {
                    case 'js':

                        if (file_exists($path_ . $libraries[$i] . '.js')) {
                            echo '<script type="text/javascript" src="' . $path . $libraries[$i] . '.js"></script>';
                        }

                        break;

                    case 'css':

                        if (file_exists($path_ . $libraries[$i] . '.css')) {
                            echo '<link href="' . $path . $libraries[$i] . '.css" media="screen" rel="stylesheet" type="text/css" >';
                        }

                        break;

                    case 'php':

                        if (file_exists($path_ . $libraries[$i] . '.php')) {
                            require_once $path . $libraries[$i] . '.php';
                        }

                        break;

                    default:

                        $file_content = file_get_contents($path . $libraries[$i] . '.' . $_EXTENSION_);

                        echo $file_content;

                        break;
                }
            }
        }

    }

    /**
     * verify required params by method
     * @param array $parameters
     * @return array
     */
    private static function requiredParameters(Array $parameters)
    {
        $returning = array();
        if (!empty($parameters)) {
            for ($i = 0; $i < count($parameters); $i++) {
                if (!$parameters[$i]->isOptional()) {
                    array_push(
                        $returning,
                        array(
                            "name" => $parameters[$i]->name,
                            "position" => $parameters[$i]->getPosition()
                        )
                    );
                }
            }
        }

        return $returning;
    }

    /**
     * @param string $header
     * @throws Exception
     * @return mixed
     */
    public static function header($header = '')
    {
        $defaultHeader = ( !empty(Config::$_MAIN_HEADER)) ? Config::$_MAIN_HEADER : "Header";
        $header = (!empty($header)) ? $header : $defaultHeader;

        $root_path = _BASE_ . _DS_ . _LAYOUT_ . _DS_ . $header . ".php";
        if ((file_exists($root_path))) {
            require_once $root_path;
        } else {
            throw new Exception('Layout header not found');
        }
    }

    /**
     * @param string $footer
     * @throws Exception
     * @return mixed
     */
    public static function footer($footer = '')
    {
        $defaultFooter = (!empty(Config::$_MAIN_FOOTER)) ? Config::$_MAIN_FOOTER : "Footer";
        $footer = (!empty($footer)) ? $footer : $defaultFooter;

        $root_path = _BASE_ . _DS_ . _LAYOUT_ . _DS_ . $footer . ".php";
        if ((file_exists($root_path))) {
            require_once $root_path;
        } else {
            throw new Exception('Layout footer not found');
        }
    }

    /**
     * search Route Mapping
     * @param $clientRequest
     * @return mixed
     * @throws Exception
     * @version 2.1
     */
    public static function readModules($clientRequest)
    {
        $files = scandir(Config::$_MODULES_);
        $modules = Config::$_MODULES_;
        if (count($files)) {
            for ($i = 0; $i < count($files); $i++) {
                if (!empty($files[$i]) && ($files[$i] != "." && $files[$i] != "..")) {
                    $file = (string)$files[$i];
                    $path = _BASE_ . _DS_ . $modules . _DS_ . $file;
                    if (is_dir($path)) {
                        $content = self::readControllers($clientRequest, $path, $file);
                        if ($content->result) {
                            return $content->content;
                        }
                    } else {
                        throw new Exception("The file path could not be found : " . $path);
                    }
                }
            }
            throw new Exception("The client request could not be found");
        } else {
            throw new Exception("The files could not be found");
        }
    }

    /**
     * Route mapping by Controllers
     * @param $clientRequest
     * @param $path
     * @param $module
     * @return stdClass
     * @throws Exception
     * @version 2.1
     */
    private static function readControllers($clientRequest, $path, $module)
    {
        $path .= _DS_ . "Controllers";

        $std = new stdClass();
        $std->content = "";
        $std->result = false;

        if (is_dir($path)) {
            $controllers = scandir($path);

            if (count($controllers)) {
                for ($i = 0; $i < count($controllers); $i++) {
                    if (!empty($controllers[$i]) && ($controllers[$i] != "." && $controllers[$i] != "..")) {
                        $controller = (string)$controllers[$i];
                        if (file_exists($path)) {
                            $controllerName = str_replace(".php", "", $controller);
                            $referenceController = str_replace("Controller", "", $controllerName);
                            try {
                                $class = $module . '\Controllers\\' . $controllerName;
                                $classModel = $module . '\Models\\' . $referenceController . "Model";
                                if (!class_exists($classModel)) {
                                    throw new RuntimeException(" Model could not be found " . $classModel);
                                }
                                $instanceModel = new $classModel();
                                $classMethods = get_class_methods($class);
                                $instance = new $class($instanceModel);
                                $reClass = new ReflectionClass($class);
                                $docsClass = $reClass->getDocComment();
                                $isRestFullClass = (strpos($docsClass, "@Rest") !== FALSE) ? "json" : "";
                                $generalHost = self::getGeneralHost($docsClass);
                                /*get Layouts*/
                                $layoutClass = self::getLayouts($docsClass, $module);

                                for ($c = 0; $c < count($classMethods); $c++) {
                                    $method = new ReflectionMethod($instance, $classMethods[$c]);
                                    $docs = $method->getDocComment();
                                    $position = strpos($docs, "@Routing");
                                    if ($position !== FALSE) {
                                        $requestPath = "";
                                        $requestType = $isRestFullClass;
                                        $requestAllowedMethod = "POST:GET:OPTIONS";
                                        $requestAllowedHost = $generalHost;
                                        for ($b = $position; $b < strlen($docs); $b++) {
                                            /*Last Character*/
                                            if (!isset($docs[$b]) || $docs[$b] == "*") {
                                                break;
                                            }
                                            /***************/
                                            $requestPath .= $docs[$b];
                                        }
                                        /*break routing path*/
                                        $requestPath = str_replace("@Routing", "", $requestPath);
                                        $requestPath = str_replace("[", "", $requestPath);
                                        $requestPath = str_replace("]", "", $requestPath);
                                        $splitRouting = explode(",", $requestPath);

                                        for ($splitIndex = 0; $splitIndex < count($splitRouting); $splitIndex++) {
                                            if (strpos($splitRouting[$splitIndex], "value") !== FALSE) {
                                                $splitValue = explode("=", $splitRouting[$splitIndex]);
                                                $requestPath = $splitValue[1];
                                            }
                                            if (strpos($splitRouting[$splitIndex], "type") !== FALSE && empty($requestType)) {
                                                $splitValue = explode("=", $splitRouting[$splitIndex]);
                                                $requestType = (!empty($splitValue[1])) ? $splitValue[1] : "";
                                            }
                                            if (strpos($splitRouting[$splitIndex], "allowedMethod") !== FALSE ) {
                                                $splitValue = explode("=", $splitRouting[$splitIndex]);
                                                if ( !empty($splitValue[1]) )
                                                    $requestAllowedMethod = strtoupper( $splitValue[1] );
                                            }
                                            if (strpos($splitRouting[$splitIndex], "allowedHost") !== FALSE ) {
                                                $splitValue = explode("=", $splitRouting[$splitIndex]);
                                                $requestAllowedHost = (!empty($splitValue[1])) ? $splitValue[1] : "";
                                            }
                                        }

                                        /*to know if the request has parameter*/
                                        $realMethod = false;
                                        if (strpos($requestPath, "{") !== FALSE && strpos($requestPath, "}") !== FALSE) {
                                            $splitClientRequest = explode("/", $clientRequest);
                                            $splitRequestPath = explode("/", $requestPath);
                                            if (count($splitClientRequest) == count($splitRequestPath)) {
                                                $realMethod = true;
                                            }
                                        }

                                        /*control client request with parameters or not*/
                                        $validRequest = false;
                                        if ($realMethod) {
                                            $validRequest = true;
                                            for ($p = 0; $p < count($splitClientRequest); $p++) {
                                                if (strpos($splitRequestPath[$p], "{") === FALSE && strpos($splitRequestPath[$p], "}") === FALSE) {
                                                    if ($splitClientRequest[$p] != $splitRequestPath[$p]) {
                                                        $validRequest = false;
                                                        break;
                                                    }
                                                }
                                                if (!$validRequest) {
                                                    break;
                                                }
                                            }
                                        } else {
                                            if (strtolower(trim($clientRequest)) == strtolower(trim($requestPath))) {
                                                $validRequest = true;
                                            }
                                        }

                                        if ($validRequest) {
                                            /*the framework found the client request*/

                                            /*Layouts by method*/
                                            $layoutsByMethod = self::getLayoutsByMethod($docs, $module);
                                            if ($layoutsByMethod != NULL) {
                                                $layoutClass = $layoutsByMethod;
                                            }

                                            /*HANDLE CORS (Cross-origin resource sharing)*/
                                            $useCors = false;
                                            if( Config::$_CORS ){
                                                $useCors = true;
                                            }
                                            /**********************************************/
                                            if($useCors) {
                                                $corsParams = self::validCors($requestAllowedHost, $requestAllowedMethod);
                                                if (count($corsParams))
                                                    $parameters = $corsParams;
                                                else
                                                    $parameters = self::clientRequestParameter($clientRequest, $requestPath);
                                            } else {
                                                $parameters = self::clientRequestParameter($clientRequest, $requestPath);
                                            }

                                            $content = self::callControllerMethod($instance, $method, $parameters, $requestType, $layoutClass);
                                            $std->content = $content;
                                            $std->result = true;
                                            return $std;
                                        }

                                    }

                                }
                            } catch (ReflectionException $rexec) {
                                throw new Exception($rexec->getMessage());
                            } catch (RuntimeException $rexection) {
                                throw new Exception($rexection->getMessage());
                            }
                        } else {
                            throw new Exception("The controller path could not be found : " . $path);
                        }
                    }
                }

            } else {
                throw new Exception("The controllers could not be found");
            }
        }

        return $std;
    }

    /**
     * @param \abstracts\Acontroller $instanceController
     * @param ReflectionMethod $method
     * @param array $params
     * @param $requestType
     * @param stdClass $layoutClass
     * @return mixed|string
     * @throws Exception
     */
    private static function callControllerMethod(\abstracts\Acontroller $instanceController, ReflectionMethod $method, array $params, $requestType, stdClass $layoutClass)
    {
        //set main controller
        Factory::setController($instanceController);

        $required_param = count(self::requiredParameters($method->getParameters()));
        $paramsCount = count($params);

        if (($required_param > 0) && ($required_param > $paramsCount)) {
            throw new Exception("the method has parameters required");
        }

        $content = (count($params) > 0) ? $method->invokeArgs($instanceController, $params) : $content = $method->invoke($instanceController);

        $requestType = strtolower(trim($requestType));
        switch ($requestType) {
            case "json":
                $content = json_encode($content);
                break;
            case "html":

                $view = (count($params) > 0) ? $method->invokeArgs($instanceController, $params) : $content = $method->invoke($instanceController);

                $callView = trim($view) . ".php";

                ob_start();

                self::header($layoutClass->header);

                $content = ob_get_contents();

                if (file_exists($layoutClass->pathView . $callView)) {
                    require_once $layoutClass->pathView . $callView;
                } else {
                    echo $view;
                }

                $content = ob_get_contents();

                self::footer($layoutClass->footer);

                $content = ob_get_contents();

                ob_end_clean();

                /**
                 * Minify all html, js and css code
                 */
                if (Config::$_DEVELOPING_ == FALSE) {
                    $minimizer = new Minimizer();
                    $content = $minimizer->minifyHtml($content);
                }
                break;
        }

        return $content;
    }

    /**
     * get parameters by client request
     * @param $clientRequest
     * @param $methodRouting
     * @return array
     */
    private static function clientRequestParameter($clientRequest, $methodRouting)
    {

        $key_reserved = array("url");

        $params = array();
        $_VARIABLE_REQUEST = null;
        if (!empty($_POST)) {
            $_VARIABLE_REQUEST = $_POST;
        } else {
            $_VARIABLE_REQUEST = $_GET;
        }

        foreach ($_VARIABLE_REQUEST as $key => $value) {
            $add = TRUE;
            for ($i = 0; $i < count($key_reserved); $i++) {
                if ($key_reserved[$i] == $key) {
                    $add = FALSE;
                    break;
                }
            }
            if ($add) {
                array_push($params, $value);
            }
        }

        $keyValue = explode("/", $methodRouting);
        $splitRequest = explode("/", $clientRequest);

        $parameters = array();

        if (count($keyValue) > 0 && count($splitRequest) > 0) {
            for ($i = 0; $i < count($keyValue); $i++) {
                if (strpos($keyValue[$i], "{") !== FALSE && strpos($keyValue[$i], "}") !== FALSE) {
                    if (isset($splitRequest[$i]))
                        array_push($parameters, $splitRequest[$i]);
                }
            }
        }

        if (count($params) > 0) {
            for ($i = 0; $i < count($params); $i++) {
                array_push($parameters, $params[$i]);
            }
        }

        return $parameters;

    }

    /**
     * get layout by controller
     * @param $docsClass
     * @return stdClass
     */
    private static function getLayouts($docsClass, $module)
    {

        $position = strpos($docsClass, "@Layouts");
        $std = new stdClass();
        $std->header = "Header";
        $std->footer = "Footer";
        $std->pathView = _BASE_ . _DS_ . _DIR_MODULE_ . _DS_ . $module . _DS_ . "Views" . _DS_;
        if ($position !== FALSE) {
            $layout = "";
            for ($b = $position; $b < strlen($docsClass); $b++) {
                /*Last Character*/
                if (empty($docsClass[$b]) || $docsClass[$b] == "*") {
                    break;
                }
                /***************/
                $layout .= $docsClass[$b];
            }
            /*break routing path*/
            $layout = str_replace("@Layouts", "", $layout);
            $layout = str_replace("[", "", $layout);
            $layout = str_replace("]", "", $layout);
            $splitLayout = explode(",", $layout);

            for ($splitIndex = 0; $splitIndex < count($splitLayout); $splitIndex++) {
                if (strpos($splitLayout[$splitIndex], "head") !== FALSE) {
                    $splitValue = explode("=", $splitLayout[$splitIndex]);
                    $std->header = (!empty($splitValue[1])) ? trim($splitValue[1]) : "header";
                }
                if (strpos($splitLayout[$splitIndex], "foot") !== FALSE) {
                    $splitValue = explode("=", $splitLayout[$splitIndex]);
                    $std->footer = (!empty($splitValue[1])) ? trim($splitValue[1]) : "footer";
                }
            }

        }

        return $std;

    }

    /**
     * @param $docsClass
     * @param $module
     * @return null|stdClass
     */
    private static function getLayoutsByMethod($docsClass, $module)
    {

        $position = strpos($docsClass, "@Layouts");
        $std = new stdClass();
        $std->header = "header";
        $std->footer = "footer";
        $std->pathView = _BASE_ . _DS_ . _DIR_MODULE_ . _DS_ . $module . _DS_ . "Views" . _DS_;
        if ($position !== FALSE) {
            $layout = "";
            for ($b = $position; $b < strlen($docsClass); $b++) {
                /*Last Character*/
                if (empty($docsClass[$b]) || $docsClass[$b] == "*") {
                    break;
                }
                /***************/
                $layout .= $docsClass[$b];
            }
            /*break routing path*/
            $layout = str_replace("@Layouts", "", $layout);
            $layout = str_replace("[", "", $layout);
            $layout = str_replace("]", "", $layout);
            $splitLayout = explode(",", $layout);

            for ($splitIndex = 0; $splitIndex < count($splitLayout); $splitIndex++) {
                if (strpos($splitLayout[$splitIndex], "head") !== FALSE) {
                    $splitValue = explode("=", $splitLayout[$splitIndex]);
                    $std->header = (!empty($splitValue[1])) ? trim($splitValue[1]) : "header";
                }
                if (strpos($splitLayout[$splitIndex], "foot") !== FALSE) {
                    $splitValue = explode("=", $splitLayout[$splitIndex]);
                    $std->footer = (!empty($splitValue[1])) ? trim($splitValue[1]) : "footer";
                }
            }

            return $std;

        }

        return NULL;

    }

    /**
     * Valid Cross-origin resource sharing
     * @param string $host
     * @param string $methods
     * @return array
     */
    private static function validCors( $host, $methods ) {
        $splitMethods = explode(":", $methods);
        $splitHost = (!empty($host)) ? explode("||", $host) : array();
        return self::cors($splitHost, $splitMethods);
    }

    /**
     * Cross-origin resource sharing
     * @param array $allowedHost
     * @param array $methods
     * @return array
     */
    private static function cors( array $allowedHost = array(), array $methods = array() ) {
        $response = array();
        $host = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'];
        if ( isset($_SERVER['HTTP_ORIGIN']) && ( $_SERVER['HTTP_ORIGIN'] != $host ) ) {

            if ( count($allowedHost) )
                if ( !in_array($_SERVER['HTTP_ORIGIN'], $allowedHost) ) {
                    header('HTTP/1.0 403 Forbidden');
                    header('Content-Type: text/plain');
                    echo "You cannot repeat this request, host denied";
                    exit;
                }

            if ( !in_array($_SERVER['REQUEST_METHOD'], $methods) ) {
                header('HTTP/1.0 403 Forbidden');
                header('Content-Type: text/plain');
                echo "You cannot repeat this request, access denied";
                exit;
            }
            //    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            //    header('Access-Control-Allow-Credentials: true');
            //    header('Access-Control-Max-Age: 86400');
            // Access-Control headers are received during OPTIONS requests
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                //    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                //        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                //    }
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
                }
                exit;
            } else if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
                //Receive the RAW post data.
                $content = trim(file_get_contents("php://input"));
                $decoded = json_decode($content);
                if ( $decoded != NULL) {
                    $std = array();
                    $res = array();
                    foreach ($decoded as $key => $value) {
                        $std[$key] = $value;
                    }
                    array_push($res, $std);
                    $response = $res;
                } else {
                    array_push($response, $content);
                }
            }

        }

        return $response;
    }

    /**
     * @param $docs
     * @return string
     */
    private static function getGeneralHost($docs) {

        $entityHost = "";
        $position = strpos($docs, "@allowedHost");
        if ($position !== FALSE) {
            $entityInfo = "";
            for ($b = $position; $b < strlen($docs); $b++) {
                /*Last Character*/
                if (!isset($docs[$b]) || $docs[$b] == "*") {
                    break;
                }
                /***************/
                $entityInfo .= $docs[$b];
            }

            /*break info*/
            $entityInfo = str_replace("@allowedHost", "", $entityInfo);
            $entityInfo = str_replace("[", "", $entityInfo);
            $entityInfo = str_replace("]", "", $entityInfo);
            $splitEntity = explode(",", $entityInfo);

            for ($splitIndex = 0; $splitIndex < count($splitEntity); $splitIndex++) {
                if (strpos($splitEntity[$splitIndex], "host") !== FALSE) {
                    $splitValue = explode("=", $splitEntity[$splitIndex]);
                    $trimVal = trim($splitValue[1]);
                    if (!empty($trimVal)) {
                        $entityHost = $trimVal;
                    }
                }
            }

            if (empty($entityHost)) {
                throw new RuntimeException("Host name could not be found");
            }

        }

        return $entityHost;

    }

}