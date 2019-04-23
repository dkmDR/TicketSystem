<?php

namespace Partner\Controllers;

//This file cannot be accessed from browser
defined('_EXEC_APP') or die('Ups! access not allowed');

use abstracts\Aorm;
use abstracts\Acontroller;
use Exception;
use RuntimeException;
use stdClass;
use Respect\Validation\Validator;
use apps\Partner\Sql\Employee;

/**
 *
 * PHP version >= 5.4
 *
 * LICENSE: This source file is subject to the MIT license
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.
 *
 * @category   Controller
 * @package    Partner\Controllers
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 2.1
 * @Rest
 */
class EmployeeController extends Acontroller
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
     * @Routing[value=get-employees]
     */
    public function getList() {
        return $this->getModel()->query(Employee::get())->getObjectList();
    }

    /**
     * @return array
     * @throws Exception
     * @Routing[value=get-employee-options]
     */
    public function getOptions() {
        return $this->getModel()->query(Employee::options())->getObjectList();
    }

    /**
     * @param $id
     * @return stdClass
     * @throws Exception
     * @Routing[value=get-employee]
     */
    public function get($id){
        return $this->getModel()->query(Employee::get($id))->getObject();
    }
    /**
     * @param stdClass $object
     * @return array
     * @Routing[value=save-employee]
     */
    public function save($object)
    {
        try{
            $object = (object) $object;
            $model = $this->getModel();
        } catch (Exception $e){
            return array("message"=>$e->getMessage(),"status"=>false);
        }
        $model->begin();
        try{

            /**Validation**/
            $employeeId = (int) $model->getObjectModelValueByKey($object,"employee_id");
            $firstName = htmlspecialchars( trim( $model->getObjectModelValueByKey($object,"first_name") ) );
            $lastName = htmlspecialchars( trim( $model->getObjectModelValueByKey($object,"last_name") ) );
            $statusId = (int) $model->getObjectModelValueByKey($object,"status_id");
            $password = strtolower( trim( $model->getObjectModelValueByKey($object,"password") ) );
            $email = htmlspecialchars( trim( $model->getObjectModelValueByKey($object,"email") ) );
            $userName = strtolower( trim( $model->getObjectModelValueByKey($object,"user_name") ) );
            /*************/

            if ( !Validator::notEmpty()->validate($firstName) ) {
                throw new RuntimeException("Enter a first name");
            }

            if ( strlen($firstName) > 150 ) {
                throw new RuntimeException("First name string length is not permitted");
            }

            if ( !Validator::notEmpty()->validate($lastName) ) {
                throw new RuntimeException("Enter a last name");
            }

            if ( strlen($lastName) > 150 ) {
                throw new RuntimeException("Last name string length is not permitted");
            }

            if ( $statusId < 1 ) {
                throw new RuntimeException("Please select at least one status");
            }

            if ( $employeeId < 1 )
                if ( !Validator::notEmpty()->validate($password) ) {
                    throw new RuntimeException("Enter a valid password");
                }

            if ( !Validator::notEmpty()->validate($email) ) {
                throw new RuntimeException("Enter a valid email");
            }

            if ( !Validator::email()->validate($email) ) {
                throw new RuntimeException("Enter a valid email");
            }

            if ( strlen($email) > 200 ) {
                throw new RuntimeException("Email string length is not permitted");
            }

            if ( !Validator::notEmpty()->validate($userName) ) {
                throw new RuntimeException("Enter a valid user name");
            }

            if ( strlen($userName) > 100 ) {
                throw new RuntimeException("User Name string length is not permitted");
            }

            $sql = Employee::verifyEmail($email, $employeeId);
            $verifyEmail = $model->query($sql)->getObject();

            if ( !empty($verifyEmail) ) {
                throw new RuntimeException("This email($email) is already exists");
            }

            $sql = Employee::verifyUserName($userName, $employeeId);
            $verifyUserName = $model->query($sql)->getObject();

            if ( !empty($verifyUserName) ) {
                throw new RuntimeException("This UserName($userName) is already exists");
            }

            $model->setEmployeeId($employeeId);
            $model->setFirstName($firstName);
            $model->setLastName($lastName);
            $model->setStatusId($statusId);
            if ( Validator::notEmpty()->validate($password) ) {
                $passwordMD5 = md5($password);
                $model->setPassword($passwordMD5);
            }
            $model->setEmail($email);
            $model->setUserName($userName);
            if ( $employeeId < 1 )
                $model->setCreatedBy(1);

            $employee_id = $model->process();

            if ( $employee_id < 1 ) {
                throw new RuntimeException("Employee could not be created, please call the administrator");
            }

            $model->commit();
            return array("message"=>"Employee has been created","status"=>true);
        } catch (RuntimeException $re){
            $model->rollback("", false);
            return array("message"=>$re->getMessage(),"status"=>false);
        }
    }

    /**
     * @param $employee_id
     * @return array
     * @Routing[value=remove-employee]
     */
    public function remove($employee_id){
        try {
            $model = $this->getModel();
            $resource = $model->query(Employee::get($employee_id))->getObject();
            if (!empty($resource)) {
                $model->setEmployeeId($employee_id);
                $model->setActive(0);
                $id = $model->process();
                if ($id < 1) {
                    throw new Exception("Employee could not be removed, please call the administrator");
                }
            } else {
                throw new Exception("Employee does not found, please refresh the app");
            }
            return array("message"=>"","status"=>true);
        } catch (Exception $e){
            return array("message"=>$e->getMessage(),"status"=>false);
        }
    }

    /**
     * @param $userName
     * @param $password
     * @return array
     * @Routing[value=log-in]
     */
    public function logIn($userName, $password){
        try{
            $model = $this->getModel("Partner/Session");

            if ( empty($userName) ) {
                throw new Exception("Enter a valid user name");
            }

            if ( empty($password) ) {
                throw new Exception("Enter a valid password");
            }

            $userName = strtolower(trim($userName));
            $password = md5( strtolower(trim($password)) );

            $resource = $model->query(Employee::logIn($userName,$password))->getObject();

            $sessionId = "";
            if ( !empty($resource) ) {
                $sessionId = md5(date('Ymd_his').rand(0,getrandmax()));
            }

            if ( empty($sessionId) ) {
                throw new RuntimeException("User does not found!");
            }

            $model->setSessionId($sessionId);
            $model->setEmployeeId($resource->employee_id);
            $session = $model->process();

            if ( $session < 1 ) {
                throw new RuntimeException("Login could not be completed, please refresh the page");
            }

            return array("message"=>"","sessionId"=>$sessionId,"status"=>true);
        } catch (Exception $e){
            return array("message"=>$e->getMessage(),"status"=>false);
        }
    }

    /**
     * @param string $sessionId
     * @return array
     * @Routing[value=valid-session-id]
     */
    public function getSession($sessionId = '') {
        try{
            $model = $this->getModel();
            if ( empty($sessionId) ) {
                throw new Exception("Session does not found");
            }
            $session = $model->query(Employee::getSession($sessionId))->getObject();
            if ( empty($session) ) {
                throw new Exception("Session does not found");
            }
            return array("message"=>"","status"=>true);
        } catch (Exception $e){
            return array("message"=>$e->getMessage(),"status"=>false);
        }
    }

    /**
     * @param string $sessionId
     * @return array
     * @Routing[value=close-session]
     */
    public function closeSession($sessionId = ''){
        try{
            $model = $this->getModel();
            if ( empty($sessionId) ) {
                throw new Exception("Session does not found");
            }
            $session = $model->query(Employee::getSession($sessionId))->getObject();
            if ( empty($session) ) {
                throw new Exception("Session does not found");
            }
            $model->query("delete from sessions where session_id = '$sessionId'")->execute();
            return array("message"=>"","status"=>true);
        } catch (Exception $e){
            return array("message"=>$e->getMessage(),"status"=>false);
        }
    }

}