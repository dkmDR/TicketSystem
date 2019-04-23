<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/16/2018
 * Time: 10:19 AM
 */

namespace lib\http\middleware;

use stdClass;

/**
 * Interface IRole
 * @package lib\http\middleware
 */
interface IRole {
    /**
     * @param stdClass|null $object
     * @return array
     */
    public function handle(stdClass $object = null);
}