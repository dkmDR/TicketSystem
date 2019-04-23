<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/4/2018
 * Time: 1:45 PM
 */

namespace lib\vendor\validator\rules;

use lib\vendor\validator\IRule;
use Respect\Validation\Validator;
use RuntimeException;

class Email implements IRule
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
        $this->message = ($isMessage) ? $keyName : " '".$keyName."' must be an email";
    }

    /**
     * @param $input
     * @return mixed|void
     */
    public function valid($input)
    {
        if ( !Validator::email()->validate($input) ) {
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