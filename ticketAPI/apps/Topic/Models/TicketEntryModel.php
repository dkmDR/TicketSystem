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
 * @Table[name=ticket_entries]
 */
class TicketEntryModel extends Aorm
{
    /**
     * @var integer
     * @PrimaryKey
     * @AutoIncrement
     * @Column[type=integer]
     */
    private $record_id;
    /**
     * @var integer
     * @Column[type=integer]
     */
    private $ticket_id;
    /**
     * @var integer
     * @Column[type=integer]
     */
    private $employee_id;
    /**
     * @var string
     * @Column[type=date]
     */
    private $date_from;
    /**
     * @var string
     * @Column[type=date]
     */
    private $date_to;
    /**
     * @var string
     * @Column[type=string]
     */
    private $note;
    /**
     * @var string
     * @Column[type=date]
     */
    private $created_on;
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
    public function getDateFrom()
    {
        return $this->date_from;
    }

    /**
     * @param string $date_from
     */
    public function setDateFrom($date_from)
    {
        $this->date_from = $date_from;
    }

    /**
     * @return string
     */
    public function getDateTo()
    {
        return $this->date_to;
    }

    /**
     * @param string $date_to
     */
    public function setDateTo($date_to)
    {
        $this->date_to = $date_to;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
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

}
