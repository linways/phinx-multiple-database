<?php

namespace linways\cli\command\db;

use linways\cli\service\db\MigrateFakeService;
use linways\cli\utls\MigrationUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrateFakeCommand extends Command{

    protected function configure(){
        $this->setName("db:migrate-fake")
             ->setDescription("To fake a migration (mark migration as done)")
             ->setHelp("
Examples:
`<fg=blue;options=bold>db:migrate-fake 20171018082229_scheduler_migrations PRO </>` For faking 'scheduler_migrations' for a tenant with code PRO
`<fg=blue;options=bold>db:migrate-fake 20171018082229_scheduler_migrations --all </>` For faking 'scheduler_migrations' on all tenants using nucleus
`<fg=blue;options=bold>db:migrate-fake 20171018082229_scheduler_migrations --db=pro_db -u db_username -p </>` For faking 'scheduler_migrations' on a specific database
`<fg=blue;options=bold>db:migrate-fake 20171018082229_scheduler_migrations PRO --revert</>` For removing the particular migration from migration table. Effectively setting this migration as 'not migrated'
             ")
             ->addArgument('migration_file_name', InputArgument::REQUIRED, 'File name of the migration to be faked. <fg=red;>eg: 20171018082229_scheduler_migrations</>')
             ->addArgument('tenant_code', InputArgument::OPTIONAL, 'Tenant code for faking the migration. Works only with nucleus.')
             ->addOption('all','a', InputOption::VALUE_NONE, 'For faking migrations against all tenants in nucleus.')
             ->addOption('db', 'd', InputOption::VALUE_REQUIRED, 'for faking migrations against a single db (Nucleus not required)')
             ->addOption('revert','r', InputOption::VALUE_NONE, 'For setting a migrations as NOT DONE. NB: This will not revert the actual database changes.')
             ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Database host name/ip.', 'localhost')
             ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'Database user name.', 'root')
             ->addOption('pass', 'p', InputOption::VALUE_NONE, 'promt for database password. <comment>[default: "root"]</comment>');
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $migrationFile = $input->getArgument('migration_file_name');
        $tenantCode = $input->getArgument('tenant_code');
        $migrateAll = $input->getOption('all');
        $revert = $input->getOption('revert');
        $db = new \stdClass();
        $db->name = $input->getOption('db');
        $db->host = $input->getOption('host');
        $db->username = $input->getOption('user');
        $db->password = $input->getOption('pass');

        if(empty($tenantCode) && empty($migrateAll) && empty($db->name)){
          $output->writeln("Run `db:migrate-fake --help` for usage");
          exit(1);
        }
        if(!$db->password)
          $db->password = 'root'; //for setting the default password
        else
          $db->password = MigrationUtils::askDbPassword($this, $input, $output);
        $dbsToMigrate = MigrationUtils::getDbDetailsForMigration($tenantCode, $migrateAll, $db);
        list($version, $migarationName) = explode ('_', $migrationFile, 2);
        $migarationName .= '-faked';
        try{
          foreach ($dbsToMigrate as $db) {
            if($revert){
              $response = MigrateFakeService::fakeRevert($version, $migarationName, $db);
              $output->writeln('<question>============= Reverting Migration(FAKE) :<options=bold;fg=black;bg=yellow>'.$db->tenantDb .' ['. $db->code.']['.$migrationFile.']</>==========</question>');
              $output->writeln('Note that this will not revert the actual database changes caused by this migration.');
            }
            else{
              $output->writeln('<question>============= Migrating(FAKE) :<options=bold;fg=black;bg=yellow>'.$db->tenantDb .' ['. $db->code.']['.$migrationFile.']</>==========</question>');
              $response = MigrateFakeService::fakeMigration($version, $migarationName, $db);
            }
            if($response == false)
              $output->writeln("<comment>$version Already migrated</comment>");
            $output->writeln('<options=bold;fg=black;bg=green>✓ DONE</>');
          }
        }catch(\Exception $e){
          $output->writeln('<error>'. $e->getMessage(). '</error>');
        }
    }
}
