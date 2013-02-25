<?php
include_once "config.php";

class MysqlConnector{
    public static function getMysqli() {
        $mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
        
        if ($mysqli->connect_error) {
            error_log($mysqli->connect_error);
        }

        return $mysqli;
    } 

    public static function queryStatic($query) {
        $mysqli = self::getMysqli();
        $result = $mysqli->query($query);
        $mysqli->close();
        return $result;
    }

    private $_mysqli = null;

    public function mysqli() {
        if (!$this->_mysqli) {
            $this->_mysqli = self::getMysqli();
        }

        return $this->_mysqli;
    }

    public function close() {
        if ($this->_mysqli) {
            $this->_mysqli->close();
            $this->_mysqli = null;
        }
    }

    public function query($query) {
        $mysqli = $this->mysqli();
        $result = $mysqli->query($query);
        return $result;
    }
}