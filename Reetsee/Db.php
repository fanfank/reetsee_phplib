<?php
/*
 * @author xuruiqi
 * @date   20140908
 * @desc   A simple lib for DB manipulation
 */
class Reetsee_Db {
    protected static $_arrDb = array();

    function __construct() {}
    function __destruct()  {}

    public function getDb($strDb) {
        return $this->_arrConf[$strDb];
    }

    public static function initDb($strDb, $strHost = '127.0.0.1', $intPort = 3306, $strUser = 'root', $strPassword = '123abc', $strCharset = 'utf8') {
        if (NULL !== self::getDb($strDb)) {
            return $this->_arrConf[$strDb];
        }

        $db = new Reetsee_Db_Db();
        if (!$db->initDb($strDb, $strHost, $intPort, $strUser, $strPassword, $strCharset)) {
            return false;
        }

        $this->_arrDb[$strDb] = $db;
        return $this->_arrDb[$strDb];
    }
}
