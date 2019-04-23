<?php

namespace Sample\Models;

//This file cannot be accessed from browser
defined('_EXEC_APP') or die('Ups! access not allowed');

use abstracts\Aorm;
use stdClass;

/**
 * PHP version >= 5.4
 *
 * LICENSE: This source file is subject to the MIT license
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.
 *
 * @category   Model
 * @package    Defaults\Models
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 2.1
 */
class SampleModel extends Aorm
{
    /**
     * @var null
     */
    private $properties = null;

    /**
     * AnnotationModel constructor.
     * @param stdClass|null $properties object { server : ??, user : ??, pass : ??, db : ??, port : ??, provider: ??}
     */
    public function __construct(stdClass $properties = null)
    {
        parent::__construct($this, $properties);
    }

    /**
     * @return null
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param null $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

}
