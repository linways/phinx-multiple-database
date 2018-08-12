<?php

namespace linways\cli\service\db;

use PDO;

class TenantService
{
    private $connection;

    /**
     * TenantService constructor.
     * @param $dbDetails  object    { name, username, password, host, port }
     */
    public function __construct($dbDetails)
    {
        try {
            $this->connection = new PDO("mysql:host=$dbDetails->host;dbname=$dbDetails->name", $dbDetails->username, $dbDetails->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            error_log($e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->connection = null;
    }

    /**
     * Returns  all Tenant(s)
     * @return Tenant[]
     * @throws \Exception
     */
    public function getAllTenants()
    {
        $query = $this->connection->prepare("SELECT id, code, db as name, host, username, password FROM tenant WHERE isActive=1");
        try {
            $query->execute();
            $tenants = $query->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        return $tenants;
    }

    /**
     * Returns tenant details by tenant code
     * @param  string $tenantCode [description]
     * @return false|\stdClass
     * @throws \Exception
     */
    public function getTenantByTenantCode($tenantCode)
    {
        if (empty($tenantCode))
            throw new \Exception('TENANT_CODE_IS_REQUIRED', "Tenant code is required");
        $query = $this->connection->prepare("SELECT id, code, db as name, host, username, password FROM tenant WHERE isActive=1 and code='$tenantCode'");
        try {
            $query->execute();
            $tenant = $query->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new \Exception( $e->getMessage(), $e->getCode());
        }
        return $tenant;
    }

}
