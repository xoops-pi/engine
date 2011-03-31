<?php
/**
 * Zend Framework for Xoops Engine
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Db
 * @version         $Id$
 */

class Xoops_Zend_Db_File_Mysql
{
    protected static $db;
    protected static $prefix;
    protected static $logs;
    protected static $errors;

    public static function setDb($db)
    {
        static::$db = $db;
    }

    public static function getDb()
    {
        if (!isset(static::$db)) {
            static::$db = XOOPS::registry("db");
        }
        return static::$db;
    }

    public static function setPrefix($prefix)
    {
        static::$prefix = $prefix;
    }

    public static function getLogs($type = null)
    {
        $return = array();
        if (empty($type)) {
            $return = (array) static::$logs;
        } elseif (isset(static::$logs[$type])) {
            $return = static::$logs[$type];
        }

        return $return;
    }

    public static function reset()
    {
        static::$db = null;
        static::$prefix = null;
        static::$logs = null;
        static::$errors = null;
    }

    /**
     * Function from phpMyAdmin (http://phpwizard.net/projects/phpMyAdmin/)
     *
     * @param   array    the splitted sql commands
     * @param   string   the sql commands
     * @return  boolean  always true
     * @access  public
     */
    public static function split(&$ret, $sql)
    {
        $sql            = trim($sql);
        $sql_len        = strlen($sql);
        $char           = '';
        $string_start   = '';
        $in_string      = false;

        for ($i = 0; $i < $sql_len; ++$i) {
            $char = $sql[$i];

           // We are in a string, check for not escaped end of
           // strings except for backquotes that can't be escaped
           if ($in_string) {
                for (;;) {
                    $i = strpos($sql, $string_start, $i);
                    // No end of string found -> add the current
                    // substring to the returned array
                    if (!$i) {
                        $ret[] = $sql;
                        return true;
                    }
                    // Backquotes or no backslashes before
                    // quotes: it's indeed the end of the
                    // string -> exit the loop
                    else if ($string_start == '`' || $sql[$i-1] != '\\') {
                        $string_start      = '';
                        $in_string         = false;
                        break;
                    }
                    // one or more Backslashes before the presumed
                    // end of string...
                    else {
                        // first checks for escaped backslashes
                        $j                     = 2;
                        $escaped_backslash     = false;
                        while ($i-$j > 0 && $sql[$i-$j] == '\\') {
                            $escaped_backslash = !$escaped_backslash;
                            $j++;
                        }
                        // ... if escaped backslashes: it's really the
                        // end of the string -> exit the loop
                        if ($escaped_backslash) {
                            $string_start  = '';
                            $in_string     = false;
                            break;
                        }
                        // ... else loop
                        else {
                            $i++;
                        }
                    } // end if...elseif...else
                } // end for
            } // end if (in string)
            // We are not in a string, first check for delimiter...
            else if ($char == ';') {
                // if delimiter found, add the parsed part to the returned array
                if ($str = trim(substr($sql, 0, $i))) {
                    $ret[]  = $str;
                }
                $sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
                $sql_len    = strlen($sql);
                if ($sql_len) {
                    $i      = -1;
                } else {
                    // The submited statement(s) end(s) here
                    return true;
                }
            } // end else if (is delimiter)
            // ... then check for start of a string,...
            else if (($char == '"') || ($char == '\'') || ($char == '`')) {
                $in_string    = true;
                $string_start = $char;
            } // end else if (is start of string)

            // for start of a comment (and remove this comment if found)...
            elseif ($char == '#'
                || (($char == ' ' || $char == "\012" || $char == "\015") && $i > 1 && $sql[$i-2] . $sql[$i-1] == '--')) {
                // starting position of the comment depends on the comment type
                $start_of_comment = (($sql[$i] == '#') ? $i : $i-2);

                // if no "\n" exits in the remaining string, checks for "\r"
                // (Mac eol style)
                $end_of_comment   = (strpos(' ' . $sql, "\012", $i+2))
                              ? strpos(' ' . $sql, "\012", $i+2)
                              : strpos(' ' . $sql, "\015", $i+2);

                //$end_of_comment = strpos(' ' . $sql, PHP_EOL, $i+2);
                if (!$end_of_comment) {
                    /*
                // no eol found after '#', add the parsed part to the returned
                // array and exit
                    // RMV fix for comments at end of file
                    $last = trim(substr($sql, 0, $i-1));
                    if (!empty($last)) {
                        $ret[] = $last;
                    }
                    */
                    return true;
                } else {
                    $sql     = substr($sql, 0, $start_of_comment) . ltrim(substr($sql, $end_of_comment));
                    $sql_len = strlen($sql);
                    //$i--;
                    $i = $start_of_comment - 1;
                    //Debug::e('=> i: '.$i.'; start: ' . $start_of_comment);
                } // end if...else
            } // end else if (is comment)
        } // end for

        // add any rest to the returned array
        $sql = trim($sql);
        if (!empty($sql) && $sql != '#' && $sql != '--') {
            $ret[] = $sql;
        }
        return true;
    }

