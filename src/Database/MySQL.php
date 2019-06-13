<?php
declare(strict_types=1);
namespace Camoo\Sms\Database;
use Camoo\Sms\Interfaces\Drivers;

/**
 * Class MySQL
 *
 */
class MySQL implements Drivers
{
    private $table_prefix = '';
    private $dbh_connect = null;
    private $dbh_query  = null;
    private $dbh_error  = null;
    private $dbh_escape = null;
    private $connection = null;
    private static $_ahConfigs = [];

    public static function getInstance(array $options=[])
    {
        static::$_ahConfigs = $options;
        return new self;
    }

    private function getConf()
    {
        $default = ['table_prefix' => '', 'db_host' => 'localhost', 'db_port' => 3306];
        static::$_ahConfigs += $default;
        return static::$_ahConfigs;
    }

    public function getDB()
    {
        list($this->dbh_connect, $this->dbh_query, $this->dbh_error, $this->dbh_escape) = $handlers;
        $this->connection = $this->db_connect($this->getConf());
        return $this;
    }

    private function escape_string($string)
    {
        return $this->is_mysqli()?  call_user_func($this->dbh_escape, $this->connection, trim($string)) : call_user_func($this->dbh_escape, trim($string));
    }

    public function close()
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

    private function db_connect($config)
    {
        if (isset($config['table_prefix'])) {
            $this->table_prefix = $config['table_prefix'];
        }

        if ($this->is_mysqli()) {
            $connection = call_user_func($this->dbh_connect, $config['db_host'], $config['db_user'], $config['db_password'], $config['db_name'], $config['db_port']);
        } else {
            $connection = call_user_func($this->dbh_connect, $config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);
            mysql_select_db($config['db_name']);
        }

        if (!$connection) {
            echo "Failed to connect to MySQL: " . call_user_func($this->dbh_error) . "\n";
            return 0;
        }

        return $connection;
    }

    public function is_mysqli()
    {
        return $this->dbh_connect === 'mysqli_connect';
    }

    protected function query($query)
    {
        if ($this->is_mysqli()) {
            $result = call_user_func($this->dbh_query, $this->connection, $query);
        } else {
            $result = call_user_func($this->dbh_query, $query);
        }

        if (!$result) {
            echo $this->getError();
        }
        return $result;
    }

    protected function getError()
    {
        if ($this->is_mysqli()) {
            return mysqli_error($this->connection);
        } else {
            return mysql_error();
        }
    }

    public function insert(string $table, array $variables = [])
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
        $query = $this->query($sql);
        
        if (!$query) {
            return false;
        } else {
            return true;
        }
    }
}
