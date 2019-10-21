<?php
namespace Camoo\Sms\Interfaces;

interface Drivers
{
    const APP_MAGIC_FUNC = ['insert', 'update', 'select', 'delete'];
    const APP_MAGIC_SUFFIX = 'ToSQL';
    const APP_DRIVERS = ['pdo_mysql', 'drizzle_pdo_mysql', 'mysqli', 'pdo_sqlite', 'pdo_pgsql', 'pdo_sqlsrv', 'sqlsrv', 'oci8', 'sqlanywhere'];
    public static function getInstance(array $options=[]);
    public function insert(string $table, array $variables = []);
    public function close();
}
