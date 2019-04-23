<?php

namespace Partner\Models;

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
 * @package    Partner\Models
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 2.1
 * @Table[name=sessions]
 */
class SessionModel extends Aorm
{
    /**
     * @var integer
     * @PrimaryKey
     * @AutoIncrement
     * @Column[type=integer]
     */
    private $record_id;
    /**
     * @var string
     * @Column[type=string]
     */
    private $session_id;
    /**
     * @var integer
     * @Column[type=integer]
     */
    private $employee_id;
    /**
     * @var string
     * @Column[type=date]
     */
    private $created_on;
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

    /**
     * @return int
     */
    public function getRecordId()
    {
        return $this->record_id;
    }

    /**
     * @param int $record_id
     */
    public function setRecordId($record_id)
    {
        $this->record_id = $record_id;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * @param string $session_id
     */
    public function setSessionId($session_id)
    {
        $this->session_id = $session_id;
    }

    /**
     * @return int
     */
    public function getEmployeeId()
    {
        return $this->employee_id;
    }

    /**
     * @param int $employee_id
     */
    public function setEmployeeId($employee_id)
    {
        $this->employee_id = $employee_id;
    }

    /**
     * @return string
     */
    public function getCreatedOn()
    {
        return $this->created_on;
    }

    /**
     * @param string $created_on
     */
    public function setCreatedOn($created_on)
    {
        $this->created_on = $created_on;
    }

}
