<?php

namespace Topic\Models;

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
 * @package    Topic\Models
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 2.1
 * @Table[name=tickets]
 */
class TicketModel extends Aorm
{
    /**
     * @var integer
     * @PrimaryKey
     * @AutoIncrement
     * @Column[type=integer]
     */
    private $ticket_id;
    /**
     * @var string
     * @Column[type=date]
     */
    private $ticket_date;
    /**
     * @var string
     * @Column[type=string]
     */
    private $subject;
    /**
     * @var integer
     * @Column[type=integer]
     */
    private $status_id;
    /**
     * @var string
     * @Column[type=string]
     */
    private $ticket_description;
    /**
     * @var integer
     * @Column[type=integer]
     */
    private $created_by;
    /**
     * @var string
     * @Column[type=date]
     */
    private $created_on;
    /**
     * @var string
     * @Column[type=string]
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
    public function getTicketId()
    {
        return $this->ticket_id;
    }

    /**
     * @param int $ticket_id
     */
    public function setTicketId($ticket_id)
    {
        $this->ticket_id = $ticket_id;
    }

    /**
     * @return string
     */
    public function getTicketDate()
    {
        return $this->ticket_date;
    }

    /**
     * @param string $ticket_date
     */
    public function setTicketDate($ticket_date)
    {
        $this->ticket_date = $ticket_date;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
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
    public function getTicketDescription()
    {
        return $this->ticket_description;
    }

    /**
     * @param string $ticket_description
     */
    public function setTicketDescription($ticket_description)
    {
        $this->ticket_description = $ticket_description;
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
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

}
