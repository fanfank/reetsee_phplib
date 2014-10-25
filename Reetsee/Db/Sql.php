<?php
/*
 * @author xuruiqi
 * @date   20140908
 * @desc   A simple lib for sql assembling
 */
class Reetsee_Db_Sql {
    //DELETE {OPTION1 OPTION2 .. OPTIONn}
    //  FROM [TABLE]
    //      WHERE conds1='1' AND conds2>'2' AND ... AND condsn<'n'
    //          {ORDER BY field1 LIMIT 10};
    //
    //INSERT {OPTION1 OPTION2 ... OPTIONn} 
    //  INTO [TABLE] (col1, col2, ..., coln) VALUES ('1', '2', ..., 'n') 
    //      {ON DUPLICATE KEY UPDATE col1='1', col2='2', ..., coln='n'};
    //
    //SELECT {OPTION1 OPTION2 ... OPTIONn}
    //  col1, col2, ..., coln
    //      FROM [TABLE]
    //          WHERE conds1='1' AND conds2>'2' AND ... AND condsn<'n'
    //              {ORDER BY field1 LIMIT 10, 20};
    //
    //UPDATE {OPTION1 OPTION2 .. OPTIONn}
    //  [TABLE] SET field1='1', field2='2', ..., fieldn='n'
    //      WHERE conds1='1' AND conds2>'2' AND ... AND condsn<'n'
    //          {ORDER BY field1 LIMIT 10};

    const SQL_PART_KEY     = 1;
    const SQL_PART_VALUE   = 2;
    const SQL_PART_COMBINE = 3;

    public static function getSqlDelete($table, $conds, $apends = NULL, $options = NULL, $arrExtra = NULL) {
        $arrSqls = array("DELETE");

        //options
        if (!empty($options)) {
            $strOptions = self::_getSqlPart($options, self::SQL_PART_KEY);
            $arrSqls[]   = "$strOptions";
        }

        //tables
        $strTables = self::_getSqlPart($table, self::SQL_PART_KEY, ',');
        $arrSqls[]  = "FROM $strTables";

        //conds
        if (!empty($conds)) {
            $strConds  = self::_getSqlPart($conds, self::SQL_PART_COMBINE, ' AND ');
            $arrSqls[] = "WHERE $conds";
        }

        //appends
        if (!empty($appends)) {
            $strAppends = self::_getSqlPart($appends);
            $arrSqls[]  = "$strAppends";
        }
        
        return implode(' ', $arrSqls);
    }

    public static function getSqlInsert($table, $fields, $dup = NULL, $options = NULL, $arrExtra = NULL) {
        $arrSqls = array("INSERT");

        //options
        if (!empty($options)) {
            $strOptions = self::_getSqlPart($options, self::SQL_PART_KEY);
            $arrSqls[]  = "$strOptions";
        }

        //tables
        $strTables = self::_getSqlPart($table, self::SQL_PART_KEY, ',');
        $arrSqls[] = "INTO $strTables";

        //columns and values
        $strCols   = self::_getSqlPart(array_keys($fields), self::SQL_PART_KEY, ',');
        $strValues = self::_getSqlPart(array_values($fields), self::SQL_PART_VALUE, ',');
        $arrSqls[] = "($strColumns) VALUES ($strValues)";

        //dup
        if (!empty($dup)) {
            $strDup    = self::_getSqlPart($dup, self::SQL_PART_COMBINE, ',');
            $arrSqls[] = "ON DUPLICATE KEY UPDATE $strDup";
        }

        return implode(' ', $arrSqls);
    }

    protected static function _getSqlPart($tuples, $type, $seperator) {
        if (!is_array($tuples)) {
            return $tuples;
        }

        $sql = "";
        switch ($type) {
            case self::SQL_PART_KEY:
                foreach ($tuples as &$key) {
                    $key = mysql_escape_string(strval($key));
                }
                $sql = implode($seperator, $tuples);
                break;
            
            case self::SQL_PART_VALUE:
                foreach ($tuples as &$value) {
                    if (is_string($value)) {
                        $value = '\'' . mysql_escape_string($value) . '\'';
                    }
                }
                $sql = implode($seperator, $tuples);
                break;

            case self::SQL_PART_COMBINE:
                $arrSqls = array();
                foreach ($tuples as $key => $value) {
                    $str = mysql_escape_string($key);
                    if (is_string($value)) {
                        $str .= '\'' . mysql_escape_string($value) . '\'';
                    } else if (is_array($value)) {
                        foreach ($value as &$val) {
                            if (is_array($val)) {
                                return false;
                            } else if (is_string($val)) {
                                $val = '\'' . mysql_escape_string($val) . '\'';
                            }
                        }
                        $str .= " (" . implode(',', $value) . ")";
                    } else {
                        $str .= $value;
                    }
                    $arrSqls[] = $str;
                }
                $sql = implode($seperator, $arrSqls);
                break;

            default:
                $sql = false;
                break;
        }

        return $sql;
    }

