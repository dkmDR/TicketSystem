<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/16/2018
 * Time: 4:10 PM
 */

namespace lib\http\middleware\roles;


use lib\http\middleware\IRole;
use lib\http\Auth;
use RuntimeException;
use stdClass;

class AuthBasic implements IRole
{
    public function handle(stdClass $object = null)
    {
        $response = array();

        try {

            $auth = new Auth($object);

            $responseAuth = $auth->authBasic();

            $response["text"] = ( $responseAuth ) ? "This request is allowed in Basic" : "Please verify the credentials in Basic";
            $response["allowed"] = $responseAuth;


        } catch (RuntimeException $rExec) {
            $response["text"] = $rExec->getMessage();
            $response["allowed"] = false;
        }

        return $response;
    }

}