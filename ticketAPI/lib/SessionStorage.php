<?php

namespace lib;

use interfaces\SessionHandlerInterface;
use lib\Config;

class SessionStorage implements SessionHandlerInterface
{

    private $_reference_db = NULL//            $_entity_id = 0
    ;

    /**
     *
     * @param Idatabase $reference_db
     */

    public function __construct()
    {

        $this->_reference_db = \Route::getRefDb();

        $this->register();

    }

    /**
     *
     * @param string $entity_id entity references on database
     */
//    public function setEntity( $entity_id )
//    {
//            $this->_entity_id = $entity_id;
//    }

    /**
     * Registers methods for Session
     */

    public function register()
    {
        session_set_save_handler
        (
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
    }

    public function close()
    {
        //echo '<br/>Closing...';                
    }

    public function destroy($session_id)
    {
        $this->_reference_db->query("DELETE FROM " . Config::$_DATA_SESSION . " WHERE " . Config::$_SESSION_ID . "='$session_id'");
        echo $this->_reference_db->rowAffect();
    }

    public function gc($maxlifetime)
    {
        //echo '<br/>GCing...: ' . $maxlifetime;
    }

    public function open($save_path, $name)
    {
        //echo '<br/>Opening...: ' . $save_path . ' name: ' . $name;              
    }

    /**
     * read session
     * @param string $session_id
     * @return string
     */

    public function read($session_id)
    {
        //echo " writing " .$session_id;
        //Verify session id

        $result = $this->_reference_db->exec("SELECT count(" . Config::$_SERIAL_ID . ") as has FROM " . Config::$_DATA_SESSION . " WHERE " . Config::$_SESSION_ID . " = '$session_id'");

        if ($result[0]["has"] < 1) {
            $res = $this->_reference_db->insert(
                array(
                    Config::$_SESSION_ID => $session_id
                ),
                Config::$_DATA_SESSION);

        }

        $result = $this->_reference_db->exec("SELECT " . Config::$_SESSION_DATA . " FROM " . Config::$_DATA_SESSION . " WHERE " . Config::$_SESSION_ID . " = '$session_id'");

        if (!empty($result[0][Config::$_SESSION_DATA])) {
            return $result[0][Config::$_SESSION_DATA];
        }
        return '';
        //return $result;
    }

    /**
     * write session
     * @param string $session_id
     * @param string $session_data
     */

    public function write($session_id, $session_data)
    {
        //echo '<br/>write...: ' . $session_id . ' data: ' . $session_data;
        $result = $this->_reference_db->exec("SELECT " . Config::$_SESSION_DATA . " FROM " . Config::$_DATA_SESSION . " WHERE " . Config::$_SESSION_ID . " = '$session_id'");

        $res = $this->_reference_db->update(array(
            Config::$_SESSION_DATA => $session_data
        ), Config::$_DATA_SESSION, Config::$_SESSION_ID . " = '" . $session_id . "'");
    }

}
