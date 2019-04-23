<?php

namespace interfaces;

//This file cannot be accessed from browser
defined('_EXEC_APP') or die('Ups! access not allowed');

/**
 * PHP version 5.4
 *
 * LICENSE: This source file is subject to the MIT license
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.
 *
 * @category   Interface
 * @package    interfaces
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 1.0
 */
interface Idatabase
{
    /**
     * connect to database
     */
    public function connect();

    /**
     * begin transaction on database
     */
    public function beginTransaction();

    /**
     * commit transaction on database
     */
    public function commitTransaction();

    /**
     * rollback transaction
     */
    public function rollbackTransaction($_EXCEPTION_MSG = 'Rollback executing...', $_THROW_EXCEPTION = TRUE);

    /**
     * close the connection to the database
     */
    public function closeConnection();

    /**
     * execute query in database
     * @param string $query sentence sql
     */
    public function query($query);

    /**
     * call this method after call setQuery
     */
    public function exec();

    /**
     * get list count by records
     * @param array $resource resource database
     */
    public function getListCount($resource = NULL);

    /**
     * set query sentence
     * @param string define sql sentence
     */
    public function setQuery($query);

    /**
     * execute query in database and return resource
     * @param string $query
     * @return array object resource database
     */
    public function getObjectList($query);

    /**
     * execute query in database and return one resource
     * @param string $query
     * @return stdClass resource database
     */
    public function getRowObjectList($query);

    /**
     * execute query in database and return resource
     * @param string $query
     * @return array resource database
     */
    public function getArrayList($query);

    /**
     * execute query in database and return one resource
     * @param string $query
     * @return array resource database
     */
    public function getRowArrayList($query);

    /**
     *
     * @param array key $values
     * @param string $table
     * @param string $id return key
     */
    public function insert(array $values, $table, $id);

    /**
     *
     * @param array $values
     * @param string $table
     * @param string $id
     * @return int
     */
    public function insertObject(\stdClass $values, $table, $id = '');

    /**
     * get sql insert
     * @param array $values
     * @param string $table
     * @return string
     */
    public function getInsert($values, $table);

    /**
     * update in database
     * @param array key $values
     * @param string $table
     * @param string $filters example attribute = value
     */
    public function update(array $values, $table, $filters);

    /**
     *
     * @param \stdClass $values
     * @param string $table
     * @param string $filters
     * @return int
     */
    public function updateObject(\stdClass $values, $table, $filters);

    /**
     * get database link
     */
    public function getLink();

    /**
     * get affected row
     * @param resource $resource database resource
     */
    public function rowAffect($resource = NULL);

    /**
     * Returns the escaped string
     * @param string $escapestring The string to be escaped. Characters encoded are NUL (ASCII 0), \n, \r, \, ', ", and Control-Z.
     */
    public function Escape($escapestring);

}