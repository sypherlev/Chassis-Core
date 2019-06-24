<?php

namespace SypherLev\Chassis\Migrate;

use SypherLev\Chassis\Action\CliAction;
use SypherLev\Chassis\Data\SourceBootstrapper;
use SypherLev\Chassis\Request\Cli;
use SypherLev\Chassis\Response\CliResponse;

class Migrate extends CliAction
{
    /* @var BaseMigration */
    private $migrationhandler;
    private $database;
    private $cliresponse;

    public function __construct(Cli $request)
    {
        parent::__construct($request);
        $this->database = $this->getRequest()->fromLineVars(0);
        $this->cliresponse = new CliResponse();
    }

    public function bootstrap()
    {
        $this->setupMigrationHandler();
        $check = $this->migrationhandler->bootstrap($this->getRequest()->fromLineVars(1));
        if ($check) {
            $this->cliresponse->setOutputMessage('Bootstrap output');
            foreach ($check as $idx => $m) {
                $this->cliresponse->insertOutputData($idx, $m);
            }
            $this->cliresponse->out();
        } else {
            throw new \Exception('Error: bootstrap failure, no filename specified or file not found');
        }
    }

    public function backup()
    {
        $this->setupMigrationHandler();
        $check = $this->migrationhandler->backup();
        if ($check) {
            $this->cliresponse->setOutputMessage('Backup output stored in /databackups');
            $this->cliresponse->out();
        } else {
            throw new \Exception('Error: backup failure, backup not complete');
        }
    }

    public function createMigration()
    {
        $this->setupMigrationHandler();
        $check = $this->migrationhandler->create($this->getRequest()->fromLineVars(0), $this->getRequest()->fromLineVars(1));
        if ($check) {
            $this->cliresponse->setOutputMessage('Migration created: ' . $check);
            $this->cliresponse->out();
        } else {
            throw new \Exception('Error: migration could not be created');
        }

    }

    public function migrateUnapplied()
    {
        $this->setupMigrationHandler();
        $check = $this->migrationhandler->migrate($this->getRequest()->fromLineVars(0));
        if (is_array($check)) {
            $this->cliresponse->setOutputMessage('Migration Result');
            if (empty($check)) {
                $check[] = 'No migrations waiting to be applied';
            }
            foreach ($check as $idx => $m) {
                $this->cliresponse->insertOutputData($idx, $m);
            }
            $this->cliresponse->out();
        } else {
            throw new \Exception('Error: migrations could not be completed');
        }
    }

    private function setupMigrationHandler()
    {
        $bootstrapper = new SourceBootstrapper();
        $source = $bootstrapper->generateSource($this->database);
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
}