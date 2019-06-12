<?php
declare(strict_types=1);
namespace Camoo\Sms\Database;

/**
 * Class MySQL
 *
 */
class MySQL
{
    private $table_prefix = '';
    private $dbh_connect = null;
    private $dbh_query  = null;
    private $dbh_error  = null;
    private $dbh_escape = null;
    private $connection = null;
    private static $_ahConfigs = [];

    public static function getInstance(array $options)
    {
        static::$_ahConfigs = $options;
        return new self;
    }

    private function getConf()
    {
        return static::$_ahConfigs;
    }

    public function getDB()
    {
        list($this->dbh_connect, $this->dbh_query, $this->dbh_error, $this->dbh_escape) = $handlers;
        $this->connection = $this->doDbConnection($this->getConf());
		return $this;
    }

    private function escape_string($string)
    {
        return $this->is_mysqli()?  call_user_func($this->dbh_escape, $this->connection, trim($string)) : call_user_func($this->dbh_escape, trim($string));
    }

    private function close_connection()
    {
        return $this->is_mysqli()?  mysqli_close($this->connection) : mysql_close();
    }

    private function getMysqlHandlers()
    {
        if (function_exists('mysqli_connect')) {
            return array('mysqli_connect', 'mysqli_query', 'mysqli_error', 'mysqli_real_escape_string');
        }

        if (function_exists('mysql_connect')) {
            return array('mysql_connect', 'mysql_query', 'mysql_error', 'mysql_real_escape_string');
        }
    }

    private function doDbConnection($config)
    {
        if (isset($config['table_prefix'])) {
            $this->table_prefix = $config['table_prefix'];
        }

        if ($success = $this->db_connect($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name'])) {
            return $success;
        }
    }

    private function db_connect($host, $user, $password, $name)
    {
        if ($this->is_mysqli()) {
            $connection = call_user_func($this->dbh_connect, $host, $user, $password, $name, $port, $socket);
        } else {
            $connection = call_user_func($this->dbh_connect, $host, $user, $password, $name);
            mysql_select_db($name);
        }

        if (!$connection) {
            echo "Failed to connect to MySQL: " . call_user_func($this->dbh_error) . "\n";
            return 0;
        }

        return $connection;
    }

    public function fetch_assoc($result)
    {
        $array = array();
        if ($this->is_mysqli()) {
            while ($row = $result->fetch_assoc()) {
                $array[] = $row;
            }
        } else {
            while ($row = mysql_fetch_assoc($result)) {
                $array[] = $row;
            }
        }

        return $array;
    }

    public function is_mysqli()
    {
        return $this->dbh_connect === 'mysqli_connect';
    }

    public function execute_query($query)
    {
        if ($this->is_mysqli()) {
            $result = call_user_func($this->dbh_query, $this->connection, $query);
        } else {
            $result = call_user_func($this->dbh_query, $query);
        }

        if (!$result) {
            echo $this->get_error();
        }

        return $result;
    }

    public function get_error()
    {
        if ($this->is_mysqli()) {
            return mysqli_error($this->connection);
        } else {
            return mysql_error();
        }
    }

    public function insert($table, $variables = array())
    {
        //Make sure the array isn't empty
        if (empty($variables)) {
            return false;
        }
        
        $sql = "INSERT INTO ".$this->table_prefix. $table;
        $fields = array();
        $values = array();
        foreach ($variables as $field => $value) {
            $fields[] = $field;
            $values[] = "'".$value."'";
        }
        $fields = ' (' . implode(', ', $fields) . ')';
        $values = '('. implode(', ', $values) .')';
        
        $sql .= $fields .' VALUES '. $values;
        $query = $this->execute_query($sql);
        
        if (!$query) {
            return false;
        } else {
            return true;
        }
    }
}