    /**
     * add a prefix.'_' to all tablenames in a query
     *
     * @param   string  $query  valid SQL query string
     * @param   string  $prefix prefix to add to all table names
     * @return  mixed   FALSE on failure
     */
    public static function prefixQuery($query, $prefix)
    {
        $pattern = "/^(INSERT[\s]+INTO|CREATE[\s]+TABLE|CREATE[\s]+VIEW|ALTER[\s]+TABLE|ALTER[\s]+VIEW|UPDATE)(\s)+([`]?)([^`\s]+)\\3(\s)+/siU";
        $pattern2 = "/^(DROP[\s]+TABLE|DROP[\s]+VIEW)(\s)+([`]?)([^`\s]+)\\3(\s)?$/siU";
        if (preg_match($pattern, $query, $matches) || preg_match($pattern2, $query, $matches)) {
            $replace = "\\1 " . $prefix . "_\\4\\5";
            $matches[0] = preg_replace($pattern, $replace, $query);
            return $matches;
        } else {
            static::$errors[] = $query;
            return false;
        }
    }

    public static function queryFile($sqlFile, &$logs = array())
    {
        $db = static::getDb();
        $pieces = array();
        $status = true;
        $sql_query = file_get_contents($sqlFile);
        self::split($pieces, $sql_query);

        $prefix = $db->prefix() . (empty(static::$prefix) ? "" : "_" . static::$prefix);
        foreach ($pieces as $piece) {
            $piece = trim($piece);
            // [0] contains the prefixed query
            // [4] contains unprefixed table name
            $prefixed_query = self::prefixQuery($piece, $prefix);
            if (false == $prefixed_query) {
                $status = false;
                $logs[] = "Invalid query: " . $piece;
                continue;
            }
            $matches = array();
            if (preg_match("/^CREATE[\s]+(TABLE|VIEW)$/siU", $prefixed_query[1], $matches)) {
                $action = "create";
                $result = $db->query($prefixed_query[0]);
            } elseif (preg_match("/^INSERT[\s]+INTO$/siU", $prefixed_query[1])) {
                $action = "insert";
                $result = $db->query($prefixed_query[0]);
            } elseif (preg_match("/^ALTER[\s]+(TABLE|VIEW)$/siU", $prefixed_query[1])) {
                $action = "alter";
                $result = $db->query($prefixed_query[0]);
            } elseif (preg_match("/^DROP[\s]+(TABLE|VIEW)$/siU", $prefixed_query[1])) {
                $action = "drop";
                $result = $db->query($prefixed_query[0]);
            } else {
                continue;
            }
            $errorInfo = $result->errorInfo();
            $tableName = (empty(static::$prefix) ? "" : static::$prefix . "_") . $prefixed_query[4];
            if (empty($errorInfo[1])) {
                $logs[] = "Success: " . $action . " TABLE " . $tableName . " - " . $piece;
                if (!empty($matches)) {
                    static::$logs[$action][$tableName] = strtolower($matches[1]);
                } else {
                    static::$logs[$action][] = $tableName;
                }
            } else {
                $status = false;
                $logs[] = "failed: " . $action . " TABLE " . $tableName . " - " . $errorInfo[2];
            }
        }
        return $status;
    }
}