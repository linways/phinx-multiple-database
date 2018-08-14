<?php

namespace linways\cli\service;

use Exception;
use linways\cli\utils\DbConnector;

class InitMultiTenantService extends DbConnector
{
    public function init()
    {
        try {
            $createTableQuery = $this->connection->prepare("CREATE TABLE `tenant` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `code` VARCHAR(25) NOT NULL,
                  `name` VARCHAR(250) NOT NULL,
                  `db` VARCHAR(50) NOT NULL,
                  `host` VARCHAR(50) NOT NULL DEFAULT 'localhost',
                  `username` VARCHAR(50) NOT NULL,
                  `password` VARCHAR(50) NOT NULL,
                  `isActive` TINYINT(4) NOT NULL DEFAULT '1',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `code` (`code`),
                  UNIQUE KEY `db` (`db`)
                ) ENGINE=InnoDB");
            $createTableQuery->execute();
            return true;
        } catch (Exception $e) {
            error_log($e);
        }
    }
}