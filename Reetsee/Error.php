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

    //error messages
    protected static $_arrErrcode2Errmsg = array(
        self::ERR_SUCCESS       => 'Success',    
        self::ERR_INVALID_FIELD => 'Invalid field in array',
        self::ERR_FIELD_NOT_SET => 'Accessing a non-set field',
    );

    public static function getErrmsg($errcode, $delimiter = '') {
        $strErrmsg = isset(self::$_arrErrcode2Errmsg[$errcode]) ?
                self::$_arrErrcode2Errmsg[$errcode] : 'Unknown error';
        return $strErrmsg . $delimiter;
    }
}