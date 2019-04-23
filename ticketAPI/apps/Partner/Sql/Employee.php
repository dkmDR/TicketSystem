<?php


namespace apps\Partner\Sql;

/**
 * Class Employee
 * @package apps\Partner\Sql
 */
class Employee
{
    /**
     * @param int $id
     * @return string
     */
    public static function get( $id = 0 ) {
        return "select 
                    e.employee_id as id, 
                    e.first_name, 
                    e.last_name, 
                    e.email, 
                    e.user_name,
                    e.password,
                    DATE_FORMAT(e.created_on, \"%M %d %Y %r\") as created_on,
                    s.status_id,
                    s.status_des,
                    CONCAT(creater.first_name,' ',creater.last_name) as created_by
                from employees e
                inner join status s on e.status_id = s.status_id 
                inner join employees creater on e.created_by = creater.employee_id
                where 
                    e.active = 1 and ( e.employee_id = $id or $id = 0 ) order by e.employee_id desc";
    }

    /**
     * @return string
     */
    public static function options(){
        return "select 
                    e.employee_id as value, 
                    UPPER( CONCAT(e.first_name, ' ',e.last_name) ) AS label
                from employees e
                inner join status s on e.status_id = s.status_id 
                where 
                    e.active = 1 order by e.employee_id desc";
    }

    /**
     * @param $email
     * @param $employee
     * @return string
     */
    public static function verifyEmail($email, $employee){
        $filter = ( $employee > 0 ) ? " and employee_id != $employee " : "";
        return "select * from employees where email = '$email' $filter";
    }

    /**
     * @param $userName
     * @param $employee
     * @return string
     */
    public static function verifyUserName($userName, $employee){
        $filter = ( $employee > 0 ) ? " and employee_id != $employee " : "";
        return "select * from employees where user_name = '$userName' $filter";
    }

    /**
     * @param $userName
     * @param $password
     * @return string
     */
    public static function logIn($userName, $password){
        return "SELECT 
                    e.* 
                FROM 
                    employees e
                WHERE 
                    e.active = 1
                    and ( e.user_name = '$userName' or e.email = '$userName' )
                    and e.password = '$password'";
    }

    /**
     * @param $sessionId
     * @return string
     */
    public static function getSession($sessionId) {
        return "
            select
                *
                from sessions
            where 
                session_id = '$sessionId'
        ";
    }
}