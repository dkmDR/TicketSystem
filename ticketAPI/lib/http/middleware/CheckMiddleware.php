<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/16/2018
 * Time: 11:00 AM
 */

namespace lib\http\middleware;

use lib\http\middleware\Filter;
use stdClass;

class CheckMiddleware extends Filter {

    /**
     * @var null|stdClass
     */
    private $param = null;

    /**
     * @var array
     */
    private $classes = array();

    /**
     * CheckMiddleware constructor.
     * @param $url
     */
    public function __construct($url)
    {

        $check = $this->checkUrl($url);

        if ( $check ) {
            if ( !empty( $this->param ) && isset ( $this->param->class ) && is_array( $this->param->class ) ) {
                $this->classes = $this->param->class;
            }
        }

    }

    /**
     * @param $clientRequest
     * @return bool
     */
    private function checkUrl ( $clientRequest ) {

        foreach ( parent::$filters as $key => $value ) {

            $requestPath = $key;

            $this->param = ( isset($value) && is_array($value) && !empty($value) ) ? (object) $value : null;

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

            if ( $validRequest ) {
                return true;
            }

        }

        return false;

    }

    /**
     * @return array
     */
    public function valid( ) {

        $classes = $this->classes;

        if ( !empty( $classes ) ) {

            foreach ( $classes as $class => $params ) {

                $nsClass = "\lib\http\middleware\\roles\\" . $class;

                if ( class_exists( $nsClass ) && isset($params) && is_array($params) ) {

                    $instance = new $nsClass();
                    $params = (object) $params;
                    $response = $instance->handle($params);

                    if ( is_array( $response ) ) {

                        if ( isset($response["allowed"]) && is_bool($response["allowed"]) ) {
                            if ( !$response["allowed"] )
                                return $response;
                        }

                    }

                }

            }

        }

        return array("allowed"=>true);

    }

}