<?php

namespace linways\cli\utls;

use com\linways\nucleus\core\dto\Tenant;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command;
use com\linways\nucleus\core\service\TenantService;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class MigrationUtils{

  /**
   * To ask the password for the database for migrations
   * @param  Command          $command [description]
   * @param  InputInterface  $input   [description]
   * @param  OutputInterface $output  [description]
   * @return mixed
   */
  public static function askDbPassword(Command $command, InputInterface $input, OutputInterface $output){
    $helper = $command->getHelper('question');
    $question = new Question('Database password: ');
    $question->setHidden(true);
    $question->setHiddenFallback(false);
    $pass = $helper->ask($input, $output, $question);
    return $pass;
  }

  /**
   * returns an array of database details for running migrations migrations
   * @param  string $tenantCode
   * @param  boolean $migrateAll  true if migrating all databases
   * @param  object $db
   * @return Tenant[]
   */
  public static function getDbDetailsForMigration($tenantCode,$migrateAll, $db ){
    $dbsToMigrate = [];
    if(!empty($tenantCode)){
      //get the details about tenant
      $singleDb = TenantService::getInstance()->getTenantByTenantCode($tenantCode);
      $dbsToMigrate[] = $singleDb;
    }else if (!empty($migrateAll)){
      //get all tenant info
      $dbsToMigrate = TenantService::getInstance()->getAllTenants();
    }else if (!empty($db->name)){
      //migrate for a single database
      $singleDb = new Tenant();
      $singleDb->tenantDb = $db->name;
      $singleDb->dbHost = $db->host;
      $singleDb->dbUsername = $db->username;
      $singleDb->dbPassword = $db->password;
      $dbsToMigrate[] = $singleDb;
    }
    return $dbsToMigrate;
  }

  /**
   * To execue a phinx command
   * @param  array            $command Command array for execution
   * @param  PhinxApplication $app     Instance of Phinx app
   * @return string                    Response of the executed command
   */
  public static function executeRun(array $command, PhinxApplication $app)
  {
      // Output will be written to a temporary stream, so that it can be
      // collected after running the command.
      $stream = fopen('php://temp', 'w+');
      // Execute the command, capturing the output in the temporary stream
      // and storing the exit code for debugging purposes.
      $exit_code = $app->doRun(new ArrayInput($command), new StreamOutput($stream));
      // Get the output of the command and close the stream, which will
      // destroy the temporary file.
      $result = stream_get_contents($stream, -1, 0);
      fclose($stream);
      return $result;
  }
}
