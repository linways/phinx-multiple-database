<?php
namespace linways\cli\service\db;
use Exception;
use Symfony\Component\Yaml\Yaml;

class MigrateFakeService{
  /**
   * creates an entry in migration table with $version and $name
   * @param  string $version       version of the migration (timestamp prefix of the migration file)
   * @param  string $migrationName name of the migration
   * @param  array  $dbDetails     database connection details
   * @return boolean
   */
  public static function fakeMigration($version, $migrationName, $dbDetails){
    $db = new \mysqli($dbDetails->dbHost, $dbDetails->dbUsername, $dbDetails->dbPassword, $dbDetails->tenantDb);
    if($db->connect_errno > 0){
        $e = new \Exception('Unable to connect to database [' . $db->connect_error . ']');
        throw $e;
        exit(0);
    }

    // read migration table name from .yml configuration
    if(empty(getenv('PHINX_CONF')))
      throw new \Exception("Environment variable `PHINX_CONF` not found. define it in `.cli.env` file on project root.");

    $phixConf = Yaml::parseFile(getenv('PHINX_CONF'));
    $migrationTableName = $phixConf ["environments"]["default_migration_table"];
    $createTable = "CREATE TABLE IF NOT EXISTS `$migrationTableName` (
    `version` bigint(20) NOT NULL,
    `migration_name` varchar(100) DEFAULT NULL,
    `start_time` timestamp NULL DEFAULT NULL,
    `end_time` timestamp NULL DEFAULT NULL,
    `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`version`))";
    $db->query($createTable);

    $sqlCheckMigrated = "SELECT * from `$migrationTableName` where version='$version'";
    $result = $db->query($sqlCheckMigrated);
    if($result->num_rows > 0){
      return false;
    }else{
      $sql = "INSERT INTO `$migrationTableName`
      (`version`, `migration_name`, `start_time`, `end_time`)
      VALUES ('$version', '$migrationName', NOW(), NOW())";
      if(!$result = $db->query($sql)){
        $e = new \Exception("There was an error running the query [$db->error]\n");
        throw $e;
        exit(0);
      }
      else
        return true;
    }
  }

  /**
   * Removes the entry from migration table.
   * @param  string $version        version of the migration (timestamp prefix of the migration file)
   * @param  string $migrationName  name of the migration
   * @param  array  $dbDetails      database connection details
   * @return true|Exception
   */
  public static function fakeRevert($version, $migrationName, $dbDetails){
    $db = new \mysqli($dbDetails->dbHost, $dbDetails->dbUsername, $dbDetails->dbPassword, $dbDetails->tenantDb);
    if($db->connect_errno > 0){
        $e = new \Exception('Unable to connect to database [' . $db->connect_error . ']');
        throw $e;
        exit(0);
    }

    // read migration table name from .yml configuration
    if(empty(getenv('PHINX_CONF')))
      throw new \Exception("Environment variable `PHINX_CONF` not found. define it in `.cli.env` file on project root.");

    $phixConf = Yaml::parseFile(getenv('PHINX_CONF'));
    $migrationTableName = $phixConf ["environments"]["default_migration_table"];
    $sql = "delete from `$migrationTableName` where version='$version'";
    if(!$result = $db->query($sql)){
      throw new \Exception("There was an error running the query [$db->error]\n");
      exit(0);
    }
    else
      return true;
  }
}
