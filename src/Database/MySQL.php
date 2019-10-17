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
        list($this->dbh_connect, $this->dbh_query, $this->dbh_error, $this->dbh_escape) = $this->getMysqlHandlers();
        if ($this->connection = $this->db_connect($this->getConf())) {
            return $this;
        }
        return false;
    }

    public function escape_string($string)
    {
        return call_user_func($this->dbh_escape, $this->connection, trim($string));
    }

    public function close()
    {
        return mysqli_close($this->connection);
    }

    private function getMysqlHandlers()
    {
        return ['mysqli_connect', 'mysqli_query', 'mysqli_error', 'mysqli_real_escape_string'];
    }

    protected function db_connect($config)
    {
        if (isset($config['table_prefix'])) {
            $this->table_prefix = $config['table_prefix'];
        }

        try {
            $connection = call_user_func($this->dbh_connect, $config['db_host'], $config['db_user'], $config['db_password'], $config['db_name'], $config['db_port']);
        } catch (\Exception $err) {
            echo "Failed to connect to MySQL: " . $err->getMessage() . "\n";
            return 0;
        }

        return $connection;
    }

    public function query($query)
    {
        $result = call_user_func($this->dbh_query, $this->connection, $query);

        if (!$result) {
            echo $this->getError();
        }
        return $result;
    }

    protected function getError()
    {
        return mysqli_error($this->connection);
    }

    public function insert(string $table, array $variables = [])
    {
        //Make sure the array isn't empty
        if (empty($variables)) {
            return false;
        }
        
        $sql = "INSERT INTO ".$table;
        $fields = [];
        $values = [];
        foreach ($variables as $field => $value) {
            $fields[] = $field;
            $values[] = "'".$this->escape_string($value)."'";
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
