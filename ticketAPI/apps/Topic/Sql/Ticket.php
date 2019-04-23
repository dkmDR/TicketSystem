<?php


namespace apps\Topic\Sql;

/**
 * Class Ticket
 * @package apps\Topic\Sql
 */
class Ticket
{
    /**
     * @return string
     */
    public static function getList(){
        return "SELECT 
                    t.ticket_id as id,
                    DATE_FORMAT(t.ticket_date, \"%M %d %Y %r\") as ticket_date,
                    t.subject,
                    s.status_id,
                    s.status_des,
                    t.ticket_description,    
                    DATE_FORMAT(t.created_on, \"%M %d %Y %r\") as created_on,
                    CONCAT(e.first_name, ' ', e.last_name) as created_by,
                    GROUP_CONCAT( CONCAT(assigned.first_name, ' ', assigned.last_name) ) as assigned
                FROM 
                    tickets t 
                    inner join status s on t.status_id = s.status_id
                    inner join employees e on t.created_by = e.employee_id
                    inner join emp_vs_ticket empvt on empvt.ticket_id = t.ticket_id
                    inner join employees assigned on assigned.employee_id = empvt.employee_id
                WHERE 
                    t.active = 1
                group by 1,2,3,4,5,6,7,8
                order by t.ticket_id desc
                ";
    }

    /**
     * @param $id
     * @return string
     */
    public static function get($id = 0){
        return "
            SELECT 
                    t.ticket_id as id,
                    t.ticket_date,
                    DATE_FORMAT(t.ticket_date, \"%M %d %Y %r\") as ticket_date_format,
                    t.subject,
                    s.status_id,
                    s.status_des,
                    t.ticket_description as description,    
                    DATE_FORMAT(t.created_on, \"%M %d %Y %r\") as created_on,
                    CONCAT(e.first_name, ' ', e.last_name) as created_by            
                FROM 
                    tickets t 
                    inner join status s on t.status_id = s.status_id
                    inner join employees e on t.created_by = e.employee_id
                WHERE 
                    t.active = 1  
                    and ( t.ticket_id = $id or $id = 0) 
        ";
    }

    /**
     * @param $ticket
     * @return string
     */
    public static function getEmployees($ticket){
        return "
            select
                empvt.record_id as id,
                assigned.employee_id,
                assigned.first_name,
                assigned.last_name,
                assigned.email
                from 
                    tickets t
                    inner join emp_vs_ticket empvt on empvt.ticket_id = t.ticket_id
                    inner join employees assigned on assigned.employee_id = empvt.employee_id
            where
                t.active = 1
                and t.ticket_id = $ticket
        ";
    }

    /**
     * @param $ticket
     * @return string
     */
    public static function assignedEmployeesLabel($ticket){
        return "select   
                    GROUP_CONCAT(UPPER(CONCAT(e.first_name, ' ',e.last_name) ) ) AS label
                from employees e
                inner join status s on e.status_id = s.status_id 
                inner join emp_vs_ticket et on e.employee_id = et.employee_id
                where 
                    e.active = 1 
                    and et.ticket_id = $ticket
                    order by e.employee_id desc";
    }

    /**
     * @param $ticket
     * @return string
     */
    public static function assignedEmployees($ticket){
        return "select 
                    e.employee_id as value, 
                    UPPER( CONCAT(e.first_name, ' ',e.last_name) ) AS label
                from employees e
                inner join status s on e.status_id = s.status_id
                inner join emp_vs_ticket et on e.employee_id = et.employee_id 
                where 
                    e.active = 1
                    and ( e.employee_id, et.ticket_id ) NOT IN (select employee_id, ticket_id from ticket_entries where active = 1)
                    and et.ticket_id = $ticket
                order by e.employee_id desc";
    }

    /**
     * @param $ticket_id
     * @return string
     */
    public static function allUserPublished($ticket_id){
        return "select
                    CASE WHEN todo.published = todo.assigned 
                    THEN
                        1
                    ELSE
                        0
                    END AS completed
                FROM
                (
                SELECT
                    count(record_id) as published,
                    (select count(record_id) from emp_vs_ticket where ticket_id = $ticket_id) as assigned
                FROM ticket_entries
                WHERE
                    ticket_id = $ticket_id
                ) AS todo";
    }

    /**
     * @param $ticket
     * @return string
     */
    public static function entries($ticket){
        return "select
                    te.record_id as id,
                    CONCAT(e.first_name,' ',e.last_name) as employee_name,
                    DATE_FORMAT(te.created_on, \"%M %d %Y %r\") as created_on,
                    te.note
                    FROM
                        ticket_entries te
                        inner join employees e on te.employee_id = e.employee_id
                WHERE
                    te.active = 1
                    and te.ticket_id = $ticket
                    ";
    }
}