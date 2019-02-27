<?php
namespace AYashenkov\Database;

use AYashenkov\Config;

class DB
{
    protected static $instance = null;
    final private function __construct() {}
    final private function __clone() {}

    /**
     * @return null|\PDO
     */
    public static function instance() {
        if (self::$instance === null) {
            try {
                self::$instance = new \PDO(
                    'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME,
                    Config::DB_USER,
                    Config::DB_PASS
                );
                self::$instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            catch (\PDOException $e) {
                die('Невозможно подключиться к базе данных, проверьте настройки.');
            }
        }
        return self::$instance;
    }

    /**
     * @param $query
     * @return \PDOStatement
     */
    public static function q($query) {
        if (func_num_args() == 1) {
            return self::instance()->query($query);
        }
        $args = func_get_args();
        return self::instance()->query(self::autoQuote(array_shift($args), $args));
    }

    /**
     * @param $query
     * @return int
     */
    public static function x($query) {
        if (func_num_args() == 1) {
            return self::instance()->exec($query);
        }
        $args = func_get_args();
        return self::instance()->exec(self::autoQuote(array_shift($args), $args));
    }

    /**
     * @param $query
     * @param array $args
     * @return mixed
     */
    public static function autoQuote($query, array $args) {
        $i = strlen($query) - 1;
        $c = count($args);
        while ($i--) {
            if ('?' === $query[$i] && false !== $type = strpos('sia', $query[$i + 1])) {
                if (--$c < 0) {
                    throw new \InvalidArgumentException('Слишком мало параметров.');
                }
                if (0 === $type) {
                    $replace = self::instance()->quote($args[$c]);
                } elseif (1 === $type) {
                    $replace = intval($args[$c]);
                } elseif (2 === $type) {
                    foreach ($args[$c] as &$value) {
                        $value = self::instance()->quote($value);
                    }
                    $replace = '(' . implode(',', $args[$c]) . ')';
                }
                $query = substr_replace($query, $replace, $i, 2);
            }
        }
        if ($c > 0) {
            throw new \InvalidArgumentException('Слишком много параметров.');
        }
        return $query;
    }

    /**
     * @return bool
     */
    public static function beginTransaction() {
        return self::instance()->beginTransaction();
    }

    /**
     * @return bool
     */
    public static function commit() {
        return self::instance()->commit();
    }

    /**
     * @return mixed
     */
    public static function errorCode() {
        return self::instance()->errorCode();
    }

    /**
     * @return array
     */
    public static function errorInfo() {
        return self::instance()->errorInfo();
    }

    /**
     * @param $statement
     * @return int
     */
    public static function exec($statement) {
        return self::instance()->exec($statement);
    }

    /**
     * @param $attribute
     * @return mixed
     */
    public static function getAttribute($attribute) {
        return self::instance()->getAttribute($attribute);
    }

    /**
     * @return array
     */
    public static function getAvailableDrivers() {
        return self::instance()->getAvailableDrivers();
    }

    /**
     * @return bool
     */
    public static function inTransaction() {
        return self::instance()->inTransaction();
    }

    /**
     * @param null $name
     * @return string
     */
    public static function lastInsertId($name = NULL) {
        return self::instance()->lastInsertId($name);
    }

    /**
     * @param $statement
     * @param array $driver_options
     * @return \PDOStatement
     */
    public static function prepare($statement, $driver_options = array()) {
        return self::instance()->prepare($statement, $driver_options);
    }

    /**
     * @return mixed
     */
    public static function query() {
        $arguments = func_get_args();
        return call_user_func_array(array(self::instance(), 'query'), $arguments);
    }

    /**
     * @param $string
     * @param int $parameter_type
     * @return string
     */
    public static function quote($string, $parameter_type = \PDO::PARAM_STR) {
        return self::instance()->quote($string, $parameter_type);
    }

    /**
     * @return bool
     */
    public static function rollBack() {
        return self::instance()->rollBack();
    }

    /**
     * @param $attribute
     * @param $value
     * @return bool
     */
    public static function setAttribute($attribute, $value) {
        return self::instance()->setAttribute($attribute, $value);
    }
}