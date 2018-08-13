<?php

namespace linways\cli\command\db;

use linways\cli\utls\MigrationUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use linways\cli\service\db\MigrateService;

class MigrateCommand extends Command{
    protected function configure(){
        $this->setName("db:migrate")
             ->setDescription("To run migrations against required databases.")
             ->setHelp("
Examples:
`<fg=blue;options=bold>db:migrate TENANT1</>` For running migrations for a tenant with code TENANT1
`<fg=blue;options=bold>db:migrate --all</>` For running migrations on all tenants
`<fg=blue;options=bold>db:migrate --db=pro_db -u db_username -p</>` For running migrations on a specific database
`<fg=blue;options=bold>db:migrate --all --target=20171018082229</>` For migrating till a particular migration.
             ")
             ->addArgument('tenant_code', InputArgument::OPTIONAL, 'Tenant code for migration. Works only if TENANT_DB env vars are set.')
             ->addOption('all','a', InputOption::VALUE_NONE, 'For executing migrations against all tenants.Works only if TENANT_DB env vars are set.')
             ->addOption('db', 'd', InputOption::VALUE_REQUIRED, 'for executing migrations against a single db')
             ->addOption('target', 't', InputOption::VALUE_REQUIRED, 'If you want to run upto a particular migration. Target version is the time stamp in the migration file name')
             ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Database host name/ip.', 'localhost')
             ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'Database user name.', 'root')
             ->addOption('pass', 'p', InputOption::VALUE_NONE, 'Prompt for database password. <comment>[default: "root"]</comment>');
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $tenantCode = $input->getArgument('tenant_code');
        $migrateAll = $input->getOption('all');
        $target = $input->getOption('target');
        $db = new \stdClass();
        $db->name = $input->getOption('db');
        $db->host = $input->getOption('host');
        $db->username = $input->getOption('user');
        $db->password = $input->getOption('pass');
        if(empty($tenantCode) && empty($migrateAll) && empty($db->name)){
          $output->writeln("Run `db:migrate --help` for usage");
          exit(1);
        }
        if(!$db->password )
          $db->password  = 'root'; //for setting the default password
        else
          $db->password = MigrationUtils::askDbPassword($this, $input, $output);
        $dbsToMigrate = MigrationUtils::getDbDetailsForMigration($tenantCode, $migrateAll, $db);
        try{
          foreach ($dbsToMigrate as $db) {
            $output->writeln('<question>============= Migrating :<options=bold;fg=black;bg=yellow>'.$db->name .' ['. $db->code.']</>==========</question>');
            $response = MigrateService::migrateDb($db->name, $target, $db->host, $db->username, $db->password);
            print_r($response);
            $output->writeln('<options=bold;fg=black;bg=green>✓ DONE</>');
            flush();
          }
        }catch(\Exception $e){
          $output->writeln('<error>'. $e->getMessage(). '</error>');
        }
    }
}
