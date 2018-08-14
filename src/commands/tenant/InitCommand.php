<?php

namespace linways\cli\command\tenant;

use linways\cli\service\InitMultiTenantService;
use linways\cli\utls\MigrationUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command{
    protected function configure(){
        $this->setName("tenant:init")
             ->setDescription("To initialize tenant management table.");
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $db = MigrationUtils::getDbDetails();
        $imts = new InitMultiTenantService($db);
        if($imts->init())
            $output->writeln('<options=bold;fg=black;bg=green>✓ Initialised tenant database.</>');
    }
}
