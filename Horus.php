<?php
/**
 * Horus is a smart micro php framework that built to do what i want
 * in an easy way in simple steps .
 * What is want [Mohammed Al Ashaal] is just a simple and small package
 * that nearly contains most of required operations without big files, or even
 * without more files/libraries .
 * "Be simple to be smart"
 * @author      Mohammed Al-Ashaal [<m7medalash3al@gmail.com>, <fb.com/alash3al>]
 * @copyright   2014 Mohammed Al-Ashaal
 * @link        http://alash3al.github.io/Horus/
 * @license     https://github.com/alash3al/Horus/blob/master/LICENSE
 * @version     6.0
 * @package     Horus
 * @filesource
 */

// -------------------------------

/**
 * Container
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    5.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
Class Horus_Container implements ArrayAccess, Countable, IteratorAggregate, Serializable
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * Construct
     * @param   array $data
     * @return  void
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }

    /**
     * Export as an array
     * @return  array
     */
    public function export()
    {
        return $this->data;
    }

    /**
     * Import an array
     * @param   array $data
     * @return  void
     */
    public function import(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get stored value
     * @param   string  $key
     * @param   string  $default
     * @return  mixed
     */
    public function _get($key, $default = null)
    {
        if(isset($this->data[$key])) return $this->data[$key];
        else return $default;
    }

    /**
     * Store a key's value
     * @param   string  $key
     * @param   mixed   $value
     * @return  void
     */
    public function _set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Check if the store has a certain key
     * @param   string $key
     * @return  bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a key from the store
     * @param mixed $key
     * @return
     */
    function remove($key)
    {
        unset($this->data[$key]);
    }

    /** @ignore */
    public function __call($name, $args)
    {
        if(!isset($this->data[$name]) or !is_callable($this->data[$name])) {
            throw new InvalidArgumentException('Call to undefined method '.__CLASS__.'::'.$name.'()');
        } else return call_user_func_array($this->data[$name], $args);
    }

    /** @ignore */
    public function __get($key)
    {
        return $this->_get($key);
    }

    /** @ignore */
    public function __set($key, $value)
    {
        $this->_set($key, $value);
    }

    /** @ignore */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /** @ignore */
    public function __unset($key)
    {
        return $this->remove($key);
    }

    /** @ignore */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /** @ignore */
    public function offsetGet($offset)
    {
        return $this->_get($offset);
    }

    /** @ignore */
    public function offsetSet($offset, $value)
    {
        $this->_set($offset, $value);
    }

    /** @ignore */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /** @ignore */
    public function count()
    {
        return count($this->data);
    }

    /** @ignore */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /** @ignore */
    public function serialize()
    {
        return json_encode($this->data);
    }

    /** @ignore */
    public function unserialize($serialized)
    {
        return json_decode($serialized, true);
    }

    /** @ignore */
    public function __toString()
    {
        return var_dump($this->data);
    }
}

// -------------------------------

