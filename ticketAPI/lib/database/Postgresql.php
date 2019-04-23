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
 * @property mixed $_link postgres connection
 * @property string $_query sql sentence
 * @property mixed $_resource database resource
 * @property mixed $db database object
 */
class Postgresql extends \lib\database\Config implements Idatabase
{
    /**
     *
     * @var mixed
     */
    private $_link;

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
     * @param \stdClass $properties {
     *      server : server's name,
     *      user : server user,
     *      pass : server password,
     *      db : server database,
     *      port : server port by dbo
     * }
     * @return void
     */
    public function setProperties(\stdClass $properties)
    {

        $std = new \stdClass();

        $std->server = $properties->server;

        $std->user = $properties->user;

        $std->pass = $properties->pass;

        $std->db = $properties->db;

        $std->port = $properties->port;

        parent::set($std);
    }

    /**
     * Connect to Postgres
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

        $this->_link = pg_connect("host=$this->_SERVER_ port=$this->_PORT_ dbname=$this->_DB_ user=$this->_USER_ password=$this->_PASS_");

        if ($this->_link === FALSE) {

            $msg = "Error trying connect to database";
            //logger error
            Factory::loggerError($msg);

            throw new \Exception($msg);

        }
    }

    /**
     * execute query in database
     * @param string $query sql sentence
     * @return mixed|NULL
     */
    public function query($query)
    {

        if (\lib\Config::$_USE_DB === FALSE) {
            return NULL;
        }

        $result = pg_query($this->_link, $query);

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
     * begin trasaction
     * @return void
     */
    public function beginTransaction()
    {
        $this->query("BEGIN WORK");
    }

    /**
     * Commit transaction
     * @return void
     */
    public function commitTransaction()
    {
        $this->query("COMMIT");
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

        if (is_resource($result)) {
            while ($row = pg_fetch_object($result)) {
                $returning[] = $row;
            }
        }

        return $returning;
    }

    /**
     * execute query in database and return one resource
     * @param string $query
     * @return stdClass database resource
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

        if (is_resource($result)) {
            while ($row = pg_fetch_array($result)) {
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
     * get sql insert
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
     *
     * @param array $values
     * @param string $table
     * @param string $id
     * @return boolean
     */
    public function insert(array $values, $table, $id)
    {

        $insert = "INSERT INTO %s(%s) VALUES(%s)";

        $keys = "";
        $valueStr = "";

        foreach ($values as $key => $value) {
            $keys .= $key . ',';
            $valueStr .= "'" . $value . "',";
        }

        $insert = sprintf($insert, $table, trim($keys, ","), trim($valueStr, ","));
        $insert .= " RETURNING $id";

        $resource = $this->query($insert);

        $object = pg_fetch_object($resource);

        if (empty($object)) {

            $msg = " Sql Error -> $insert ";
            //logger
            Factory::loggerError($msg);
            if (!Config::$_DEVELOPING_) {
                $msg = " Something went wrong in the request to the database ";
            }

            throw new Exception($msg);

        }

        return $object;

    }

    /**
     *
     * @param stdClass $values
     * @param string $table
     * @param string $id
     * @return int
     */
    public function insertObject(\stdClass $values, $table, $id = "")
    {

        $insert = "INSERT INTO %s(%s) VALUES(%s)";

        $keys = "";
        $valueStr = "";

        foreach ($values as $key => $value) {
            $keys .= $key . ',';
            $valueStr .= "'" . $value . "',";
        }

        $insert = sprintf($insert, $table, trim($keys, ","), trim($valueStr, ","));

        if (!empty($id)) {
            $insert .= " RETURNING $id";

            $resource = $this->query($insert);
            $object = pg_fetch_object($resource);

            return $object->$id;
        }

        $resource = $this->query($insert);

        $affect = $this->rowAffect($resource);

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
     * @param string define sql sentence
     * @return Postgresql
     */
    public function setQuery($query)
    {
        $this->_query = $query;
        return $this;
    }

    /**
     * call this method after call setQuery
     * @return Postgresql
     */
    public function exec()
    {
        $this->_resource = $this->getObjectList($this->_query);
        return $this;
    }

    /**
     * get list count by records
     * @param array $resource resource database
     * @return integer
     */
    public function getListCount($resource = NULL)
    {
        $res = ($resource == NULL) ? $this->_resource : $resource;
        return count($res);
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
            throw new \Exception($_EXCEPTION_MSG);
        }
    }

    /**
     * get affected row
     * @param resource $resource database resource
     * @return integer
     */
    public function rowAffect($resource = NULL)
    {
        return pg_affected_rows($resource);
    }

    /**
     * update in database
     * @param array $values
     * @param string $table
     * @param string $filters example attribute = value
     * @return integer
     */
    public function update(array $values, $table, $filters)
    {

        $update = "UPDATE %s SET %s WHERE " . $filters;

        $keys = "";
        foreach ($values as $key => $value) {
            $keys .= $key . "= '" . $value . "',";
        }
        $update = sprintf($update, $table, trim($keys, ","));

        $resource = $this->query($update);

        $affect = $this->rowAffect($resource);

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
     * update in database
     * @param stdClass $values
     * @param string $table
     * @param string $filters example attribute = value
     * @return integer
     */

    public function updateObject(\stdClass $values, $table, $filters)
    {

        $update = "UPDATE %s SET %s WHERE " . $filters;

        $keys = "";
        foreach ($values as $key => $value) {
            $keys .= $key . "= '" . $value . "',";
        }
        $update = sprintf($update, $table, trim($keys, ","));

        $resource = $this->query($update);

        $affect = $this->rowAffect($resource);

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
     * close the connection to the database
     * @return boolean
     */
    public function closeConnection()
    {
        return pg_close($this->_link);
    }

    /**
     *
     * @param string $escapestring a string containing text to be escaped.
     * @return string A string containing the escaped data.
     */
    public function Escape($escapestring)
    {
        return pg_escape_string($this->_link, $escapestring);
    }

}