    protected static function _getSqlPart($tuples, $tuples_type) {
        if (empty($tuples)) {
            return '';
        }

        $sql = '';
        switch ($tuples_type) {
            case self::SQL_PART_SEL: //SELECT 模板
                if (!is_array($tuples) || empty($tuples)) {
                    $sql = FALSE;
                    break;
                }

                $sql .= "SELECT ";
                $arrFields = array();
                foreach ($tuples as $field) {
                    $field = mysql_escape_string($field);
                    $arrFields[] = $field;
                }
                $sql .= implode(', ', $arrFields);
                break;

            case self::SQL_PART_FROM: //FROM 模板
                if (!is_string($tuples) || 0 === strlen($tuples)) {
                    $sql = FALSE;
                    break;
                }

                $tuples = mysql_escape_string($tuples);
                $sql .= "FROM `$tuples`";
                break;

            case self::SQL_PART_WHERE_AND: //WHERE AND 模板
                if (!is_array($tuples) || empty($tuples)) {
                    $sql = FALSE;
                    break;
                }

                $sql .= 'WHERE ';
                $arrAndConds = array();
                foreach ($tuples as $field => $value) {
                    $field = mysql_escape_string($field);
                    $value = mysql_escape_string($value);
                    $arrAndConds[] = "`$field`='$value'";
                }
                $sql .= implode(' AND ', $arrAndConds);
                break;

            case self::SQL_PART_UPDATE: //UPDATE 模板
                if (!is_string($tuples) || 0 === strlen($tuples)) {
                    $sql = FALSE;
                    break;
                }

                $tuples = mysql_escape_string($tuples);
                $sql .= "UPDATE `$tuples` SET";
                break;

            case self::SQL_PART_VALUES: //KEY=VALUE 模板
                if (!is_array($tuples) || empty($tuples)) {
                    $sql = FALSE;
                    break;
                }

                $sql .= "";
                $arrKeyValues = array();
                foreach ($tuples as $field => $value) {
                    $field = mysql_escape_string($field);
                    $value = mysql_escape_string($value);
                    $arrKeyValues[] = "`$field`='$value'";
                }
                $sql .= implode(', ', $arrKeyValues);
                break;

            case self::SQL_PART_DUP: //ON DUPLICATE 模板
                if (empty($tuples)) {
                    $sql = '';
                }
                $values_sql = self::_getSqlPart($tuples, self::SQL_PART_VALUES);
                if (FALSE === $values_sql) {
                    $sql = FALSE;
                    break;
                }

                $sql = "ON DUPLICATE KEY UPDATE $values_sql";
                break;

            case self::SQL_PART_INSERT: //INSERT 模板
                if (!is_string($tuples) || empty($tuples)) {
                    return FALSE;
                }
                $sql .= "INSERT INTO $tuples";
                break;

            case self::SQL_PART_INSERT_VALUES: //INSERT VALUES 模板
                if (!is_array($tuples) || empty($tuples)) {
                    return FALSE;
                }

                $sql .= "";
                $arrFields = array();
                $arrValues = array();
                foreach ($tuples as $field => $value) {
                    $field = mysql_escape_string($field);
                    $value = mysql_escape_string($value);
                    $arrFields[] = "`$field`";
                    $arrValues[] = "'$value'";
                }
                $sql .= '(' . implode(',', $arrFields) . ') VALUES (' . implode(',', $arrValues) . ')';

                break;

            default:
                $sql = FALSE;
                break;

        }
        return $sql;
    }

    public static function getSqlSelect($table, $fields, $conds, $appends = NULL, $options = NULL, $arrExtra = NULL) {
        $arrSqls = array("SELECT");

        //options
        if (!empty($options)) {
            $strOptions = self::_getSqlPart($options, self::SQL_PART_KEY);
            $arrSqls[]  = "$strOptions";
        }

        //columns
        $strCols   = self::_getSqlPart($fields, self::SQL_PART_KEY, ',');
        $arrSqls[] = "$strCols";

        //tables
        $strTables = self::_getSqlPart($table, self::SQL_PART_KEY, ',');
        $arrSqls[] = "FROM $strTables";

        //conds
        if (!empty($conds)) {
            $strConds  = self::_getSqlPart($conds, self::SQL_PART_COMBINE, ' AND ');
            $arrSqls[] = "WHERE $strConds";
        }

        //appends
        if (!empty($appends)) {
            $strAppends = self::_getSqlPart($appends);
            $arrSqls[]  = "$strAppends";
        }

        return implode(' ', $arrSqls);
    }

    public static function getSqlUpdate($table, $fields, $conds = NULL, $appends = NULL, $options = NULL, $arrExtra = NULL) {
        $arrSqls = array("UPDATE");

        //options
        if (!empty($options)) {
            $strOptions = self::_getSqlPart($options, self::SQL_PART_KEY);
            $arrSqls[]  = "$strOptions";
        }

        //tables
        $strTables = self::_getSqlPart($table, self::SQL_PART_KEY, ',');
        $arrSqls[] = "$strTables SET";

        //fields
        $strFields = self::_getSqlPart($fields, self::SQL_PART_COMBINE, ',');
        $arrSqls[] = "$strFields";

        //conds
        if (!empty($conds)) {
            $strConds  = self::_getSqlPart($conds, self::SQL_PART_COMBINE, ' AND ');
            $arrSqls[] = "WHERE $strConds";
        }

        //appends
        if (!empty($appends)) {
            $strAppends = self::_getSqlPart($appends);
            $arrSqls[]  = "$strAppends";
        }

        return implode(' ', $arrSqls);
    }
}
