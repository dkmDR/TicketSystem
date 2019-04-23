<?php

namespace apps\Miscellaneous\Sql;

/**
 * Class Miscellaneous
 * @package apps\Miscellaneous\Sql
 */
class Miscellaneous
{
    /**
     * @return string
     */
    public static function getStatus(){
        return "select status_id as value, status_des as label from status where type='status' order by status_id";
    }

    /**
     * @return string
     */
    public static function getState(){
        return "select status_id as value, status_des as label from status where type='ticket' order by status_id";
    }
}