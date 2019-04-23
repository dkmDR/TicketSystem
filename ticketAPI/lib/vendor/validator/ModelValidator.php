<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/4/2018
 * Time: 11:31 AM
 */

namespace lib\vendor\validator;

use abstracts\Aorm;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use ReflectionMethod;

class ModelValidator
{
    /**
     * @var array
     */
    private $columns = array();
    /**
     * @var Aorm
     */
    private $class;

    /**
     * ModelValidator constructor.
     * @param array $columns
     * @param Aorm $class
     */
    public function __construct( array $columns, Aorm $class )
    {
        $this->class = $class;
        $this->attributeForValid($columns);
    }

    /**
     * @param array $columns
     * @return void
     */
    private function attributeForValid( array $columns ) {

        if ( count($columns) ) {
            foreach ($columns as $index => $value) {
                $value = (object)$value;
                if (isset($value->valid) && !empty($value->valid)) {
                    array_push($this->columns, $value);
                }
            }
        }

    }

    /**
     * @throws RuntimeException
     * @return void
     */
    public function validate( ) {

        if ( count($this->columns) ) {

            foreach ($this->columns as $index => $value) {

                $split = explode(":", $value->valid);

                try {
                    for ($i = 0; $i < count($split); $i++) {

                        if (empty($split[$i])) {
                            continue;
                        }

                        $explode = explode("?", $split[$i]);

                        if (empty($explode[0])) {
                            continue;
                        }

                        $classSplit = ucfirst($explode[0]);

                        $parameter = (!empty($explode[1])) ? $explode[1] : "";

                        $class = $classSplit;
                        $nsClass = 'lib\vendor\validator\rules\\' . $class;
                        $rClass = new ReflectionClass($nsClass);

                        $getter = $this->class->gettingGetter($value->name, $this->class);

                        $input = $this->class->$getter();

                        $instance = new $nsClass;

                        $isMessage = false;
                        if (!empty($value->keyMessage)) {
                            $keyMessage = $value->keyMessage;
                            $isMessage = true;
                        } else {
                            $keyMessage = $value->name;
                        }

                        $instance->setKey($keyMessage, $isMessage);

                        $rMethod = new ReflectionMethod($instance, 'valid');

                        if (!empty($parameter)) {
                            $instance->compare($input, $parameter);
                        } else {
                            $instance->valid($input);
                        }

                    }
                } catch (ReflectionException $re) {
                    throw new RuntimeException($re->getMessage());
                }
            }

        }

    }
}