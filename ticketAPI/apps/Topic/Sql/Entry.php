<?php


namespace apps\Topic\Sql;


class Entry
{
    /**
     * @param $date_from
     * @param $date_to
     * @return string
     */
    public static function entries($date_from,$date_to){
        return "SELECT
                    t.ticket_id,
                    CONCAT(e.first_name,' ',e.last_name) as employee_name,
                    te.note,
                    DATE_FORMAT(te.date_from, \"%M %d %Y %r\") as date_from,
                    DATE_FORMAT(te.date_to, \"%M %d %Y %r\") as date_to,
                    TIMESTAMPDIFF(HOUR, te.date_from, te.date_to) as hour_diff
                    FROM ticket_entries te
                    INNER JOIN employees e on te.employee_id = e.employee_id
                    INNER JOIN tickets t on te.ticket_id = t.ticket_id
                WHERE
                    t.active = 1
                    and ( CAST( te.date_from AS DATE ) = '$date_from' and CAST( te.date_to AS DATE ) = '$date_to' )";
    }
}