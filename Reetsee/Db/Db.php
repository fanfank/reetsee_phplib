<?php
/*
 * @author xuruiqi
 * @date   20140908
 * @desc   A simple lib for DB manipulation
 */
class Reetsee_Db_Db {
    private   $_mysql = NULL;
    private   $_arrConf = array();

    public function __construct() {
        $this-_mysql = mysqli_init();
    }

    function __destruct() {
        $this->close();
    }

    public function __get($target) {
        switch ($target) {
            case 'error':
                return $this->_mysql->error;
            case 'errno':
                return $this->_mysql->errno;
            case 'insert_id':
                return $this->_mysql->insert_id;
            case 'affected_rows':
                return $this->_mysql->affected_rows;
            case 'lastSQL':
                return $this->_mysql->lastSQL;
            case 'isConnected':
                return $this->_mysql->ping();
            case 'db':
                return $this->_mysql;
            default:
                return NULL;
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

    public function getDb() {
        //查询是否已经有连接
        if (!$this->isConnected) {
            if (!$this->reconnect()) {
                return NULL;
            }
        }
        return $this;
    }

    public function getLastId() {
        return $this->insert_id;
    }

    public function initDb($strDb, $strHost = '127.0.0.1', $intPort = 3306, $strUser = 'root', $strPassword = '123abc', $strCharset = 'utf8') {
        $this->_arrConf = array(
            'host'     => $strHost,
            'port'     => $intPort,
            'dbname'   => $strDb,    
            'charset'  => $strCharset,
            'username' => $strUser,
            'passwd'   => $strPassword,
        );
        return $this->reconnect();
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

    public function query($strSql, $resulttype = MYSQLI_ASSOC) {
        if (!$this->isConnected) {
            $this->reconnect();
        }

        $strSql     = strval($strSql);
        $mysqli_res = $this->_mysql->query($strSql);

        if (NULL === $mysqli_res || is_bool($mysqli_res)) {
            $arrOutput = (TRUE === $mysqli_res) ? TRUE : FALSE;
            if (!$arrOutput) {
                $this->log("sql execution failed. errno=" . $this->errno . ", error=" . $this->error . ", sql=$strSql");
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

    public function reconnect() {
        if ($this->isConnected) {
            $this->close();
        }

        if (empty($this->_arrConf)) {
            return FALSE;
        }

        $arrConf = $this->_arrConf;
        $bolRes  = $this->_mysql->real_connect(
            $arrConf['host'],   
            $arrConf['username'],
            $arrConf['passwd'],
            $arrConf['dbname'],
            $arrConf['port']
        );
        if (!$bolRes) {
            return FALSE;
        }

        //设置字符集
        if (!empty($arrConf['charset']) && !$this->_mysql->set_charset($arrConf['charset'])) {
            $this->close();
            return FALSE;
        }

        return TRUE;
    }

    public function close() {
        if ($this->isConnected) {
            $this->_mysql->close();
        }
    }
}
