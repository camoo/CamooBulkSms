<?php
declare(strict_types=1);
namespace Camoo\Sms\Database;

use \Doctrine\DBAL\Configuration;
use \Doctrine\DBAL\DriverManager;
use Camoo\Sms\Exception\CamooSmsException;
use Camoo\Sms\Interfaces\Drivers;
use \Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class AppDb
 * @author CamooSarl
 */
class AppDb implements Drivers
{
    private static $conn;
    private $execute = true;

    public function __call($name, $xargs)
    {
        $sFunc = substr($name, 0, - strlen(Drivers::APP_MAGIC_SUFFIX));
        if (in_array($sFunc, Drivers::APP_MAGIC_FUNC) && substr($name, strlen($sFunc)) === Drivers::APP_MAGIC_SUFFIX && method_exists($this, $sFunc)) {
            $this->execute = false;
            $xQuery = $this->{$sFunc}($xargs[0], $xargs[1]);
            $this->execute = true;
            return empty($xQuery)? false : $xQuery->getSQL();
        }
        throw new CamooSmsException(sprintf('Method %s::%s does not exist', get_class($this), $name));
    }

    public static function getInstance(array $options=[])
    {
        $default = ['db_host' => 'localhost', 'db_port' => 3306, 'driver' => 'pdo_mysql'];
        $options += $default;
        $ahConfigs = [
            'driver'   => $options['driver'],
            'user'     => $options['db_user'],
            'password' => $options['db_password'],
            'dbname'   => $options['db_name'],
            'host'     => $options['db_host'],
            'port'     => $options['db_port'],
        ];
        static::$conn = DriverManager::getConnection($ahConfigs, new Configuration());
        return new self;
    }

    /**
     * Turns the query being built into an insert query that inserts into
     * a certain table
     *
     * <code>
     *     $qb = AppDb::getInstance()
     *         ->insert('sms_table', ['to' => '237612345678', 'from' => 'YourCompany', 'message' => 'Foo bar']);
     * </code>
     *
     * @param string $table The table into which the rows should be inserted.
     * @param mixed $variables variables that should be inserted.
     *
     * @return QueryBuilder instance | bool
     */
    public function insert($table, $variables = [])
    {
        //Make sure the array isn't empty
        if (empty($variables)) {
            return false;
        }

        $query = $this->query()->insert($table);
        $hFields = [];
        $hValues = [];
        foreach ($variables as $field => $value) {
            $hFields[$field] = '?';
            $hValues[] = $value;
        }
        $query->values($hFields)
            ->setParameters($hValues);
        return $this->execute === true? $query->execute() : $query;
    }

    /**
     * Gets doctrine queryBuilder
     * @doc https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/query-builder.html#sql-query-builder
     *
     * @return Doctrine\DBAL\Query\QueryBuilder
     */
    public function query() : QueryBuilder
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * Gets doctrine Connection
     *
     * @return Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return static::$conn;
    }

    /**
     * Closes the connection.
     *
     * @return void
     */
    public function close() : void
    {
        $this->getConnection()->close();
    }
}
