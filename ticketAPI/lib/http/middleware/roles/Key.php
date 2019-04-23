<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/16/2018
 * Time: 4:12 PM
 */

namespace lib\http\middleware\roles;

use lib\http\middleware\IRole;
use stdClass;

/**
 * Class Key
 * @package lib\http\middleware\roles
 */
class Key implements IRole
{
    /**
     * @param stdClass|null $object
     * @return array
     */
    public function handle(stdClass $object = null)
    {
        $response = array(
            "text" => "key not found",
            "allowed" => false
        );

        if ( $object->key == "e10adc3949ba59abbe56e057f20f883e" ) {
          $response["text"] = "key found";
          $response["allowed"] = true;
        }

        return $response;
    }

}