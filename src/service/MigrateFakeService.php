<?php

namespace linways\cli\service;

use Exception;
use linways\cli\utils\DbConnector;
use Symfony\Component\Yaml\Yaml;

class MigrateFakeService extends DbConnector
{

    /**
     * creates an entry in migration table with $version and $name
     * @param $version
     * @param $migrationName
     * @param $dbDetails
     * @return bool
     * @throws Exception
     */
    public function fakeMigration($version, $migrationName)
    {
        if (empty(getenv('PHINX_CONF')))
            throw new \Exception("Environment variable `PHINX_CONF` not found. define it in `.cli.env` file on project root.");

        // Parse phinx.yml file to get migration table name
        $phinxConf = Yaml::parseFile(getenv('PHINX_CONF'));
        $migrationTableName = $phinxConf ["environments"]["default_migration_table"];

        try {
            // Check whether the migration table exists
            $query = $this->connection->prepare("SELECT 1 FROM `$migrationTableName` LIMIT 1");
            $query->execute();
        } catch (\Exception $e) {
            // Creating migration table.
            // TODO: Create this table using Phinx command if exist.
            $createTableQuery = $this->connection->prepare("CREATE TABLE IF NOT EXISTS `$migrationTableName` (
                            `version` bigint(20) NOT NULL,
                            `migration_name` varchar(100) DEFAULT NULL,
                            `start_time` timestamp NULL DEFAULT NULL,
                            `end_time` timestamp NULL DEFAULT NULL,
                            `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
                            PRIMARY KEY (`version`))");
            $createTableQuery->execute();
        }
        $CheckMigratedQuery = $this->connection->prepare("SELECT * from `$migrationTableName` where version='$version'");
        $CheckMigratedQuery->execute();
        if ($CheckMigratedQuery->fetch())
            return false;
        else {
            $sql = $this->connection->prepare("INSERT INTO `$migrationTableName` 
                    (`version`, `migration_name`, `start_time`, `end_time`)
                    VALUES ('$version', '$migrationName', NOW(), NOW())");
            $sql->execute();
            return true;

        }
    }

    /**
     * Removes the entry from migration table.
     * @param $version
     * @param $migrationName
     * @return bool
     * @throws Exception
     */
    public function fakeRevert($version, $migrationName)
    {
        if (empty(getenv('PHINX_CONF')))
            throw new \Exception("Environment variable `PHINX_CONF` not found. define it in `.cli.env` file on project root.");

        // Parse phinx.yml file to get migration table name
        $phinxConf = Yaml::parseFile(getenv('PHINX_CONF'));
        $migrationTableName = $phinxConf ["environments"]["default_migration_table"];

        $query = $this->connection->prepare("delete from `$migrationTableName` where version='$version'");
        $query->execute();
        return true;
    }
}
