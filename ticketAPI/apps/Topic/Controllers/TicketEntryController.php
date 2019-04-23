<?php

namespace Topic\Controllers;

//This file cannot be accessed from browser
defined('_EXEC_APP') or die('Ups! access not allowed');

use abstracts\Aorm;
use abstracts\Acontroller;
use apps\Topic\Sql\Ticket;
use apps\Topic\Sql\Entry;
use Exception;
use RuntimeException;
use Respect\Validation\Validator;
use stdClass;

/**
 *
 * PHP version >= 5.4
 *
 * LICENSE: This source file is subject to the MIT license
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.
 *
 * @category   Controller
 * @package    Topic\Controllers
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 2.1
 * @Rest
 */
class TicketEntryController extends Acontroller
{
    /**
     * AnnotationController constructor.
     * @param Aorm $model
     */
    public function __construct(Aorm $model)
    {
        parent::__construct($model);
    }

    /**
     * @param $ticket
     * @return array
     * @throws Exception
     * @Routing[value=get-ticket-entries]
     */
    public function getByTicket($ticket){
        return $this->getModel()->query(Ticket::entries($ticket))->getObjectList();
    }

    /**
     * @param $object
     * @return array
     * @throws Exception
     * @Routing[value=get-entries-by-date]
     */
    public function getEntries($object){
        $object = (object) $object;
        $splitFrom = explode("T", $object->date_from);
        $splitTo = explode("T", $object->date_to);
        $dateFrom = $splitFrom[0];
        $dateTo = $splitTo[0];
        $sql = Entry::entries($dateFrom,$dateTo);
        return $this->getModel()->query($sql)->getObjectList();
    }
    /**
     * @param stdClass $object
     * @return array
     * @Routing[value=create-entry]
     */
    public function create($object)
    {
        try{
            $model = $this->getModel();
            $object = (object) $object;
        } catch (Exception $e){
            return array("message"=>$e->getMessage(),"status"=>false);
        }
        $model->begin();
        try{

            $ticket_id = (int) $model->getObjectModelValueByKey($object,"ticket_id");
            $employee_id = (int) $model->getObjectModelValueByKey($object,"employee_id");
            $date_from = $model->getObjectModelValueByKey($object,"date_from");
            $date_to = $model->getObjectModelValueByKey($object,"date_to");
            $note = htmlspecialchars( $model->getObjectModelValueByKey($object,"note") );

            if ( $ticket_id < 1 ){
                throw new RuntimeException("Ticket does not found, please refresh the page");
            }

            if ( $employee_id < 1 ){
                throw new RuntimeException("You must select at least one employee");
            }

            if ( !Validator::notEmpty()->validate($date_from) ) {
                throw new RuntimeException("Date from could not be empty");
            }

            if ( !Validator::date()->validate($date_from) ){
                throw new RuntimeException("Date from is not valid!");
            }

            if ( !Validator::notEmpty()->validate($date_to) ) {
                throw new RuntimeException("Date to could not be empty");
            }

            if ( !Validator::date()->validate($date_to) ){
                throw new RuntimeException("Date to is not valid!");
            }

            if ( !Validator::notEmpty()->validate($note) ) {
                throw new RuntimeException("Note could not be empty");
            }

            $model->setTicketId($ticket_id);
            $model->setEmployeeId($employee_id);
            $model->setDateFrom($date_from);
            $model->setDateTo($date_to);
            $model->setNote($note);

            $entry_id = $model->process();

            $completed = $model->query(Ticket::allUserPublished($ticket_id))->getObject()->completed;

            if ( $completed == 1 ) {
                $model->query("update tickets set status_id = 2 where ticket_id = $ticket_id")->execute();
            }

            if ( $entry_id < 1 ) {
                throw new RuntimeException("Entry could not be created, please call the administrator");
            }

            $model->commit();
            return array("message"=>"Entry has been created","status"=>true);
        } catch (RuntimeException $re){
            $model->rollback("", false);
            return array("message"=>$re->getMessage(),"status"=>false);
        }
    }

}