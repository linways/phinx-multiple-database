<?php

namespace linways\cli\utils;

use PDO;

class DbConnector
{
    protected $connection;

    /**
     * TenantService constructor.
     * @param $dbDetails  object    { name, username, password, host, port }
     */
    public function __construct($dbDetails)
    {
        try {
            $this->connection = new PDO("mysql:host=$dbDetails->host;dbname=$dbDetails->name", $dbDetails->username, $dbDetails->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->connection = null;
    }
}