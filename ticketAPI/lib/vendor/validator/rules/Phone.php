<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/5/2018
 * Time: 1:48 PM
 */

namespace lib\vendor\validator\rules;


use lib\vendor\validator\IRule;
use Respect\Validation\Validator;
use RuntimeException;

class Phone implements IRule
{
    /**
     * @var string
     */
    private $message = "";
    /**
     * @param $input
     * @return mixed|void
     */
    public function valid($input)
    {
        if ( !Validator::phone()->validate($input) ) {
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

    /**
     * @param $keyName
     * @param $isMessage
     * @return mixed|void
     */
    public function setKey($keyName, $isMessage )
    {
        $this->message = ($isMessage) ? $keyName : " '".$keyName."' is not a phone ";
    }


}