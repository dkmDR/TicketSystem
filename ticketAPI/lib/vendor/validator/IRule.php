<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/4/2018
 * Time: 11:49 AM
 */

namespace lib\vendor\Validator;


interface IRule
{
    /**
     * @param $input
     * @return mixed
     */
    public function valid( $input );

    /**
     * @param $input
     * @param $inputCompare
     * @return mixed
     */
    public function compare( $input, $inputCompare );

    /**
     * @param $keyName
     * @param $isMessage
     * @return mixed
     */
    public function setKey( $keyName, $isMessage );
}