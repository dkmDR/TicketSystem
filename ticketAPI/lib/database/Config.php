<?php

namespace lib\database;

defined('_EXEC_APP') or die('Ups! access not allowed');

/**
 * PHP version 5.4
 *
 * LICENSE: This source file is subject to the MIT license
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.
 *
 * @category   Database
 * @package    lib\database
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 1.0
 */
class Config
{
    /**
     * server database
     * @var string
     */
    protected $_SERVER_ = "localhost";

    /**
     * user database
     * @var string
     */
    protected $_USER_ = "root";

    /**
     * pass database
     * @var string
     */
    protected $_PASS_ = "";

    /**
     * database connection
     * @var string
     */
    protected $_DB_ = "ticketsystem";

    /**
     *
     * @var int port database
     */
    protected $_PORT_ = 5432;

    /**
     * set configuration to database
     * @param \stdClass $std
     */
    protected function set(\stdClass $std)
    {

        $this->_SERVER_ = $std->server;

        $this->_USER_ = $std->user;

        $this->_PASS_ = $std->pass;

        $this->_DB_ = $std->db;

        $this->_PORT_ = $std->port;

    }

}
