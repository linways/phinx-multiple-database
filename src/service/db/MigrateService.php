<?php
namespace linways\cli\service\db;
use Phinx\Console\PhinxApplication;

class MigrateService{

    /**
     * migrate a single database using username and password
     * @param $dbName
     * @param $target
     * @param $host
     * @param $username
     * @param $password
     * @return string
     * @throws \Exception
     */
  public static function migrateDb($dbName, $target, $host, $username, $password){
    $app = new PhinxApplication();
    $_SERVER['PHINX_DBNAME'] = $dbName;
    $_SERVER['PHINX_DBHOST'] = $host;
    $_SERVER['PHINX_DBUSER'] = $username;
    $_SERVER['PHINX_DBPASS'] = $password;
    $_SERVER['PHINX_CONFIG_DIR'] = dirname (getenv('PHINX_CONF'));
    if(empty($_SERVER['PHINX_CONFIG_DIR']))
      throw new \Exception("Environment variable `PHINX_CONF` not found. define it in `.cli.env` file on project root.");

    $wrap = new \Phinx\Wrapper\TextWrapper($app, array(
                // 'config_path' => __DIR__. '/../../../phinx.yml',
                'parser' => 'yaml'
    ));
    try{
      if($target)
        $response = $wrap->getMigrate(null, $target);
      else
        $response = $wrap->getMigrate();
    }catch(\Exception $e){
      throw $e;
      exit(1);
    }
    unset($wrap);
    unset($app);
    return $response;
  }
}
