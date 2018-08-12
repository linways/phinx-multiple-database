<?php

namespace linways\cli\command\db;

use Phar;
use Exception;
use linways\cli\utls\MigrationUtils;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CreateMigrationCommand extends Command
{

    protected function configure()
    {
        $this->setName("db:create-migration")
            ->setDescription("To create a new migration file.")
            ->setHelp("
Examples:
`<fg=blue;options=bold>db:create-migration NameForMigrationInCamelCase</>` For creating a new migration file
             ")
            ->addArgument('migration_name', InputArgument::REQUIRED, 'Name of the migration in camel case.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrationName = $input->getArgument('migration_name');
        $app = new PhinxApplication();
        $command = ['create'];
        $command += ["name" => $migrationName];
        // For adding custom Migration template
        // if(Phar::running(true))
        //   $command += ["--template" => Phar::running(true) ."/MigrationCustom.template.php.dist"];
        // else
        //   $command += ["--template" => "./MigrationCustom.template.php.dist"];
        try {
            $response = MigrationUtils::executeRun($command, $app);
            print_r($response);
            $output->writeln('<options=bold;fg=black;bg=green>✓ DONE</>');
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            exit(1);
        }
    }
}
