<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/16/2018
 * Time: 10:21 AM
 */

namespace lib\http\middleware\roles;

use lib\http\middleware\IRole;
use lib\http\Auth;
use RuntimeException;
use stdClass;

class AuthDigest implements IRole
{
    /**
     * @param stdClass|null $object
     * @return array
     */
    public function handle(stdClass $object = null)
    {
        $response = array();

        try {

            $auth = new Auth($object);

            $responseAuth = $auth->authDigest();

            $response["text"] = ( $responseAuth ) ? "This request is allowed in Digest" : "Please verify the credentials in Digest";
            $response["allowed"] = $responseAuth;


        } catch (RuntimeException $rExec) {
            $response["text"] = $rExec->getMessage();
            $response["allowed"] = false;
        }

        return $response;
    }

}