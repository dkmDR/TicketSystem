<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/5/2018
 * Time: 1:32 PM
 */

namespace lib\vendor\validator\rules;

use lib\vendor\validator\IRule;
use RuntimeException;

class MinLength implements IRule
{
    /**
     * @var string
     */
    private $message = "";

    /**
     * @var string
     */
    private $isMessage = "";

    /**
     * @param $input
     * @return mixed|void
     */
    public function valid($input)
    {
        // TODO: Implement valid() method.
    }

    public function compare($input, $inputCompare)
    {
        if ( strlen( $input ) < $inputCompare ) {
            $message = ($this->isMessage) ? $this->message : $this->message . " character length must be higher than $inputCompare ";
            throw new RuntimeException($message);
        }
    }

    /**
     * @param $keyName
     * @param $isMessage
     * @return mixed|void
     */
    public function setKey($keyName, $isMessage )
    {
        $this->isMessage = $isMessage;

        $this->message = ($isMessage) ? $keyName : "'$keyName'";
    }
}