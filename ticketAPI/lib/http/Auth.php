<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 6/5/2018
 * Time: 4:18 PM
 */

namespace lib\http;

use RuntimeException;
use stdClass;
use Respect\Validation\Validator;

class Auth
{
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $key;
    /**
     * @var array
     */
    private $contentType = array("application/json","application/x-www-form-urlencoded");

    /**
     * Auth constructor.
     * @param $object
     */
    function __construct(stdClass $object)
    {
        $this->validate($object);

        $this->username = $object->username;
        $this->password = $object->password;
        $this->key = md5($object->username.":".$object->password);

    }

    /**
     * @return bool
     * @throws RuntimeException
     */
    public function authBasic() {
        if ( !isset( $_SERVER['PHP_AUTH_USER'] ) ) {
            throw new RuntimeException("Authorization denied");
        }

        $user = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];

        if ( $user == $this->username && $pass == $this->password ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws RuntimeException
     */
    public function authDigest() {
        if ( !isset($_SERVER['PHP_AUTH_DIGEST']) ) {
            throw new RuntimeException("Authorization denied");
        }

        $digest = $_SERVER['PHP_AUTH_DIGEST'];

        if ( $digest == $this->key ) {
            return true;
        }

        return false;
    }

    /**
     * @param stdClass $object
     * @throws RuntimeException
     */
    private function validate(stdClass $object) {

        if ( isset($object->host) && !Validator::notEmpty()->validate($object->host) ) {
            throw new RuntimeException("Undefined host/s into HOST SERVER");
        }

        if ( isset($object->username) && !Validator::notEmpty()->validate($object->username) ) {
            throw new RuntimeException("Undefined username into HOST SERVER");
        }

        if ( isset($object->password) && !Validator::notEmpty()->validate($object->password) ) {
            throw new RuntimeException("Undefined password into HOST SERVER");
        }

        if ( !in_array($_SERVER['CONTENT_TYPE'], $this->contentType) ) {
            throw new RuntimeException("Content type is not permitted");
        }

        if ( strpos($object->host, $_SERVER['HTTP_HOST']) === false ) {
            throw new RuntimeException("Host is not permitted");
        }

    }

    /**
     * @return string
     */
    public function info(){
        return "{
            host: ".$_SERVER['HTTP_HOST'].",
            remote: ".$_SERVER['REMOTE_HOST'].",
            content-type: ".$_SERVER['CONTENT_TYPE'].",
        }";
    }

    /**
     * @return null|object
     */
    public function postInput() {
        if ( !empty($_POST) ) {
            return (object) $_POST;
        }

        return NULL;
    }

    /**
     * @return bool
     */
    public function authPost() {

        $post = $this->postInput();

        if ( !empty($post) ) {

            if ( $post->auth_user_name == $this->username && $post->auth_pass_word == $this->password ) {
                return true;
            }

            return false;

        }

        return false;

    }

}