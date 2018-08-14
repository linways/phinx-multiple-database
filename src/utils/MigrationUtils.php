<?php

namespace linways\cli\utls;

use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use linways\cli\service\TenantService;

class MigrationUtils
{

    /**
     * To ask the password for the database for migrations
     * @param  Command $command
     * @param  InputInterface $input
     * @param  OutputInterface $output
     * @return string                    password
     */
    public static function askDbPassword(Command $command, InputInterface $input, OutputInterface $output)
    {
        $helper = $command->getHelper('question');
        $question = new Question('Database password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $pass = $helper->ask($input, $output, $question);
        return $pass;
    }


    /**
     * returns an array of database details for running migrations migrations
     * @param $tenantCode       Tenant Code
     * @param $migrateAll       True if migrating all databases
     * @param $db               Name of the database
     * @return \StdClass[]      [{name, username, password, host, port}]
     * @throws \Exception
     */
    public static function getDbDetailsForMigration($tenantCode, $migrateAll, $db)
    {
        $dbsToMigrate = [];
        if (!empty($tenantCode)) {
            //TODO: add option to encrypt the db password when storing in the tenant database
            //get the details about tenant
            $tenantService = new TenantService(self::getDbDetails());
            $singleDb = $tenantService->getTenantByTenantCode($tenantCode);
            $dbsToMigrate[] = $singleDb;
        } else if (!empty($migrateAll)) {
            //TODO: add option to encrypt the db password when storing in the tenant database
            //get all tenant info
            $tenantService = new TenantService(self::getDbDetails());
            $dbsToMigrate = $tenantService->getAllTenants();
        } else if (!empty($db->name)) {
            //migrate for a single database
            $dbsToMigrate[] = $db;
        }
        return $dbsToMigrate;
    }

    /**
     * Creates a DB details object using the values from the env variable
     * @return \StdClass   {name, username, password, host, port}
     */
    public static function getDbDetails(){
        $db = new \StdClass();
        $db->name = getenv('TENANT_DB');
        $db->username = getenv('TENANT_DB_USER');
        $db->password = getenv('TENANT_DB_PASSWORD');
        $db->host = getenv('TENANT_DB_HOST')? getenv('TENANT_DB_HOST'): 'localhost';
        $db->port = getenv('TENANT_DB_PORT')? getenv('TENANT_DB_PORT'): 3306;
        return $db;
    }

    /**
     * To execue a phinx command
     * @param  array $command Command array for execution
     * @param  PhinxApplication $app Instance of Phinx app
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
