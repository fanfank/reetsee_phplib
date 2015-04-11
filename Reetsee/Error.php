<?php
/*
 * @author xuruiqi
 * @date   20150406
 * @copyright reetsee.com
 */     
class Reetsee_Error {
    //error codes
    const ERR_SUCCESS = 0;
    const ERR_INVALID_FIELD = 1000;
    const ERR_FIELD_NOT_SET = 1001;
    const ERR_SERVICE_ERROR = 1002;
    const ERR_UI_INPUT_ERROR = 1003;
    const ERR_GET_DB_FAIL    = 1004;
    const ERR_DB_ERROR       = 1005;

    //error messages
    protected static $_arrErrcode2Errmsg = array(
        self::ERR_SUCCESS       => 'Success',    
        self::ERR_INVALID_FIELD => 'Invalid field in array',
        self::ERR_FIELD_NOT_SET => 'Accessing a non-set field',
        self::ERR_SERVICE_ERROR => 'service error',
        self::ERR_UI_INPUT_ERROR => 'UI input error',
        self::ERR_GET_DB_FAIL    => 'Get database error',
        self::ERR_DB_ERROR       => 'Db exec error',
    );

    public static function getErrmsg($errcode, $delimiter = '') {
        $strErrmsg = isset(self::$_arrErrcode2Errmsg[$errcode]) ?
                self::$_arrErrcode2Errmsg[$errcode] : 'Unknown error';
        return $strErrmsg . $delimiter;
    }
}
