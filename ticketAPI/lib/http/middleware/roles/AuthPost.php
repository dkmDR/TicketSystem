<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/17/2018
 * Time: 10:55 AM
 */

namespace lib\http\middleware\roles;


use lib\http\middleware\IRole;
use lib\http\Auth;
use RuntimeException;
use stdClass;

class AuthPost implements IRole
{
    public function handle(stdClass $object = null)
    {
        $response = array();

        try {

            $auth = new Auth($object);

            $responseAuth = $auth->authPost();

            $response["text"] = ( $responseAuth ) ? "This request is allowed on Post" : "Please verify the credentials on Post";
            $response["allowed"] = $responseAuth;


        } catch (RuntimeException $rExec) {
            $response["text"] = $rExec->getMessage();
            $response["allowed"] = false;
        }

        return $response;
    }
}