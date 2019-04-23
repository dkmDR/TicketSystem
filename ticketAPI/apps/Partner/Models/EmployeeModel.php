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
 * @Table[name=employees]
 */
class EmployeeModel extends Aorm
{
    /**
     * @var integer
     * @PrimaryKey
     * @AutoIncrement
     * @Column[type=integer]
     */
    private $employee_id;
    /**
     * @var string
     * @Column[type=string]
     */
    private $first_name;
    /**
     * @var string
     * @Column[type=string]
     */
    private $last_name;
    /**
     * @var integer
     * @Column[type=integer]
     */
    private $status_id;
    /**
     * @var string
     * @Column[type=string]
     */
    private $password;
    /**
     * @var string
     * @Column[type=string]
     */
    private $email;
    /**
     * @var string
     * @Column[type=string]
     */
    private $user_name;
    /**
     * @var string
     * @Column[type=date]
     */
    private $created_on;
    /**
     * @var integer
     * @Column[type=integer]
     */
    private $created_by;
    /**
     * @var integer
     * @Column[type=integer]
     */
    private $active;
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
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param string $first_name
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    /**
     * @return int
     */
    public function getStatusId()
    {
        return $this->status_id;
    }

    /**
     * @param int $status_id
     */
    public function setStatusId($status_id)
    {
        $this->status_id = $status_id;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->user_name;
    }

    /**
     * @param string $user_name
     */
    public function setUserName($user_name)
    {
        $this->user_name = $user_name;
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

    /**
     * @return int
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * @param int $created_by
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param int $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

}
