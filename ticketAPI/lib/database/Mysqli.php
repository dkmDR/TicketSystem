<?php

namespace lib\database;

//This file cannot be accessed from browser
defined('_EXEC_APP') or die('Ups! access not allowed');

use interfaces\Idatabase;
use lib\Config;
use Factory;
use Exception;
use stdClass;

/**
 *
 * PHP version 5.4
 *
 * LICENSE: This source file is subject to the MIT license
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.
 *
 * @category   Database Driver
 * @package    lib\database
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 1.0
 *
 * @property mixed $_link mysqli connection
 * @property string $_query sql sentence
 * @property mixed $_resource database resource
 * @property mixed $db database object
 */
class Mysqli extends \lib\database\Config implements Idatabase
{
    /**
     *
     * @var mixed
     */
    private $_link = NULL;

    /**
     *
     * @var string
     */
    private $_query = "";

    /**
     *
     * @var mixed
     */
    private $_resource = NULL;

    /**
     * @var mixed
     */
    public static $db = NULL;

    /**
     *
     * @param boolean $_DEFAULT_CONNECTION_
     */

    function __construct($_DEFAULT_CONNECTION_ = TRUE)
    {

        if ($_DEFAULT_CONNECTION_) {
            $this->connect();
        }

    }

    /**
     * get Link
     * @return mixed
     */
    public function getLink()
    {
        return $this->_link;
    }

    /**
     * Set properties or credentials
     * @param stdClass $properties {
     *      server : server's name,
     *      user : server user,
     *      pass : server password,
     *      db : server database,
     *      port : server port by dbo
     * }
     * @return void
     */
    public function setProperties(stdClass $properties)
    {

        $std = new stdClass();

        $std->server = $properties->server;

        $std->user = $properties->user;

        $std->pass = $properties->pass;

        $std->db = $properties->db;

        $std->port = $properties->port;

        parent::set($std);
    }

    /**
     * connect to database
     * @throws \Exception
     * @return void|NULL
     */
    public function connect()
    {

        if (Config::$_USE_DB === FALSE) {
            return NULL;
        }

        if (self::$db != NULL) {
            $this->_link = self::$db;
            return NULL;
        }

        $this->_link = mysqli_connect($this->_SERVER_, $this->_USER_, $this->_PASS_, $this->_DB_);

        if (!$this->_link) {
            $msg = "Error trying connect to database";
            //logger error
            Factory::loggerError($msg);

            throw new Exception($msg);
        }

        self::$db = $this->_link;
    }

    /**
     * execute query
     * @param string $query
     * @throws Exception
     * @return mixed|NULL
     */
    public function query($query)
    {

        if (Config::$_USE_DB === FALSE) {
            return NULL;
        }

        $result = mysqli_query($this->_link, $query);

        if ($result) {
            return $result;
        }

        $msg = " Sql Error -> $query ";
        Factory::loggerError($msg);

        if (!Config::$_DEVELOPING_) {
            $msg = " Something went wrong in the request to the database ";
        }

        throw new Exception($msg);
    }

    /**
     * @param string $query sql sentence
     * @return Mysqli
     */
    public function setQuery($query)
    {
        $this->_query = $query;
        return $this;
    }

    /**
     * call this method after call setQuery
     * @return Mysqli
     */
    public function exec()
    {
        $this->_resource = $this->getObjectList($this->_query);
        return $this;
    }

    /**
     * get list count by records
     * @param array $resource database resource
     * @return integer
     */
    public function getListCount($resource = NULL)
    {
        $res = ($resource == NULL) ? $this->_resource : $resource;
        return count($res);
    }

    /**
     * begin transaction
     * @return void
     */
    public function beginTransaction()
    {
        $this->query("START TRANSACTION");
    }

    /**
     * commit transaction
     * @return void
     */
    public function commitTransaction()
    {
        $this->query("COMMIT");
    }

    /**
     * rollback transaction
     * @throws Exception
     * @return void
     */
    public function rollbackTransaction($_EXCEPTION_MSG = 'Rollback executing...', $_THROW_EXCEPTION = TRUE)
    {
        $this->query("ROLLBACK");
        if ($_THROW_EXCEPTION) {
            throw new Exception($_EXCEPTION_MSG);
        }
    }

    /**
     * close connection
     * @return boolean
     */
    public function closeConnection()
    {
        return mysqli_close($this->_link);
    }

    /**
     * execute query in database and return resource
     * @param string $query
     * @return array database resource object
     */
    public function getObjectList($query)
    {

        $returning = array();

        $result = $this->query($query);

        if (is_object($result)) {
            while ($row = mysqli_fetch_object($result)) {
                $returning[] = $row;
            }
        }

        return $returning;

    }

    /**
     * execute query in database and return one resource
     * @param string $query
     * @return stdClass database resource object
     */
    public function getRowObjectList($query)
    {

        $resource = $this->getObjectList($query);

        if (count($resource) > 0) {
            return $resource[0];
        }
    }

