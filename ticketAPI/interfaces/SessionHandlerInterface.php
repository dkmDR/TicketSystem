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
interface SessionHandlerInterface
{
    public function close();

    public function destroy($session_id);

    public function gc($maxlifetime);

    public function open($save_path, $name);

    public function read($session_id);

    public function write($session_id, $session_data);
}