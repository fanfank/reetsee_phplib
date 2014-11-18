<?php
/*
 * @author xuruiqi
 * @date   20141115
 * @desc   Http类
 */
class Reetsee_Http {
    public static function get($strField, $default = NULL) {
        $value = NULL;

        if (isset($_GET[$strField])) {
            $value = $_GET[$strField];
        }

        if (NULL === $value) {
            $value = $_POST[$strField];
        }

        if (NULL === $value) {
            $value = $default;
        }

        return $value;
    }
}
