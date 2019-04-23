<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/4/2018
 * Time: 1:42 PM
 */

namespace lib\vendor\validator\rules;

use lib\vendor\validator\IRule;
use Respect\Validation\Validator;
use RuntimeException;

class HigherThanZero implements IRule
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
    public function setKey($keyName, $isMessage)
    {
        $this->message = ($isMessage) ? $keyName : " '".$keyName."' must be higher than Zero";
    }

    /**
     * @param $input
     * @return mixed|void
     */
    public function valid( $input )
    {
        if ( !Validator::intVal()->validate($input) || $input < 1 ) {
            throw new RuntimeException($this->message);
        }
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