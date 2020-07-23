<?php

namespace SypherLev\Chassis\Migrate;

use SypherLev\Chassis\Action\CliAction;
use SypherLev\Chassis\Data\SourceBootstrapper;
use SypherLev\Chassis\Error\ChassisException;
use SypherLev\Chassis\Response\CliResponse;

class MigrateAction extends CliAction
{
    /* @var BaseMigration */
    private $migrationhandler;
    private $database;
    private $responder;
    private $pdoclass = "\PDO";

    public function init()
    {
        $this->responder = new CliResponse();
        try {
            try {
                $switch = $this->getRequest()->fromLineVars(0);
            }
            catch (ChassisException $e) {
                $this->help();
                return;
            }
            if(!in_array($switch, ['backup','create','migrate','bootstrap','help'])) {
                throw new ChassisException("Option not recognized, please refer to bin/chassis help");
            }
            switch ($switch) {
                case "backup" :
                    $this->setupMigrationHandler();
                    $this->backup();
                    break;
                case "create" :
                    $this->setupMigrationHandler();
                    $this->createMigration();
                    break;
                case "migrate" :
                    $this->setupMigrationHandler();
                    $this->migrateUnapplied();
                    break;
                case "bootstrap" :
                    $this->setupMigrationHandler();
                    $this->bootstrap();
                    break;
                default :
                    $this->help();
                    break;
            }
        }
        catch (ChassisException $e) {
            $this->responder->setOutputMessage($e->getMessage());
            $this->disableExecution($this->responder);
        }
    }

    public function index() {
        return;
    }

    public function help() {
        $this->responder->setOutputMessage($this->getInfo());
        $this->responder->out();
    }

    public function setPDOClass($string) {
        $this->pdoclass = $string;
    }

    public function bootstrap()
    {
        try {
            $this->migrationhandler->bootstrap($this->getRequest()->fromOpts('b', true));
            $this->responder->setOutputMessage('Bootstrap complete');
            $this->responder->out();
        }
        catch (ChassisException $e) {
            $this->responder->setOutputMessage($e->getFormattedMessage());
            $this->responder->out();
        }
    }

    public function backup()
    {
        $check = $this->migrationhandler->backup();
        if ($check == "") {
            $this->responder->setOutputMessage('Backup output stored in /databackups');
            $this->responder->out();
        } else {
            $this->responder->setOutputMessage("Error: ".$check);
            $this->responder->out();
        }
    }

    public function createMigration()
    {
        try {
            $filename = $this->migrationhandler->create($this->database, $this->getRequest()->fromOpts('n'));
            $this->responder->setOutputMessage('Migration created: ' . $filename);
            $this->responder->out();
            return;
        }
        catch (ChassisException $e) {
            $this->responder->setOutputMessage("Error: ".$e->getMessage());
            $this->responder->out();
            return;
        }
    }

    public function migrateUnapplied()
    {
        try {
            $check = $this->migrationhandler->migrate($this->database);
            if (is_array($check)) {
                $this->responder->setOutputMessage('Migration Result');
                if (empty($check)) {
                    $check[] = 'No migrations waiting to be applied';
                }
                foreach ($check as $idx => $m) {
                    $this->responder->insertOutputData($idx, $m);
                }
                $this->responder->out();
            } else {
                throw new ChassisException('Error: migrations could not be completed');
            }
        }
        catch (ChassisException $e) {
            $this->responder->setOutputMessage("Error: ".$e->getMessage());
            $this->responder->out();
            return;
        }
    }

    private function setupMigrationHandler()
    {
        $this->database = $this->getRequest()->fromOpts('d', true);
        $bootstrapper = new SourceBootstrapper();
        $source = $bootstrapper->generateSource($this->database, $this->pdoclass);
        $query = $bootstrapper->generateQuery($this->database);
        $this->migrationhandler = new BaseMigration($source, $query);
        $this->migrationhandler->setRawDatabaseParams(
            $bootstrapper->driver,
            $bootstrapper->user,
            $bootstrapper->pass,
            $bootstrapper->database,
            $bootstrapper->host,
            $bootstrapper->port,
            $bootstrapper->cliutil
        );
    }

    private function getInfo() : string {
        $output = <<<EOT
CHASSIS FRAMEWORK MIGRATIONS

Usage: bin/chassis [option] [-d <database>] [-n <migration name>] [-b <bootstrap file location>]

   create -d <database_prefix> -n <migration name>
        Create a migration in the migrations folder with the given name (A-Za-z_0-9)
        for the database specified by the prefix from the .env file
        Example: in .env file as follows
            mysql_engine=mysql
            mysql_host=localhost
            ...etc
        Prefix = "mysql"
        The migration file will be created in /migrations in the format
        <database_prefix>_<unix_timestamp>_<migration name>.sql
        The migrations folder is automatically created in the fileroot as specified in .env

   migrate -d <database_prefix> 
        Run all unapplied migrations in /migrations on the database specified by the prefix in the .env file
        All .sql files with the corresponding prefix in /migrations will be checked and run if not applied
  
   bootstrap -d <database_prefix> -b <bootstrap_file_location.sql>
        Run a bootstrap file on the database specified by the prefix in the .env file
        This bootstrap file should, at a minimum, create the migrations table
        Example as follows:
        
        CREATE TABLE IF NOT EXISTS `migrations` (
          `id` INT NOT NULL AUTO_INCREMENT,
          `filename` VARCHAR(500) NOT NULL,
          `status` INT NOT NULL DEFAULT 0,
          `last_update` INT NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
   backup -d <database prefix>
        Create a backup of the database specified by the prefix in the .env file
        Backups are in the format <database prefix>_<Y-m-d--H-i-s>.sql.gz and are stored in /databackups
        The databackups folder is automatically created in the fileroot as specified in .env
        
   help
        Display this message
EOT;
        return $output;
    }
}