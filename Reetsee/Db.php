<?php
/*
 * @author xuruiqi
 * @date   20140908
 * @desc   A simple lib for DB manipulation
 */
class Reetsee_Db {
    protected $_arrCurrentConf   = array();
    protected $_objCurrentDb     = NULL;
    protected $_lastAffectedRows = 0;
    protected $_lastInsertId     = NULL;
    protected $_lastSql          = '';

    protected $_arrDb     = array();

    function __destruct() {
        foreach ($this->_arrDb as $mysqli) {
            $mysqli->close();
        }
    }

    /**
     * @author xuruiqi
     * @param
     *      string $table
     *      array  $conds
     *      array  $options
     *      string $appends
     *      array  $arrExtra
     * @desc 数据库delete接口
     */
    public function delete($table, $conds, $appends = NULL, $options = NULL, $arrExtra = NULL) {
        $sql = Reetsee_Db_Sql::getSqlDelete($table, $conds, $appends, $options, $arrExtra);
        if (empty($sql)) {
            $this->log("Reetsee_Db_Sql::getSqlDelete Failed, table=[" . serialize($table) . "], conds=[" . serialize($conds) . "], options=[" . serialize($options) . "], appends=[" . serialize($appends) . "], arrExtra=[" . serialize($arrExtra) . "]");
            return FALSE;
        }
        return $this->query($sql);
    }

    public function getDb($strDb, $strCharset = 'utf8', $strHost = '127.0.0.1', $intPort = 3306, $strUser = 'root', $strPassword = '123abc') {
        //查询是否已经有连接
        if (isset($this->_arrDb[$strDb]) && $this->_arrDb[$strDb]->ping()) {
            return $this->_arrDb[$strDb];
        }

        $mysqli = new mysqli($strHost, $strUser, $strPassword, $strDb, $intPort);
        if ($mysqli->connect_errno) {
            return FALSE;
        }

        //设置字符集
        if (!$mysqli->set_charset($strCharset)) {
            $mysqli->close();
            return FALSE;
        } else {

        }

        $this->_arrDb[$strDb] = $mysqli;
        $this->_objCurrentDb = $this->_arrDb[$strDb];
        $this->_arrCurrentConf = array(
            'host'     => $strHost,
            'port'     => $intPort,
            'db_name'  => $strDb,    
            'charset'  => $strCharset,
            'user'     => '<invisible>',
            'password' => '<invisible>',
        );
        return $this->_arrDb[$strDb];
    }

    public function getLastId() {
        return $this->_lastInsertId;
    }

    public function initDb($strDb, $strCharset = 'utf8', $strHost = '127.0.0.1', $intPort = 3306, $strUser = 'root', $strPassword = '123abc') {
        return $this->getDb($strDb, $strCharset, $strHost, $intPort, $strUser, $strPassword);
        /*
        $mysqli = new mysqli($strHost, $strUser, $strPassword, $strDb, $intPort);
        if ($mysqli->connect_errno) {
            return FALSE;
        }

        //设置字符集
        if (!$mysqli->set_charset($strCharset)) {
            $mysqli->close();
            return FALSE;
        } else {

        }

        $this->_arrDb[$strDb] = $mysqli;
        $this->_objCurrentDb = $this->_arrDb[$strDb];
        $this->_arrCurrentConf = array(
            'host'     => $strHost,
            'port'     => $intPort,
            'db_name'  => $strDb,    
            'charset'  => $strCharset,
            'user'     => '<invisible>',
            'password' => '<invisible>',
        );
        return TRUE;
         */
    }

    /**
     * @author xuruiqi
     * @param
     *      string $table
     *      array  $fields
     *      array  $options
     *      array  $dup
     *      array  $arrExtra
     * @desc 数据库insert接口
     */
    public function insert($table, $fields, $dup = NULL, $options = NULL, $arrExtra = NULL) {
        $sql = Reetsee_Db_Sql::getSqlInsert($table, $fields, $dup, $options, $arrExtra);
        if (empty($sql)) {
            $this->log("Reetsee_Db_Sql::getSqlInsert Failed, table=[" . serialize($table) . "], fields=[" . serialize($fields) . "], options=[" . serialize($options) . "], arrExtra=[" . serialize($arrExtra) . "]");
            return FALSE;
        }
        return $this->query($sql);
    }

    protected function log($log) {
        echo $log;
    }

    public function query($strSql, $mysqli = NULL, $resulttype = MYSQLI_ASSOC) {
        if (NULL === $mysqli) {
            $mysqli = $this->_objCurrentDb;
        }

        $strSql = strval($strSql);
        $this->_lastSql = $strSql;
        $mysqli_res = $mysqli->query($strSql);

        if (NULL === $mysqli_res || is_bool($mysqli_res)) {
            $arrOutput = (TRUE === $mysqli_res) ? TRUE : FALSE;
            if (!$arrOutput) {
                $this->log("sql execution failed. errno=" . $mysqli->errno . ", error=" . $mysqli->error . ", sql=$strSql, conf=[" . serialize($this->_arrCurrentConf) . "]");
            }
        } else {
            if (method_exists('mysqli_result', 'fetch_all')) {
                $arrOutput = $mysqli_res->fetch_all($resulttype);
            } else {
                for(;$row = $mysqli_res->fetch_array($resulttype);) {
                    $arrOutput[] = $row;
                }
            }
            $mysqli_res->free();
        }
        
        $this->_lastInsertId = $mysqli->insert_id;
        $this->_lastAffectedRows = $mysqli->affected_rows;
        return $arrOutput;
    }

    /**
     * @author xuruiqi
     * @param
     *      string $table
     *      array  $fields 
     *      array  $conds
     *      array  $options
     *      string $appends
     *      array  $arrExtra
     * @desc 数据库select接口
     */
    public function select($table, $fields, $conds, $appends = NULL, $options = NULL, $arrExtra = NULL) {
        $sql = Reetsee_Db_Sql::getSqlSelect($table, $fields, $conds, $appends, $options, $arrExtra);
        if (empty($sql)) {
            $this->log("Reetsee_Db_Sql::getSqlSelect Failed, table=[" . serialize($table) . "], fields=[" . serialize($fields) . "], conds=[" . serialize($conds) . "], options=[" . serialize($options) . "], appends=[" . serialize($appends) . "], arrExtra=[" . serialize($arrExtra) . "]");
            return FALSE;
        }
        return $this->query($sql);
    }

    /**
     * @author xuruiqi
     * @param
     *      string $table
     *      array  $fields
     *      array  $conds
     *      array  $options
     *      string $appends
     *      array  $arrExtra
     * @desc 数据库update接口
     */
    public function update($table, $fields, $conds = NULL, $appends = NULL, $options = NULL, $arrExtra = NULL) {
        $sql = Reetsee_Db_Sql::getSqlUpdate($table, $fields, $conds, $appends, $options, $arrExtra);
        if (empty($sql)) {
            $this->log("Reetsee_Db_Sql::getSqlUpdate Failed, table=[" . serialize($table) . "], fields=[" . serialize($fields) . "], conds=[" . serialize($conds) . "], options=[" . serialize($options) . "], appends=[" . serialize($appends) . "], arrExtra=[" . serialize($arrExtra) . "]");
            return FALSE;
        }
        return $this->query($sql);
    }
}