    /**
     * execute query in database and return resource
     * @param string $query
     * @return array database resource
     */
    public function getArrayList($query)
    {

        $returning = array();
        $result = $this->query($query);

        if (is_object($result)) {
            while ($row = mysqli_fetch_array($result)) {
                $returning[] = $row;
            }
        }

        return $returning;

    }

    /**
     * execute query in database and return one resource
     * @param string $query
     * @return array database resource
     */
    public function getRowArrayList($query)
    {

        $resource = $this->getArrayList($query);

        if (count($resource) > 0) {
            return $resource[0];
        }

    }

    /**
     *
     * @param \stdClass $values
     * @param string $table
     * @param string $filters
     * @return int
     */
    public function updateObject(stdClass $values, $table, $filters)
    {

        $update = "UPDATE %s SET %s WHERE " . $filters;

        $keys = "";
        foreach ($values as $key => $value) {
            $keys .= $key . "= '" . $value . "',";
        }
        $update = sprintf($update, $table, trim($keys, ","));

        $this->query($update);
        $affect = $this->rowAffect();

        if ($affect < 0) {

            $msg = " Sql Error -> $update ";
            //logger
            Factory::loggerError($msg);
            if (!Config::$_DEVELOPING_) {
                $msg = " Something went wrong in the request to the database ";
            }

            throw new Exception($msg);

        }
        return $affect;

    }

    /**
     *
     * @param array $values
     * @param string $table
     * @param string $filters
     * @return int
     */
    public function update(array $values, $table, $filters)
    {

        $update = "UPDATE %s SET %s WHERE " . $filters;

        $keys = "";
        foreach ($values as $key => $value) {
            $keys .= $key . "= '" . $value . "',";
        }
        $update = sprintf($update, $table, trim($keys, ","));

        $this->query($update);
        $affect = $this->rowAffect();

        if ($affect < 0) {

            $msg = " Sql Error -> $update ";
            //logger
            Factory::loggerError($msg);
            if (!Config::$_DEVELOPING_) {
                $msg = " Something went wrong in the request to the database ";
            }

            throw new Exception($msg);

        }
        return $affect;

    }

    /**
     *
     * @param array $values
     * @param string $table
     * @param string $id
     * @return int
     */
    public function insert(array $values, $table, $id = '')
    {

        $insert = "INSERT INTO %s(%s) VALUES(%s)";

        $keys = "";
        $valueStr = "";

        foreach ($values as $key => $value) {
            $keys .= $key . ',';
            $valueStr .= "'" . $value . "',";
        }

        $insert = sprintf($insert, $table, trim($keys, ","), trim($valueStr, ","));

        $this->query($insert);
        $affect = mysqli_insert_id($this->_link);

        if ($affect < 1) {

            $msg = " Sql Error -> $insert ";
            //logger
            Factory::loggerError($msg);
            if (!Config::$_DEVELOPING_) {
                $msg = " Something went wrong in the request to the database ";
            }

            throw new Exception($msg);

        }

        return $affect;

    }

    /**
     *
     * @param stdClass $values
     * @param string $table
     * @param string $id
     * @return int
     */
    public function insertObject(\stdClass $values, $table, $id = '')
    {

        $insert = "INSERT INTO %s(%s) VALUES(%s)";

        $keys = "";
        $valueStr = "";

        foreach ($values as $key => $value) {
            $keys .= $key . ',';
            $valueStr .= "'" . $value . "',";
        }

        $insert = sprintf($insert, $table, trim($keys, ","), trim($valueStr, ","));

        $this->query($insert);
        $affect = mysqli_insert_id($this->_link);

        if ($affect < 1) {

            $msg = " Sql Error -> $insert ";
            //logger
            Factory::loggerError($msg);
            if (!Config::$_DEVELOPING_) {
                $msg = " Something went wrong in the request to the database ";
            }

            throw new \Exception($msg);

        }

        return $affect;

    }

    /**
     *
     * @param array $values
     * @param string $table
     * @return string
     */
    public function getInsert($values, $table)
    {

        $insert = "INSERT INTO %s(%s) VALUES(%s)";

        $keys = "";
        $valueStr = "";

        foreach ($values as $key => $value) {
            $keys .= $key . ',';
            $valueStr .= "'" . $value . "',";
        }

        $insert = sprintf($insert, $table, trim($keys, ","), trim($valueStr, ","));

        return $insert;

    }

    /**
     * row affect
     * @param mixed $resource
     * @return int
     */
    public function rowAffect($resource = NULL)
    {
        return mysqli_affected_rows($this->_link);
    }

    /**
     * Returns the escaped string
     * @param string $escapestring Required. The string to be escaped. Characters encoded are NUL (ASCII 0), \n, \r, \, ', ", and Control-Z.
     */
    public function Escape($escapestring)
    {
        return mysqli_real_escape_string($this->_link, $escapestring);
    }

}

?>