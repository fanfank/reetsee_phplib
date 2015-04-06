<?php
/*
 * @author xuruiqi
 * @date   20141026
 * @desc   a simple lib for log
 */
class Reetsee_Log {
    const LOG_LEVEL_DEBUG   = 1;
    const LOG_LEVEL_NOTICE  = 2;
    const LOG_LEVEL_WARNING = 3;
    const LOG_LEVEL_FATAL   = 4;

    const LOG_TYPE_NORMAL = 0;
    const LOG_TYPE_WF     = 1;

    //$_arrModule2Fd[mod name][0] : notice and debug log's fd
    //$_arrModule2Fd[mod name][1] : warning and fatal log's fd
    static private $__arrModule2Fd = array();

    //$_arrModule2Fd[mod name][0] : notice and debug log's log path
    //$_arrModule2Fd[mod name][1] : warning and fatal log's log path
    static private $__arrModule2LogPath = array();

    public function __construct() {

    }

    public function error($str) {
        echo $str;
    }

    static public function debug($strMsg, $intTraceLevel = 0) {
        self::_print(self::LOG_LEVEL_DEBUG, $strMsg, $intTraceLevel);
    }

    static public function notice($strMsg, $intTraceLevel = 0) {
        self::_print(self::LOG_LEVEL_NOTICE, $strMsg, $intTraceLevel);
    }

    static public function warning($strMsg, $intTraceLevel = 0) {
        self::_print(self::LOG_LEVEL_WARNING, $strMsg, $intTraceLevel);
    }

    static public function fatal($strMsg, $intTraceLevel = 0) {
        self::_print(self::LOG_LEVEL_FATAL, $strMsg, $intTraceLevel);
    }

    static protected function _print($intLogLevel, $strMsg, $intTraceLevel = 0) {
        if (!isset($_SERVER['module_log_level'])) {
            $_SERVER['module_log_level'] = self::LOG_LEVEL_NOTICE;
        }

        if ($_SERVER['module_log_level'] > $intLogLevel || 
                !isset($_SERVER['MODULE'])) {
            return;
        }

        $strMsg = strval($strMsg);

        $arrTrace = debug_backtrace();
        $intDepth = 2 + $intTraceLevel;
        $intTraceDepth = count($arrTrace);
        if ($intDepth > $intTraceDepth) {
            $intDepth = $intTraceDepth;
        }
        $arrTargetTrace = $arrTrace[$intDepth];
        unset($arrTrace);
        if (isset($arrTargetTrace['file'])) {
            $arrTargetTrace['file'] = basename($arrTargetTrace['file']);
        }

        //不同级别的日志收敛到这里处理
        switch($intLogLevel) {
            case self::LOG_LEVEL_FATAL:
                $strPrepend = 'Fatal: ';
                $strAppend = "\n";
                break;
            case self::LOG_LEVEL_WARNING:
                $strPrepend = 'Warning: ';
                $strAppend = "\n";
                //$fd = self::_getLogFd(self::LOG_LEVEL_WARNING);
                break;
            case self::LOG_LEVEL_NOTICE:
                $strPrepend = 'Notice: ';
                $strAppend = "\n";
                //$fd = self::_getLogFd(self::LOG_LEVEL_NOTICE);
                break;
            case self::LOG_LEVEL_DEBUG:
                $strPrepend = 'Debug: ';
                $strAppend = "\n";
                //$fd = self::_getLogFd(self::LOG_LEVEL_DEBUG);
                break;
        }
        //$fd = self::_getLogFd($intLogLevel);
        $strLogPath = self::_getLogPath($intLogLevel);

        $strPrepend = strval(@date("Y-m-d H:i:s")) . 
                " {$arrTargetTrace['file']} {$arrTargetTrace['class']} {$arrTargetTrace['function']} {$arrTargetTrace['line']} " . 
                $strPrepend . 
                ' ';

        $strMsg = $strPrepend . $strMsg . $strAppend;

        /*
        if ($fd != 0 && $fd != false && $fd != NULL) {
            fwrite($fd, $strMsg, 512);
        }
        */

        if (!empty($strLogPath) && 0 !== strlen($strLogPath)) {
            file_put_contents($strLogPath, $strMsg, FILE_APPEND);
        }
    }

    static protected function _getLogPath($intLogLevel = self::LOG_LEVEL_DEBUG) {
        $intLogType = 0;
        if ($intLogLevel <= self::LOG_LEVEL_NOTICE) {
            $intLogType = self::LOG_TYPE_NORMAL;
            $strLogSuffix = '';

        } else if ($intLogLevel <= self::LOG_LEVEL_FATAL) {
            $intLogType = self::LOG_TYPE_WF;
            $strLogSuffix = '.wf';

        } else {
            return false;
        }

        if (!isset(self::$__arrModule2Fd[MODULE][$intLogType])) {
            self::$__arrModule2Fd[MODULE][$intLogType] = 
                    ROOT_PATH . '/log/' . MODULE . '/module.log' . $strLogSuffix;
        }        
        $strLogPath = self::$__arrModule2Fd[MODULE][$intLogType];
        $strDir     = dirname($strLogPath);

        if (!is_dir($strDir) && !mkdir($strDir, 0755, true)) {
            return false;
        }

        return $strLogPath;
    }

    /*
    static protected function _getLogFd($intLogLevel = self::LOG_LEVEL_DEBUG) {
        if ($intLogLevel <= self::LOG_LEVEL_NOTICE) {
            $intFdIdx = 0;
            $strLogSuffix = '';

        } else if ($intLogLevel <= self::LOG_LEVEL_FATAL) {
            $intFdIdx = 1;
            $strLogSuffix = '.wf';

        } else {
            return false;
        }

        if (!isset(self::$__arrModule2Fd[MODULE][$intFdIdx]) || 
                0 == self::$__arrModule2Fd[MODULE][$intFdIdx] ||
                false == self::$__arrModule2Fd[MODULE][$intFdIdx]) {

            self::$__arrModule2Fd[MODULE][$intFdIdx] = 
                        fopen(ROOT_PATH . '/log/' . MODULE . '/module.log' . $strLogSuffix);
        }

        return self::$__arrModule2Fd[MODULE][$intFdIdx];
    }
    */
}