/**
 * SQL Manager
 * A simple yet powerful sql framework that extends PDO
 * and adds some new features to make code simple .
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    4.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
class Horus_SQL extends PDO
{
    /** @ignore */
    protected $stmnt, $driver, $database, $connected;

    /** @ignore */
    public function __construct(){}

    /**
     * Construtor
     * @param string    $dns
     * @param string    $username
     * @param string    $password
     * @param array     $driver_options
     * @return Object
     */
    public function connect($dns, $username = null, $password = null, array $driver_options = array())
    {
        parent::__construct($dns, $username, $password, $driver_options);
        $this->_setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        list($this->driver) = explode(':', $dns, 2);
        if(preg_match('/dbname(.*?)=(.+)/i', $dns, $m)) {
            $this->database = trim($m[2]);
        }
        $this->connected = true;
        return $this;
    }

    /**
     * Check whether we have connected to our db or not
     * @return  bool
     */
    public function connected()
    {
        return ($this->connected === true);
    }

    /**
     * Create new mysql connection
     * @param string    the database host server
     * @param string    the database name
     * @param string    the database username
     * @param string    the database password
     * @return object
     */
    public function mysql($host, $dbname, $username = null, $password = null)
    {
        return $this->connect("mysql:host={$host}; dbname={$dbname}", $username, $password);
    }

    /**
     * Create new mariaDB connection
     * @param string    the database host server
     * @param string    the database name
     * @param string    the database username
     * @param string    the database password
     * @return object
     */
    public function mariaDB($host, $dbname, $username = null, $password = null)
    {
        $c = $this->connect("mysql:host={$host}; dbname={$dbname}", $username, $password);
        $this->driver = 'mariaDB';
        return $c;
    }

    /**
     * Connect to sqlite file
     * @param   string  $filename
     * @param   bool    $persistent
     * @return  object
     */
    public function sqlite($filename, $persistent = false)
    {
        $filename = ($filename == ':temp:') ? (realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . 'horus-temp-db.sqlite') : $filename;
        $c =  $this->connect("sqlite:{$filename}",null,null,array(PDO::ATTR_PERSISTENT => (bool) $persistent));
        $this->query('pragma synchronous = off;');
        return $c;
    }

    /**
     * Create new postgresql connection
     * @param string    the database host server
     * @param string    the database name
     * @param string    the database username
     * @param string    the database password
     * @return object
     */
    public function pgsql($host, $dbname, $username = null, $password = null)
    {
        return $this->connect("pgsql:host={$host};dbname={$dbname};user={$username};password={$password}");
    }

    /**
     * Create new ms-sqlserver connection
     * @param string    the database host server
     * @param string    the database name
     * @param string    the database username
     * @param string    the database password
     * @return object
     */
    public function mssql($host, $dbname, $username = null, $password = null)
    {
        return $this->connect("mssql:host={$host};dbname={$dbname}", $username, $password);
    }

    /**
     * Create new oracle connection
     * @param string    the database host server
     * @param string    the database name
     * @param string    the database username
     * @param string    the database password
     * @return object
     */
    public function oracle($host, $dbname, $username = null, $password = null)
    {
        return $this->connect("oci:dbname=//{$host}/{$dbname}", $username, $password);
    }

    /**
     * Gets the current used driver
     * @return string
     */
    public function driver()
    {
        return strtolower($this->driver);
    }

    /**
     * Gets the current used database
     * @return string
     */
    public function database()
    {
        return $this->database;
    }

    /**
     * Execute sql statement
     * This is prepare + execute
     * @param   string    $statement
     * @param   mixed     $inputs
     * @return  False | object
     */
    public function query($statement, $inputs = null)
    {
        $this->stmnt = $this->prepare($statement);
        if(!is_object($this->stmnt)) return false;
        return ((bool) ( $this->stmnt->execute((array) $inputs) ) ? $this : false);
    }

    /**
     * Basic insert/replace statement
     * @param   string  $table
     * @param   array   $inserts
     * @return  dbquery
     */
    public function insert($table, array $inserts, $replace = false)
    {
        // insert or replace
        $s = $replace ? 'REPLACE' : 'INSERT';

        // multiple inserts
        if(isset($inserts[1]) and is_array($inserts[1])) 
        {
            $cols   =   implode(', ', (array) $inserts[0]); array_shift($inserts);
            $inputs =   array();
            $vals   =   implode(', ', array_fill(1, count($inserts), '('.implode(', ', array_fill(1, count($inserts[0]), '?')).')'));

            foreach($inserts as &$i) {
                $inputs = array_merge($inputs, $i);
            }

            return $this->query("$s INTO $table($cols) VALUES $vals", $inputs);
        }

        // single insert
        else 
        {
            $cols = implode(', ', array_keys($inserts));
            $inputs = array_values($inserts);
            $vals = implode(', ', array_fill(1, count($inputs), '?'));
            return $this->query("$s INTO $table($cols) VALUES ($vals)", $inputs);
        }
    }

    /**
     * Basic Update statement
     * @param   string    $table
     * @param   array     $updates
     * @param   string    $where
     * @param   mixed     $inputs
     * @return  dbquery
     */
    public function update($table, array $updates, $where = null, $inputs = null)
    {
        $i = (array) array_values($updates);
        $vals = array();

        foreach($updates as $k => $v) {
            $vals[] = "$k = ?";
        }

        $vals = implode(', ', $vals);
        $inputs = array_merge($i, (array) $inputs);
        $where = empty($where) ? null : "WHERE $where ";

        return $this->query("UPDATE $table SET $vals $where", $inputs);
    }

    /**
     * Basic delete statement
     * @param   string  $table
     * @param   string  $where
     * @param   mixed   $inputs
     * @return  dbquery
     */
    public function delete($table, $where = null, $inputs = null)
    {
        $where = empty($where) ? null : "WHERE $where";
        return $this->query("DELETE FROM $table $where ", (array) $inputs);
    }

    /**
     * Run select statment
     * @param   string  $table
     * @param   string  $columns
     * @param   string  $sql
     * @param   mixed   $inputs
     * @return  Horus_SQL | false
     */
    public function get($table, $columns, $sql = null, $inputs = null)
    {
        if($this->query("SELECT {$columns} FROM {$table} $sql", (array) $inputs))
        {
            return $this;
        }
        else return false;
    }

    /**
     * Set charset name
     * @param   string name
     * @return  bool
     */
    public function charset($name)
    {
        return $this->query("SET CHARSET {$name}");
    }

    /**
     * Gets database tables
     * @param   string  $dbname
     * @return  array
     */
    public function tables($dbname = null)
    {
        $dbname = empty($dbname) ? $this->database :$dbname;
        return $this->query("SELECT table_name FROM INFORMATION_SCHEMA.tables WHERE table_schema = ?", $dbname)->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Gets database table columns
     * @param   string  $table_name
     * @param   string  $dbname
     * @return  array
     */
    public function columns($table_name, $dbname = null)
    {
        $dbname = empty($dbname) ? $this->database :$dbname;
        return $this->query("SELECT column_name FROM INFORMATION_SCHEMA.columns WHERE table_schema = ? and table_name = ?", array($dbname, $table_name))->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Check if table(s) exists or not
     * @param   mixed  $table_name
     * @param   string  $dbname
     * @return  bool
     */
    public function table_exists($table_name, $dbname = null)
    {
        $tables = $this->tables($dbname);
        return sizeof(array_intersect($tables, (array) $table_name)) === sizeof((array) $table_name);
    }

    /**
     * Check if table column(s) exists or not
     * @param   string  $table_name
     * @param   mixed   $column
     * @param   string  $dbname
     * @return  bool
     */
    public function column_exists($table_name, $column, $dbname = null)
    {
        $columns = $this->columns($table_name, $dbname);
        return sizeof(array_intersect($columns, (array) $column)) === sizeof((array) $column);
    }

    /** @ignore */
    public function __call($name, $args)
    {
        if(is_callable(array($this->stmnt, $name))) {
            return call_user_func_array(array($this->stmnt, $name), $args);
        }
    }
}

// -------------------------------

/**
 * Horus SQL Based Table Class
 * 
 * @package     Horus
 * @author      Mohammed Al-Ashaal
 * @since       4.1
 * @copyright   2014 Mohammed Al-Ashaal
 */
class Horus_SQL_Table
{
    /**
     * @var string
     */
    var $table; 

    /**
     * @var resource
     */
    var $db;

    /**
     * Class Constructor
     * @param   resource    $db
     * @param   string      $table [optional]
     * @return  object
     */
    function __construct(Horus_SQL $db, $table = null)
    {
        $this->db = $db;
        $this->table = $table;
    }

    /**
     * Build a new table object
     * @param   string  $name
     * @return  object
     */
    function using($name)
    {
        return new self($this->db, $name);
    }

    /**
     * Add new entry
     * @param   array   $inserts
     * @return  LastInsertId | FALSE
     */
    function add(array $inserts)
    {
        return $this->insert($this->table, $inserts);
    }

    /**
     * Replace an entry
     * @param   array   $replaces
     * @return  LastInsertId | FALSE
     */
    function rep(array $replaces)
    {
        return $this->insert($this->table, $replaces, TRUE);
    }

    /**
     * Delete an entry
     * @param   string  $where
     * @param   mixed   $inputs
     * @return  bool
     */
    function del($where, $inputs)
    {
        return $this->delete($this->table, $where, $inputs);
    }

    /**
     * Edit an entry
     * @param   array   $edits
     * @param   string  $where
     * @param   mixed   $inputs
     * @return  bool
     */
    function edit(array $edits, $where = null, $inputs = null)
    {
        return $this->update($this->table, $edits, $where, $inputs);
    }

    /**
     * Get an entry
     * @param   string  $where
     * @param   mixed   $inputs
     * @param   int     $fetch_style
     * @return  mixed
     */
    function get_one($where, $inputs, $fetch_style = PDO::FETCH_ASSOC)
    {
        if($this->query("SELECT * FROM {$this->table} WHERE $where LIMIT 1", $inputs))
            return $this->fetch((int) $fetch_style);
        else return FALSE;
    }

    /**
     * Get all entries
     * @param   string  $where
     * @param   mixed   $inputs
     * @param   string  $more_sql
     * @param   mixed   $more_inputs
     * @return  query_object
     */
    function get_all($where = null, $inputs = null, $more_sql = null, $more_inputs = null)
    {
        $where = empty($where) ? null : " WHERE $where ";
        return $this->query("SELECT * FROM {$this->table} $where $more_sql", array_merge((array) $inputs, (array) $more_inputs));
    }

    /**
     * Count entries
     * @param   string    $where
     * @param   mixed     $inputs
     * @return  float
     */
    function count($where = null, $inputs = null)
    {
        $where = empty($where) ? null : " WHERE $where ";
        return (float) $this->query("SELECT COUNT(*) FROM {$this->table} AS COUNT $where", $inputs)->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Optimize current table
     * @return bool
     */
    function optimize()
    {
        return $this->query("OPTIMIZE TABLE {$this->table}");
    }

    /**
     * Drop current table
     * @return bool
     */
    function drop()
    {
        return $this->query("DROP TABLE {$this->table}");
    }

    /**
     * Truncate current table
     * @return bool
     */
    function truncate()
    {
        return $this->query("TRUNCATE TABLE {$this->table}");
    }

    /**
     * Gets current table columns
     * @return  array
     */
    function cols()
    {
        return $this->columns($this->table);
    }

    /**
     * Check if the table exists (only if the $column is null)
     * or it will check if the provide column is exists or not.
     * @param   mixed   $column
     * @return  bool
     */
    function exists($column = null)
    {
        if(empty($column)) return $this->table_exists($this->table);
        else return $this->column_exists($this->table, $column);
    }

    /** @ignore */
    function __call($a, $b)
    {
        return call_user_func_array(array($this->db, $a), $b);
    }
}

// -------------------------------

/**
 * Horus key => value datastore
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    6.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
Class Horus_SQL_KV
{
    /** @ignore */
    protected $sql, $table;

    /**
     * Constructor
     * @param   Horus_SQL   $sql
     * @param   string      $table
     * @return
     */
    public function __construct(Horus_SQL $sql, $table = null)
    {
        $this->sql      =   $sql;
        $this->table    =   $table;
    }

    /**
     * Select table to work on
     * @param   string $table
     * @return  this
     */
    public function using($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Create a table [if not exists]
     * @param   string $table
     * @param   string $engine
     * @param   string $charset
     * @return  false | this
     */
    public function create($table, $engine = 'innoDB', $charset = 'UTF8')
    {
        $r = $this->sql->query("CREATE TABLE IF NOT EXISTS {$table} ( `ds_key` TEXT NOT NULL, `ds_value` MEDIUMBLOB, `ds_updated` INT, PRIMARY KEY (`ds_key`(255)) ) ENGINE {$engine} DEFAULT CHARSET {$charset}");
        $this->table = $table;
        return ($r == true) ? $this : false;
    }

    /**
     * SET key(s) => val(s)
     * @param   mixed $k
     * @param   mixed $v
     * @return  bool
     */
    public function set($k, $v = null)
    {
        $v = (is_array($v) or is_object($v)) ? json_encode($v) : $v;
        $k = is_array($k) ? $k : array($k => $v);
        $data = array();
        $data[] = array('ds_key', 'ds_value', 'ds_updated');

        foreach( $k as $key => &$val )
            $data[] = array($key, $val, time());

        return $this->sql->insert($this->table, $data, true);
    }

    /**
     * GET key(s)
     * @param   mixed $k
     * @return  mixed
     */
    public function get($k)
    {
        $k = (array) $k;
        $all = count($k) > 1; 
        $vals = '(' . implode(', ', array_fill(1, count($k), '?')) . ')';
        $this->sql->get($this->table, ($all ? 'ds_key,ds_value' : 'ds_value'), " WHERE ds_key IN {$vals} ", $k);
        return $all ? $this->sql->fetchAll(PDO::FETCH_KEY_PAIR) : $this->sql->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Get formated key based on a sql pattern
     * @param   string  $pattern
     * @param   string  $limit
     * @param   string  $order
     * @return  array | false
     */
    public function fget($pattern, $limit = 5, $order = 'desc')
    {
        $this->sql->get($this->table, 'ds_key,ds_value', " WHERE ds_key LIKE ? ORDER BY ds_updated {$order} LIMIT {$limit} ", $pattern);
        return $this->sql->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Fetch using sql statment
     * @param   string $limit
     * @param   string $order
     * @return  array | false
     */
    public function all($limit = 5, $order = 'desc')
    {
        $this->sql->get($this->table, 'ds_key,ds_value', " ORDER BY ds_updated {$order} LIMIT {$limit} ");
        return $this->sql->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Delete key(s)
     * @param   mixed $k
     * @return  bool
     */
    public function del($k)
    {
        $k = (array) $k;
        $vals = '(' . implode(', ', array_fill(1, count($k), '?')) . ')';
        return $this->sql->delete($this->table, " ds_key IN {$vals} ", $k);
    }

    /**
     * Check whether key(s) exists
     * @param   mixed $k
     * @return  bool
     */
    public function has($k)
    {
        $k = (array) $k;
        return (($d = $this->get($k, true)) != false  && count($d) === count($k));
    }

    /**
     * Push element(s) to the end of a list
     * @param   string $k
     * @param   string $v
     * @param   string ...
     * @return  bool
     */
    public function push($k, $v)
    {
        $k = $k;
        $v = (array) array_slice(func_get_args(), 1);
        $d = (($d = $this->get($k)) !== false ? (array) json_decode($d, true) : array());
        $d = array_merge($d, $v);
        $d = json_encode($d);
        unset($v);
        return $this->set($k, $d);
    }

    /**
     * Pop element(s) off the end of a list
     * @param   string  $k
     * @param   integer $count
     * @return  bool
     */
    public function pop($k, $count = 1)
    {
        $k = $k;
        $v = (array) array_slice(func_get_args(), 1);
        $d = (($d = $this->get($k)) !== false ? (array) json_decode($d, true) : array());
        for($i=0; $i < (int) $count; ++$i) array_pop($d);
        unset($v);
        $d = json_encode($d);
        return $this->set($k, $d);
    }

    /**
     * Shift element(s) off the end of a list
     * @param   string  $k
     * @param   integer $count
     * @return  bool
     */
    public function shift($k, $count = 1)
    {
        $k = $k;
        $v = (array) array_slice(func_get_args(), 1);
        $d = (($d = $this->get($k)) !== false ? (array) json_decode($d, true) : array());
        for($i=0; $i < (int) $count; ++$i) array_shift($d);
        unset($v);
        $d = json_encode($d);
        return $this->set($k, $d);
    }

    /**
     * Prepend element(s) to the start of a list
     * @param   string $k
     * @param   string $v
     * @param   string ...
     * @return  bool
     */
    public function unshift($k, $v)
    {
        $k = $k;
        $v = (array) array_slice(func_get_args(), 1);
        $d = (($d = $this->get($k)) !== false ? (array) json_decode($d, true) : array());
        $d = array_merge($v, $d);
        $d = json_encode($d);
        unset($v);
        return $this->set($k, $d);
    }

    /**
     * Increment key(s)
     * @param   mixed $k
     * @param   integer $by
     * @return  bool
     */
    public function increment($k, $by = 1)
    {
        $k = (array) $k;
        $by = (int) abs($by); $GLOBALS['__kv_by'] = $by;
        if(!$this->has($k)):
            if(count($k) == 1) 
            {
                return $this->set($k, $by);
            }
            else 
            {
                $kys = $this->get($k);
                $diff = array_diff(array_values($k), array_keys($kys));
                foreach($diff as $x => &$v) $kys[$diff[$x]] = 0;
            }
        else:
            $kys = $this->get($k);
        endif;
        $kys = array_map(create_function('$v', 'return (double) $v + $GLOBALS[\'__kv_by\'];'), $kys);
        return $this->set($kys);
    }

    /**
     * Dencrement key(s)
     * @param   mixed $k
     * @param   integer $by
     * @return  bool
     */
    public function decrement($k, $by = 1)
    {
        $k = (array) $k;
        $by = (int) abs($by); $GLOBALS['__kv_by'] = $by;
        if(!$this->has($k)):
            if(count($k) == 1) 
            {
                return $this->set($k, $by);
            }
            else 
            {
                $kys = $this->get($k);
                $diff = array_diff(array_values($k), array_keys($kys));
                foreach($diff as $x => &$v) $kys[$diff[$x]] = 0;
            }
        else:
            $kys = $this->get($k);
        endif;
        $kys = array_map(create_function('$v', 'return (double) $v - $GLOBALS[\'__kv_by\'];'), $kys);
        return $this->set($kys);
    }
}

// -------------------------------

/**
 * Horus document datastore
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    6.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
Class Horus_SQL_DocStore
{
    /** @ignore */
    protected $sql, $table;

    /**
     * Constructor
     * @param   Horus_SQL   $sql
     * @param   string      $table
     * @return  Constructor
     */
    public function __construct(Horus_SQL $sql)
    {
        $this->sql      =   $sql;
    }

    /**
     * Select table to work on
     * @param   string $table
     * @return  this
     */
    public function using($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Create a table [if not exists]
     * @param   string $table
     * @param   string $engine
     * @param   string $charset
     * @return  false | this
     */
    public function create($table, $engine = 'innoDB', $charset = 'UTF8')
    {
        $r = $this->sql->query(
            "CREATE TABLE IF NOT EXISTS {$table} 
            (
                `AID`       BIGINT  NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `CID`       VARCHAR(30) NOT NULL, 
                `UPDATED`   INT,
                `TYPE`      VARCHAR(30),
                `BODY`      MEDIUMBLOB,
                UNIQUE KEY(`CID`),
                KEY(`UPDATED`)
            ) ENGINE {$engine} DEFAULT CHARSET {$charset}");
        $this->table = $table;
        return ($r == true) ? $this : false;
    }

    /**
     * Create an index
     * @param   string $name
     * @param   string $type
     * @param   string $engine
     * @param   string $charset
     * @return  bool
     */
    public function index($name, $type = 'varchar(255)', $engine = 'innoDB', $charset = 'UTF8')
    {
        $r = $this->sql->query(
            "CREATE TABLE IF NOT EXISTS index_{$name} 
            (
                `CID`       VARCHAR(30) NOT NULL,
                `{$name}`   {$type},
                PRIMARY KEY(`CID`),
                FOREIGN KEY (`CID`) REFERENCES {$this->table}(`CID`) ON DELETE CASCADE
            ) ENGINE {$engine} DEFAULT CHARSET {$charset}");
        return (bool) $r;
    }

    /**
     * Insert new document
     * @param   array $data
     * @return  bool
     */
    public function put(array $data)
    {
        // get the system keys 
        $id     = empty($data['_id'])    ? $this->uniqid()  : $data['_id'];
        $index  = empty($data['_index']) ? array()          : (array) $data['_index'];
        $type   = empty($data['_type'])  ? ''               : $data['_type'];
        $data   = array_merge( array( 'id' => $id ), $data );

        // remove the system keys
        unset($data['_id'], $data['_index'], $data['_type']);

        // results ...
        $r = array();

        // 1). insert into the main table
        $r[] = $x = $this->sql->insert($this->table, array
        (
            'CID'       =>  $id,
            'UPDATED'   =>  time(),
            'TYPE'      =>  $type,
            'BODY'      =>  $this->encode($data)
        ));

        // 2). save indexes
        for( $i = 0, $c = count($index); $i < $c; ++ $i ) {
            $r[] = $this->sql->insert("index_{$index[$i]}", array(
                'CID'       =>  $id,
                $index[$i]  =>  $data[$index[$i]]
            ));
        }

        // return
        return !in_array(false, $r, false);
    }

    /**
     * Update a document
     * @param   array $data
     * @return  bool
     */
    public function edit(array $data)
    {
        // No id ! [exit "false"]
        if(empty($data['_id'])) return false;
        
        // get the system keys 
        $id     = $data['_id'];
        $index  = empty($data['_index']) ? array()          : (array) $data['_index'];
        $type   = empty($data['_type'])  ? ''               : $data['_type'];
        $data   = array_merge( array( 'id' => $id ), $data );

        // remove the system keys
        unset($data['_id'], $data['_index'], $data['_type']);

        // results ...
        $r = array();

        // 1). edit the main table
        $r[] = $this->sql->update($this->table, array(
            'UPDATED'   =>  time(),
            'TYPE'      =>  $type,
            'BODY'      =>  $this->encode($data)
        ), 'CID = ?', $id);

        // 2). update indexes
        for( $i = 0, $c = count($index); $i < $c; ++ $i ) {
            $r[] = $this->sql->update("index_{$index[$i]}", array(
                $index[$i]  =>  $data[$index[$i]]
            ), 'CID = ?', $id);
        }

        // return
        return !in_array(false, $r, false);
    }

    /**
     * Delete entyties
     * @param   array $id
     * @return  bool
     */
    public function delete($id)
    {
        $id     = (array) $id;
        $vals   = "(" . implode(', ', array_fill(1, count($id), '?')) . ")";

        return (bool) $this->sql->delete($this->table, "CID IN {$vals}", $id);
    }

    /**
     * Fetch data
     * @param   array $data
     * @return  false | Horus_SQL
     */
    public function get(array $data = array())
    {
        // get the system keys 
        $id     = empty($data['_id'])    ? ''               : $data['_id'];
        $index  = empty($data['_index']) ? array()          : (array) $data['_index'];
        $sql    = empty($data['_sql'])   ? ''               : $data['_sql'];
        $inputs = empty($data['_inputs'])? array()          : (array) $data['_inputs'];

        // remove the system keys
        unset($data['_id'], $data['_index'], $data['_type'], $data['_sql'], $data['_inputs']);

        // SQL statment
        $stmnt = "SELECT {$this->table}.* FROM {$this->table} ";
        $inpts = array();

        // Join on indexes + main table cols
        for( $i = 0, $c = count($index); $i < $c; ++ $i ) {
            $stmnt .= " LEFT JOIN index_{$index[$i]} AS jt_{$i} ON jt_{$i}.CID = {$this->table}.CID ";
        }

        // using id ?
        if(!empty($id)) {
            $stmnt .= " WHERE ( CID = ? ) ";
            $inpts[] = $id;
        }

        // more sql ?
        if(!empty($sql)) {
            $stmnt .= $sql;
            $inpts = array_merge($inpts, $inputs);
        }

        // update vars
        $sql = $stmnt;
        $inputs = $inpts;

        // free some vars
        unset($i, $id, $index, $inpts, $stmnt);

        // return result
        return ($this->sql->query($sql, $inputs)) ? $this->sql : false;
    }

    /**
     * Drop indexes
     * @param   string $index
     * @return  Horus_Query
     */
    public function dropIndex($index)
    {
        return (bool) $this->sql->query("DROP TABLE IF EXISTS `index_{$index}`");
    }

    /**
     * Encode document
     * @param   array $data
     * @return  string
     */
    public function encode(array $data)
    {
        return  gzcompress(json_encode($data), 7);
    }

    /**
     * Decode document
     * @param   string $data
     * @return  array
     */
    public function decode($data)
    {
        return json_decode(gzuncompress($data), true);
    }

    /**
     * Generate a unique id
     * @param   integer $length
     * @return  string
     */
    public function uniqid($length = 15)
    {
        return substr(str_shuffle(sha1(microtime().uniqid())), 0, (int) $length);
    }
}

// -------------------------------

/**
 * Router
 * A smart simple light router with better routing
 * michanism .
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
Class Horus_Router
{
    /**
     * @var array
     */
    protected $routes       =   array();

    /**
     * @var array
     */
    protected $shortcuts    =   array();

    /**
     * @var string
     */
    protected $basepath     =   "/";

    /**
     * Constructor
     * @return  Horus_Router
     */
    public function __construct()
    {
        // simulator ?
        $_SERVER['SIMULATOR_STATE'] =  (!isset($_SERVER['SIMULATOR_STATE']) ? 1 : (int) $_SERVER['SIMULATOR_STATE']);

        // simulator method ?
        $_SERVER['SIMULATOR_METHOD'] =  (empty($_SERVER['SIMULATOR_METHOD']) ? 1 : (int) $_SERVER['SIMULATOR_METHOD']);
        $_SERVER['SIMULATOR_METHOD'] = ($_SERVER['SIMULATOR_METHOD'] > 2 or $_SERVER['SIMULATOR_METHOD'] < 1) ? 1 : $_SERVER['SIMULATOR_METHOD'];

        // set default server vars
        !empty($_SERVER['SCRIPT_PROTOCOL']) or ($_SERVER['SCRIPT_PROTOCOL'] =   isset($_SERVER['HTTPS']) ? 'https' : 'http');
        $_SERVER['SCRIPT_URL']      =   $_SERVER['SCRIPT_PROTOCOL'] . '://' . rtrim($_SERVER['SERVER_NAME'],'/') . '/' . ltrim(rtrim(dirname($_SERVER['SCRIPT_NAME']),'/'),'/') . '/';
        $_SERVER['SCRIPT_ROUTE']    =   $_SERVER['SCRIPT_URL'];

        // starting simulator
        $this->simulator((bool) $_SERVER['SIMULATOR_STATE']);

        // update the path info
        if(!empty($_SERVER['PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = str_replace('//', '/', '/'. ltrim(rtrim($_SERVER['PATH_INFO'], '/'), '/') .'/');
        }
        else $_SERVER['PATH_INFO'] = '/';

        // set REQUEST_ROUTE
        $_SERVER['REQUEST_ROUTE'] = $_SERVER['SCRIPT_ROUTE'] . ltrim($_SERVER['PATH_INFO'], '/');

        // REGEXP shortcuts
        $this->shortcut(array
        (
            '{num}'     =>  '([0-9\.,]+)',
            '{alpha}'   =>  '([a-zA-Z]+)',
            '{alnum}'   =>  '([a-zA-Z0-9\.\w]+)',
            '{str}'     =>  '([a-zA-Z0-9-_\.\w]+)',
            '{any}'     =>  '([^\/]+)',
            '{*}'       =>  '?:|(.*?)',
            '{date}'    =>  '([0-9]+\/[0-9]{2,2}+\/[0-9]{2,2}+)'
        ));
    }

    /**
     * Shortcut to {@see} Horus_Router::route() 
     * @param   string  $a
     * @param   array   $b
     * @return  void
     */
    public function __call($a, $b)
    {
        return $this->route(str_replace('_', '|', $a), $b[0], $b[1]);
    }

    /**
     * Create a group of routes
     * @param  string       $pattern
     * @param  callback     $callback
     */
    public function with($pattern, $callback)
    {
        $this->basepath = $this->prepare($pattern);
        if(is_callable($callback))
            call_user_func($callback);
        $this->basepath = '/';
    }

    /**
     * Call / execute an internal pattern
     * @param   string  $method
     * @param   string  $path
     * @return  string | false
     */
    public function exec($method = '', $path = '')
    {
        // prepare method & path
        $method = empty($method) ? strtoupper($_SERVER['REQUEST_METHOD']) : strtoupper($method);
        $path   = empty($path) ? $_SERVER['PATH_INFO'] : $path;
        $path   = str_replace('//', '/', '/' . rtrim(ltrim($path, '/'), '/') . '/');
        $path   = str_replace(chr(0), '', $path);

        // output and status
        $output = "";
        $status = 0;

        // start catching the output
        ob_start();

        // iterate over the registered routes
        // if the method matches the current method or ANY
        // then iterate over them and check it's callback type
        foreach( new ArrayIterator($this->routes) as $m => $patterns ):
            if( in_array($m, array('ANY', $method)) ):
                foreach( new ArrayIterator($patterns) as $pattern => $callable ):
                    list( $callable, $type ) = $callable;
                    $is_class = in_array($type, array('class_name', 'used_class'), false);
                    $pattern  = "/^{$pattern}" . (!$is_class ? "$" : "") . "/";
                    if( preg_match($pattern, $path, $args) ):
                        array_shift($args);
                        switch($type):
                            Case "callback":
                                call_user_func_array($callable, $args);
                                ++ $status;
                                break;
                            Case "file":
                                $f = create_function('$file, $args', 'include $file;');
                                call_user_func_array($f, array($callable, $args));
                                ++ $status;
                                break;
                            Case "class_name":
                            Case "used_class":
                                $class  = ($type == 'used_class') ? $callable : new $callable;
                                $uri    = preg_replace($pattern, '', $path, 1);
                                $uri    = empty($uri) ? '/' : str_replace('//', '/', '/' . ltrim(rtrim($uri, '/'), '/') . '/');
                                $parts  = explode('/', $uri);
                                array_shift($parts); array_pop($parts);
                                $method = empty($parts[0]) ? 'index' : $parts[0];
                                $method = str_replace(array('-', '.'), '_', $method);
                                $args   = (array) array_slice($parts, 1);
                                if(is_callable(array($callable, $method)) and method_exists($callable, $method) and $method[0] !== '_'):
                                    call_user_func_array(array($class, $method), $args);
                                    ++$status;
                                endif;
                                break;
                        endswitch;
                    endif;      // pattern matches current request path ?
                endforeach;     // sub loop .
            endif;              // matches current method + ANY ?
        endforeach;             // main loop .

        // catch the full output
        $output = ob_get_clean();

        // return the output or false
        return $status > 0 ? $output : false;
    }

    /**
     * Add regex shortcut(s)
     * @param  mixed    $key
     * @param  string   $value
     */
    public function shortcut($key, $value = null)
    {
        $key = !is_array($key) ? array($key => $value) : $key;
        
        foreach($key as $k => &$v) {
            $this->shortcuts[$k] =  $v;
        }
    }

    /**
     * Remove regex shortcut
     * @param  string    $key
     */
    public function unshortcut($key)
    {
        foreach( (array) $key as $k ) {
            unset($this->shortcuts[$k]);
        }
    }

    /**
     * Check if current requested path matches a pattern
     * @param   string $pattern
     * @param   bool   $strict
     * @return  bool
     */
    public function is($pattern, $strict = true)
    {
        $pattern = $this->prepare($pattern);
        $pattern = $strict ? "/^{$pattern}$/" : "/^$pattern/";
        return (bool) preg_match($pattern, $_SERVER['PATH_INFO']);
    }

    /**
     * Horus simulator
     * @param   bool $state
     * @return  void
     */
    protected function simulator($state = false)
    {
        // We will work on REQUEST_URI & QUERY_STRING
        $ruri = str_replace(chr(0), '', rawurldecode($_SERVER['REQUEST_URI']));
        $qstr = empty($_SERVER['QUERY_STRING']) ? '' : $_SERVER['QUERY_STRING'];

        // Remove the current (dir/filename or /dir) from the ruri
        if( stripos($ruri, $_SERVER['SCRIPT_NAME']) === 0 ) {
            $ruri = substr($ruri, strlen($_SERVER['SCRIPT_NAME']));
        }
        elseif( stripos($ruri, dirname($_SERVER['SCRIPT_NAME'])) === 0 ) {
            $ruri = substr($ruri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }

        // state is yes ?
        if($state != true) return ($_SERVER['PATH_INFO'] = $ruri); 

        // if using routing method (2)
        // let's use '?/' based routing
        if($_SERVER['SIMULATOR_METHOD'] == 2):
            $_SERVER['SCRIPT_ROUTE'] .= '?/';
            if($qstr[0] !== '/') {
                exit(header('Location: ' . $_SERVER['SCRIPT_ROUTE']));
            }
            list(, $_SERVER['PATH_INFO']) = explode('?', $ruri, 2);
            $_SERVER['QUERY_STRING'] = parse_url($_SERVER['PATH_INFO'], PHP_URL_QUERY);
            parse_str($_SERVER['QUERY_STRING'], $_GET);
            $_SERVER['PATH_INFO'] = parse_url($_SERVER['PATH_INFO'], PHP_URL_PATH);
       
        // if using routing method (1)
        // let's use 'index.php/' based routing
        elseif($_SERVER['SIMULATOR_METHOD'] == 1):
            $_SERVER['SCRIPT_ROUTE'] .= basename($_SERVER['SCRIPT_NAME']) . '/';
            if(!isset($_SERVER['PATH_INFO'])) {
                exit(header('Location: ' . $_SERVER['SCRIPT_ROUTE'], true, 302));
            }
        endif;

        // free some memory
        unset($qstr, $ruri);
    }

    /**
     * Add routes
     * @param   string      $method
     * @param   string      $pattern
     * @param   callback    $callback
     * @return  void
     */
    protected function route($method, $pattern, $callable)
    {
        // allow multiple methods / patterns per callback
        $method     =   (array) explode('|', strtoupper($method));
        $pattern    =   (array) $pattern;

        // callable type
        $ctype = null;

        // tmp state
        $tmp = 0;

        // must be a valid callback
        // or valid class instance
        // or valid class name
        if(is_callable($callable)):
            $ctype = 'callback';
        elseif(is_object($callable) and get_class($callable)):
            $ctype = 'used_class';
        elseif(is_string($callable) and class_exists($callable)):
            $ctype = 'class_name';
        elseif(is_file($callable)):
            $callable = $callable;
            $ctype = 'file';
        else:
            foreach($method as &$x)
            {
                if(isset($this->routes[$x][$this->prepare($callable)])) 
                {
                    $callable = $this->routes[$x][$this->prepare($callable)][0];
                    ++$tmp; 
                    break;
                }
            }
            if($tmp < 1) throw new InvalidArgumentException('Invalid callbale');
            else return $this->route(implode('|', $method), $pattern, $callable);
        endif;

        // store routes into $routes
        foreach( $method as &$m )
            foreach($pattern as &$ptt)
                $this->routes[$m][$this->prepare($ptt)] = array($callable, $ctype);

        // free some memry
        unset($method, $pattern, $ctype, $callable, $ctype, $ptt, $m, $x, $tmp);
    }

    /**
     * Prepare a pattern
     * @param   string $pattern
     * @return  string
     */
    protected function prepare($pattern)
    {
        $pattern = $this->basepath . ltrim(rtrim($pattern, '/'), '/') . '/';
        $pattern = str_replace(array('//', '\\\\'), array('/', '\\'), $pattern);
        $pattern = addcslashes($pattern, '/');
        $pattern = str_replace(array('//', '\\\\'), array('/', '\\'), $pattern);
        return str_ireplace(array_keys($this->shortcuts), array_values($this->shortcuts), $pattern);
    }
}

// -------------------------------

/**
 * Horus kernel
 * 
 * @package  Horus
 * @author   Mohammed Al-Ashaal
 * @since    1.0.0
 * @copyright 2014 Mohammed Al-Ashaal
 */
Class Horus extends Horus_Container
{
    /**
     * The output stored here
     * @var string
     */
    protected $output = null;

    /**
     * Run from CLI ?
     * @var string
     */
    public $cli    = false;

    /**
     * events/hooks array
     * @var array
     */
    public $events = array();

    /**
     * Horus instance stored here
     * @var object
     */
    protected static $instance;

    /**
     * Constructor
     * @return  void
     */
    public function __construct()
    {
        // is cli ?
        if(stripos(php_sapi_name(), 'cli') !== false) {
            die('Sorry i\'m a web framework, i must start under a standard web server');
        }

        // is PHP >= PHP 5.2
        if(!version_compare(phpversion(), '5.2', '>=')) {
            die('Sorrty i need PHP at least 5.2');
        }

        // get the output buffer
        // ad it to our output, then clean & start new
        $this->output = ob_get_clean();
        ob_start();

        // some core methods
        $this->router       =   new Horus_Router();
        $this->sql          =   new Horus_SQL;
        $this->sql_table    =   new Horus_SQL_Table($this->sql);
        $this->sql_kv       =   new Horus_SQL_KV($this->sql);
        $this->sql_docstore =   new Horus_SQL_DocStore($this->sql);
        $this->session      =   $this->session();

        // some php ini settings
        ini_set('session.hash_function',    1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly',  1);
        ini_set('session.name', '__HORUS__');

        // set some constants
        defined('ROUTE')    or define('ROUTE', $_SERVER['SCRIPT_ROUTE'], true);
        defined('URL')      or define('URL', $_SERVER['SCRIPT_URL'], true);
        defined('DS') 		or define('DS', DIRECTORY_SEPARATOR, true);
        defined('BASEPATH') or define('BASEPATH', realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . DS, true);
        defined('COREPATH') or define('COREPATH', realpath(dirname(__FILE__)) . DS, false);
        define('HORUS_START', microtime(1), true);
		define('Horus_Version', (float) '6.0', true);

        // some headers
        header("Content-Type: text/html; charset=UTF-8",    TRUE);
        header("X-Powered-By: Horus/".Horus_Version,   TRUE);

        // some other methods
        $this->e404 = create_function
        ('', '
            @ob_get_clean();
            die("
                <title>404 not found</title>
                <h1>Not found</h1> 
                <p> The requested URL was not found on this server . </p>
            ");
        ');

        // force _POST, _GET to be arrays if they are not
        $_GET   =   (array) $_GET;
        $_POST  =   (array) $_POST;

        // store the instance
        self::$instance = $this;
    }

    /**
     * Run the horus application
     * @return void
     */
    public function run()
    {
        // only run if not .
        (!$this->ok) or ($this->ok = true);

        // dispatch all routes [between events]
        $this->trigger('horus.dispatch.before');
        if(($o = $this->router->exec()) !== false) echo $o;
        else $this->e404();
        $this->trigger('horus.dispatch.after');

        // get the output
        // prepare then only send if the request is not head
        $this->output .= ob_get_clean();
        $this->trigger('horus.output.before', array($this));
        $this->output  = $this->trigger('horus.output.filter', $this->output, $this->output);
        (strtoupper($_SERVER['REQUEST_METHOD']) == 'HEAD') or print $this->output;
        $this->trigger('horus.output.after', array($this));

        // end
        ob_get_level() < 1 or ob_end_flush(); exit;
    }

    /**
     * Get horus instance
     * @return  object
     */
    public static function instance()
    {
        return self::$instance;
    }

    /**
     * Listen an event
     * @param   string      $tag
     * @param   callback    $callback
     * @param   integer     $prority
     * @return  void
     */
    public function listen($tag, $callback, $prority = 0)
    {
        $this->events[$tag][$prority][] = $callback;
        ksort($this->events[$tag]);
    }

    /**
     * Trigger event(s)
     * @param   string  $tag
     * @param   mixed   $params
     * @return  mixed
     */
    public function trigger($tag, $params = null, $default = null)
    {
        if(isset($this->events[$tag])) {
            $filtered = null;
            foreach(new ArrayIterator($this->events[$tag]) as $p => $callbacks) {
                  foreach($callbacks as $id => &$callback) {
                        if(is_callable($callback)) {
                            $filtered = call_user_func_array($callback, array_merge((array) $params, array($filtered)));
                        }
                  }
            }
            return empty($filtered) ? $default : $filtered;
        }
        return $default;
    }

    /**
     * Access any input key from any request method
     * @param   mixed   $needle
     * @param   mixed   $default
     * @return  mixed
     */
    public function input($needle = '*', $default = FALSE)
    {
        // is josn ?
        $is_json = create_function('$str', 'return is_array(json_decode($str, true));');

        // get the web inputs
        $winputs = str_replace(chr(0), '', file_get_contents('php://input'));

        // parse inputs for json / basic http query
        if($is_json($winputs)) $_SERVER['__INPUT__'] = json_decode($winputs, true);
        else parse_str($winputs, $_SERVER['__INPUT__']);

        // merge all
        $inputs = array_merge($_GE, $_POST, $_SERVER['__INPUT__']);

        if($needle == '*') return $inputs;
        else return isset($inputs[$needle]) ? $inputs[$needle] : $default;
    }

    /**
     * Register a driectory [and extension] to be
     * autoloaded .
     * @param   string $path
     * @param   string $extension
     * @return  lambada
     */
    public function autoload($path, $extension = 'php')
    {
        $GLOBALS['__ext']   =   '.' . ltrim($extension, '.');
        $GLOBALS['__path']  =   realpath($path) . DS;
        spl_autoload_register($func = create_function('$class', '
            $path = $GLOBALS["__path"];
            $extension = $GLOBALS["__ext"];
            $class = rtrim(ltrim(str_replace(array("\\\\", "/", "_"), DS, $class), DS), DS);
            if(!is_file($file = $path . $class . $extension)) {
                $file = $path . $class . DS . basename($class) . $extension;
            }
            if(is_file($file)) include_once $file;
        '));
        return $func;
    }

    /**
     * Smart redirect method
     * 
     * @since   version 1.0
     * @version 1.0
     * @param   string  $to
     * @param   integer $using
     * @return  void
     */
    public function go($to, $using = 302)
    {
        $scheme = parse_url($to, PHP_URL_SCHEME);
        $to = (!empty($scheme) ? $to : (ROUTE . ltrim($to, '/')));

        if(headers_sent()) 
            return call_user_func_array(__FUNCTION__, array($to, 'html'));
        
        @list($using, $after) = (array) explode(':', $using);
        
        switch(strtolower($using)):
            case 'html':
                echo('<meta http-equiv="refresh" content="'.(int) $after.'; URL='.$to.'">');
                break;
            case 'js':
                echo('<script type="text/javascript">setTimeout(function(){window.location="'.$to.'";}, '.(((int) $after)*1000).');</script>');
                break;
            default:
                exit(header("Location: $to", true, $using));
        endswitch;
    }

    /**
     * Debug (show errors) or not ?
     * @param   bool $state
     * @return  void
     */
    public function debug($state = TRUE)
    {
        if($state)
            error_reporting(E_ALL);
        else
            error_reporting(0);
    }

    /**
     * Simple session manager
     * @return  Horus_Container
     */
    protected function session()
    {
        $session = array
        (
            'start'     =>  create_function('$lifetime = 3600', 'if(session_id() != "") return ; session_set_cookie_params((int)$lifetime); session_start(); session_regenerate_id();'),
            'get'       =>  create_function('$k', 'return isset($_SESSION[$k]) ? $_SESSION[$k] : -1;'),
            'set'       =>  create_function('$k, $v = null', '$a = is_array($k) ? $k : array($k => $v); $_SESSION = array_merge($_SESSION, $a);'),
            'exists'    =>  create_function('$k', '$r = array(); foreach( (array) $k as $x ) $r[] = isset($_SESSION[$x]); return !in_array(false, $r, false);'),
            'end'       =>  create_function('', '@session_unset(); @session_destroy();'),
            'del'       =>  create_function('$k', 'foreach( (array) $k as $x) unset($_SESSION[$x]);'),
            'flush'     =>  create_function('', '$_SESSION = array();'),
            'started'   =>  create_function('', 'return session_id() != "";'),
            'all'       =>  create_function('', 'return (array) @$_SESSION;')
        );
        return new Horus_Container($session);
    }
}

// -----------------------