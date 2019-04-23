<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/4/2018
 * Time: 11:52 AM
 */

namespace lib\vendor\validator\rules;

use RuntimeException;
use lib\vendor\validator\IRule;
use Respect\Validation\Validator;

class NotEmpty implements IRule
{
    /**
     * @var string
     */
    private $message = "";

    /**
     * @param $keyName
     * @param $isMessage
     * @return mixed|void
     */
    public function setKey($keyName, $isMessage )
    {
        $this->message = ($isMessage) ? $keyName : " '".$keyName."' can not be null ";
    }

    /**
     * @param $input
     * @return bool
     * @throws RuntimeException
     */
    public function valid( $input )
    {
        if ( !Validator::notEmpty()->validate($input) ) {
            throw new RuntimeException($this->message);
        }

        return true;
    }

    /**
     * @param $input
     * @param $inputCompare
     * @return mixed|void
     */
    public function compare($input, $inputCompare)
    {
        // TODO: Implement compare() method.
    }

}