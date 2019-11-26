<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* DB class
*/
class DB {
	private $adaptor;

	/**
	 * Constructor
	 *
	 * @param	string	$adaptor
	 * @param	string	$hostname
	 * @param	string	$username
     * @param	string	$password
	 * @param	string	$database
	 * @param	int		$port
	 *
 	*/
	public function __construct($adaptor, $hostname, $username, $password, $database, $port = NULL) {
		$class = 'DB\\' . $adaptor;

		if (class_exists($class)) {
			$this->adaptor = new $class($hostname, $username, $password, $database, $port);
		} else {
			throw new \Exception('Error: Could not load database adaptor ' . $adaptor . '!');
		}
	}

	/**
     * 
     *
     * @param	string	$sql
	 * 
	 * @return	array
     */
	//public function query($sql) {
//		return $this->adaptor->query($sql);
//	}

    public function query($sql) {
        $args = func_get_args();
        $sql = call_user_func_array(array($this, 'prepare_sql'), $args);
        $query_start = microtime(true);
        $result = $this->adaptor->query($sql);

        return $result;

    }

	/**
     * 
     *
     * @param	string	$value
	 * 
	 * @return	string
     */
	public function escape($value) {
		return $this->adaptor->escape($value);
	}

	/**
     * 
	 * 
	 * @return	int
     */
	public function countAffected() {
		return $this->adaptor->countAffected();
	}

	/**
     * 
	 * 
	 * @return	int
     */
	public function getLastId() {
		return $this->adaptor->getLastId();
	}
	
	/**
     * 
	 * 
	 * @return	bool
     */	
	public function connected() {
		return $this->adaptor->connected();
	}



	////// svtol
    ///

    public function get_row($sql)
    {
        $args = func_get_args();
        $sql = call_user_func_array(array($this, 'prepare_sql'), $args);

        $result = $this->adaptor->query($sql);

        if($result->num_rows) return $result->row;
        else return array();
    }

    public function get_one($sql)
    {
        $args = func_get_args();
        $sql = call_user_func_array(array($this, 'prepare_sql'), $args);

        $result = $this->get_row($sql);
        if($result) {
            $keys = array_keys($result);
            return $result[$keys[0]];
        } else return null;
    }

    public function get_rows($sql)
    {
        $args = func_get_args();
        $sql = call_user_func_array(array($this, 'prepare_sql'), $args);

        $result = $this->adaptor->query($sql);

        if($result->num_rows) return $result->rows;
        else return array();
    }

    public function get_array($sql)
    {
        $result = $this->adaptor->query($sql);
        $result_array = array();
        if($result->num_rows) {
            foreach ($result->rows as $row) {
                $keys = array_keys($row);

                if(count($keys) == 2)
                    $result_array[ $row[$keys[0]] ] = $row[$keys[1]];
                else {
                    $key = $row[$keys[0]];
                    unset($row[$keys[0]]);
                    $result_array[$key] = $row;
                }
            }
            return $result_array;
        }
        else return array();
    }

    public function prepare_sql($sql_pattern = null)
    {
        $args = func_get_args();
        if (!$args)
            return false;

        if (count($args) == 1)
            return $args[0];

        $pattern = $args[0];

        /* if ($this->options['use_cache']) {
             $m = array();
             if (preg_match('~^cache:(?:(\d+):)?(select.*)$~i', $pattern, $m)) {
                 $this->use_cache(false, $m[1] ? (int)$m[1] : self::CACHE_MINUTES);
                 $pattern = $m[2];
             }
         }

         if ($this->options['use_prefix'])
             $pattern = preg_replace('~\{([a-z0-9_]+)\}~i',
                 $this->options['prefix'] . '\\1', $pattern);
 */
        if (strpos($pattern, '?') === false)
            return $pattern;

        $q = '';
        $ta = explode('?', $pattern);
        for ($i = 0, $count = count($ta); $i < $count; $i++) {
            $q .= $ta[$i];
            if ($i+1 < $count) {
                $j = $i+1;
                if (isset($args[$j])) {
                    if (is_array($args[$j]))
                        $q .= $this->fields_set($args[$j]);
                    else
                        $q .= $this->escape($args[$j]);
                } else
                    $q .= '?';
            }
        }
        return $q;
    }
}

class sDb {

    const DBCLASS = 'Db';
    const MASTER = 1;
    const SLAVE = 2;

    private static $instances = array();

    /**
     * @return DbMysql
     */
    public static function get_instance($instance_name = null, $options = null) {

        $instance_name = (string)$instance_name;

        if (!isset(self::$instances[$instance_name])) {
            $DBCLASS = self::DBCLASS;
            //$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
            self::$instances[$instance_name] = new $DBCLASS(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
        }

        $instance = & self::$instances[$instance_name];

        if ($options)
            $instance->set_options($options);

        return $instance;
    }

    public static function set_options(array $options, $instance_name = null) {
        self::get_instance($instance_name, $options);
    }

    public static function set_option($name, $value, $instance_name = null) {
        self::get_instance($instance_name)->set_option($name, $value);
    }

    /***** for PHP >= 5.3
    public static function __callStatic($method_name, array $args)
    {
    $instance = self::get_instance();
    if (method_exists($instance, $method_name)
    && is_callable(array($instance, $method_name))) {
    return call_user_func_array(array($instance, $method_name), $args);
    } else
    throw new Exception('Db::' . $method_name . '() doesn\'t exists');
    }*/

    public static function use_cache($name = false, $minutes = DbMysql::CACHE_MINUTES) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'use_cache'), $a);
    }

    public static function escape($str) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'escape'), $a);
    }

    public static function prepare_sql($sql_pattern) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'prepare_sql'), $a);
    }

    public static function fields_set(array $data) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'fields_set'), $a);
    }

    public static function query($q) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'query'), $a);
    }

    public static function debug($q) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'query'), $a);
    }

    public static function num_rows($res) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'num_rows'), $a);
    }

    public static function fetch_array($res) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'num_rows'), $a);
    }

    public static function get_rows($q) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'get_rows'), $a);
    }

    public static function get_row($q) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'get_row'), $a);
    }

    public static function get_first($q) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'get_one'), $a);
    }

    public static function get_one($q) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'get_one'), $a);
    }

    public static function get_array($q) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'get_array'), $a);
    }

    public static function get_assoc_key_value($q) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'get_assoc_key_value'), $a);
    }

    public static function get_assoc_key_row($q) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'get_assoc_key_row'), $a);
    }

    public static function get_plane_column($q) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'get_plane_column'), $a);
    }

    public static function affected_rows() {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'affected_rows'), $a);
    }

    public static function insert_id() {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'getLastId'), $a);
    }

    public static function next_query_db($db = self::SLAVE) {
        $a = func_get_args();
        return call_user_func_array(array(self::get_instance(), 'next_query_db'), $a);
    }
}