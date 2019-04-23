<?php

namespace abstracts;

//This file cannot be accessed from browser
use lib\Preference;

defined('_EXEC_APP') or die('Ups! access not allowed');

/**
 *
 * PHP version 5.4
 *
 * LICENSE: This source file is subject to the MIT license
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.
 *
 * @category   Abstract
 * @package    abstracts
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 2.1
 */
abstract class Acontroller
{
    private $_model = null;

    protected function __construct(Aorm $model)
    {
        $this->_model = $model;
    }

    /**
     * get Model from Controller
     * @param string $model Module's Name . '/' . Controller's Name
     * @param object $properties database server information for model {
     *      server : server's name,
     *      user : server user,
     *      pass : server password,
     *      db : server database,
     *      port : server port by dbo
     * }
     * @return Aorm
     */
    public function getModel($model = '', $properties = null)
    {

        if (!empty($model)) {

            $stack = explode("/", $model);
            if (count($stack) < 2) {
                throw new \Exception('You must specify the module and model');
            }

            $referenceModel = $stack[0] . '\\Models\\' . $stack[1] . "Model";
            return new $referenceModel($properties);
        }

        Preference::$DO_RELATION = true;
        return $this->_model;

    }

}