<?php

namespace Topic\Controllers;

//This file cannot be accessed from browser
defined('_EXEC_APP') or die('Ups! access not allowed');

use abstracts\Aorm;
use abstracts\Acontroller;
use apps\Partner\Sql\Employee;
use apps\Topic\Sql\Ticket;
use Exception;
use RuntimeException;
use stdClass;
use Respect\Validation\Validator;

/**
 *
 * PHP version >= 5.4
 *
 * LICENSE: This source file is subject to the MIT license
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.
 *
 * @category   Controller
 * @package    Defaults\Controllers
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 2.1
 * @Rest
 */
class TicketController extends Acontroller
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
     * @return array
     * @throws Exception
     * @Routing[value=search-tickets]
     */
    public function getList(){
        return $this->getModel()->query(Ticket::getList())->getObjectList();
    }

    /**
     * @param $id
     * @return stdClass
     * @throws Exception
     * @Routing[value=get-ticket]
     */
    public function get($id){
        return $this->getModel()->query(Ticket::get($id))->getObject();
    }

    /**
     * @param $ticket
     * @return array
     * @throws Exception
     * @Routing[value=get-ticket-info]
     */
    public function getInfo($ticket){
        return array(
            "ticket" => $this->getModel()->query(Ticket::get($ticket))->getObject(),
            "assigned" => $this->getModel()->query(Ticket::assignedEmployeesLabel($ticket))->getObject(),
            "employees" => $this->getModel()->query(Ticket::assignedEmployees($ticket))->getObjectList()
        );
    }

    /**
     * @param $ticket
     * @return array
     * @throws Exception
     * @Routing[value=get-employees-by-ticket-options]
     */
    public function getEmployeesOption($ticket){
        return $this->getModel()->query(Ticket::assignedEmployees($ticket))->getObjectList();
    }

    /**
     * @param $ticket
     * @return array
     * @throws Exception
     * @Routing[value=get-employees-by-ticket]
     */
    public function getEmployeeList($ticket){
        return $this->getModel()->query(Ticket::getEmployees($ticket))->getObjectList();
    }

    /**
     * @param stdClass $object
     * @return array
     * @Routing[value=create-ticket]
     */
    public function create($object)
    {
//        \Factory::printDie($object);
        try{
            $model = $this->getModel();
            $empTicket = $this->getModel("Topic/EmpVsTicket");
            $object = (object) $object;
        } catch ( Exception $e ) {
            return array("message"=>$e->getMessage(),"status"=>false);
        }
        $model->begin();
        try{

            $ticket_id = (int) $model->getObjectModelValueByKey($object,"ticket_id");
            $subject = htmlspecialchars( $model->getObjectModelValueByKey($object,"subject") );
            $date = $model->getObjectModelValueByKey($object,"ticket_date");
            $status = (int) $model->getObjectModelValueByKey($object,"status_id");
            $ticket_description = $model->getObjectModelValueByKey($object,"ticket_description");
            $employees = $model->getObjectModelValueByKey($object,"employees");
            $sessionId = $model->getObjectModelValueByKey($object,"sessionId");

            if ( empty($sessionId) ) {
                throw new RuntimeException("You are not logged in #1");
            }

            $session = $model->query(Employee::getSession($sessionId))->getObject();

            if ( empty($session) ) {
                throw new RuntimeException("You are not logged in #2");
            }

//            if ( Validator::notEmpty()->validate($date) ) {
//                $split = explode("T", $date);
//                if ( !empty($split) ) {
//                    $date = $split[0];
//                }
//            }

            if ( empty($subject) ) {
                throw new RuntimeException("Subject could not be empty");
            }

            if ( strlen($subject) > 150 ) {
                throw new RuntimeException("Subject is not valid, string length is not permitted");
            }

            if ( !Validator::notEmpty()->validate($date) ) {
                throw new RuntimeException("Date could not be empty");
            }

//            if ( !Validator::date("Y-m-d")->validate($date) ){
            if ( !Validator::date()->validate($date) ){
                throw new RuntimeException("Date is not valid!");
            }

            if ( $status < 1 ) {
                throw new RuntimeException("Select at least one status");
            }

            if ( empty($employees) ) {
                throw new RuntimeException("Please select at least one employee");
            }

            if ( !Validator::notEmpty()->validate($ticket_description) ) {
                throw new RuntimeException("Description could not be empty");
            }

			$dateResource = $model->query("SELECT CAST('$date' AS DATETIME) as ticket_date")->getObject();
			
            $model->setTicketId($ticket_id);
            $model->setTicketDate($dateResource->ticket_date);
            $model->setSubject($subject);
            $model->setStatusId($status);
            $model->setTicketDescription($ticket_description);
            $model->setActive(1);
            if ( $ticket_id < 1 )
                $model->setCreatedBy($session->employee_id);

            if ( $ticket_id > 0 ) {
                $model->query("update tickets set active = 0 where ticket_id = $ticket_id")->execute();
            }

            $ticketId = $model->process();

            if ( $ticketId < 1 ) {
                throw new RuntimeException("Ticket could not be created/updated, please make sure that you modify at least one input");
            }

            if ( $ticket_id > 0 ) {
                $model->query("delete from emp_vs_ticket where ticket_id = $ticket_id")->execute();
                $ticketId = $ticket_id;
            }

            foreach ($employees as $key => $employee){
                $empTicket->setEmployeeId($employee->value);
                $empTicket->setTicketId($ticketId);
                $recordId = $empTicket->process();
                if ( $recordId < 1 ){
                    throw new RuntimeException("Employee could not be added, please call the administrator");
                }
            }

            $model->commit();
            return array("message"=>"Ticket was created","status"=>true);
        } catch (RuntimeException $re){
            $model->rollback("", false);
            return array("message"=>$re->getMessage(),"status"=>false);
        }
    }

    /**
     * @param $ticket
     * @return array
     * @throws Exception
     * @Routing[value=remove-ticket]
     */
    public function remove($ticket){
        try{
            $model = $this->getModel();
        } catch (Exception $e){
            return array("message"=>$e->getMessage(),"status"=>false);
        }
        try {
            $record = $model->query("select * from tickets where ticket_id = $ticket and active = 1")->getObject();
            if (empty($record)) {
                throw new RuntimeException("Ticket does not found, please refresh the page");
            }
            $model->query("update tickets set active = 0 where ticket_id = $ticket")->execute();
            $tickets = $this->getList();
            return array("message"=>"Record was removed","tickets"=>$tickets,"status"=>true);
        } catch (RuntimeException $re){
            return array("message"=>$re->getMessage(),"status"=>false);
        }
    }

    /**
     * @param $record_id
     * @return array
     * @throws Exception
     * @Routing[value=remove-assign-ticket]
     */
    public function removeAssigned($record_id){
        try{
            $model = $this->getModel();
        } catch (Exception $e){
            return array("message"=>$e->getMessage(),"status"=>false);
        }
        try {
            $record = $model->query("select * from emp_vs_ticket where record_id = $record_id")->getObject();
            if (empty($record)) {
                throw new RuntimeException("Record does not found, please refresh the page");
            }
            $employeesList = $this->getEmployeeList($record->ticket_id);
            if ( count($employeesList) == 1 ) {
                throw new RuntimeException("You can not remove all assigned employee");
            }
            $model->query("delete from emp_vs_ticket where record_id = $record_id")->execute();
            $employees = $this->getEmployeeList($record->ticket_id);
            return array("message"=>"Record was removed","employees"=>$employees,"status"=>true);
        } catch (RuntimeException $re){
            return array("message"=>$re->getMessage(),"status"=>false);
        }
    }

    /**
     * @param $record_id
     * @return array
     * @throws Exception
     * @Routing[value=remove-entry]
     */
    public function removeEntry($record_id){
        try{
            $model = $this->getModel();
        } catch (Exception $e){
            return array("message"=>$e->getMessage(),"status"=>false);
        }
        try {
            $record = $model->query("select * from ticket_entries where record_id = $record_id")->getObject();
            if (empty($record)) {
                throw new RuntimeException("Record does not found, please refresh the page");
            }
            $model->query("delete from ticket_entries where record_id = $record_id")->execute();
            $entries = $model->query(Ticket::entries($record->ticket_id))->getObjectList();
            return array("message"=>"Record was removed","entries"=>$entries,"status"=>true);
        } catch (RuntimeException $re){
            return array("message"=>$re->getMessage(),"status"=>false);
        }
    }

}