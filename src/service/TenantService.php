<?php

namespace linways\cli\service;

use linways\cli\utils\DbConnector;
use PDO;

class TenantService extends DbConnector
{

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
