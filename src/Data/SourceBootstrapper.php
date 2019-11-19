<?php

namespace SypherLev\Chassis\Data;

use SypherLev\Blueprint\QueryBuilders\MySql\MySqlQuery;
use SypherLev\Blueprint\QueryBuilders\MySql\MySqlSource;
use SypherLev\Blueprint\QueryBuilders\Postgres\PostgresQuery;
use SypherLev\Blueprint\QueryBuilders\Postgres\PostgresSource;

class SourceBootstrapper
{
    public $driver;
    public $host;
    public $database;
    public $user;
    public $pass;
    public $port;
    public $cliutil;

    public function generateSource($identifier) {
        $this->driver = getenv($identifier.'_engine');
        $this->host = getenv($identifier.'_host');
        $this->database = getenv($identifier.'_dbname');
        $this->user = getenv($identifier.'_username');
        $this->pass = getenv($identifier.'_password');
        $this->port = getenv($identifier.'_port');
        $this->cliutil = getenv($identifier.'_cliutil');

        if($this->port == "") {
            if($this->driver == 'mysql') {
                $this->port = 3306;
            }
            if($this->driver == 'pgsql') {
                $this->port = 5432;
            }
        }

        if($this->validateConfig()) {
            $dns = $this->driver . ':dbname=' . $this->database . ";host=" . $this->host;
            $options = [];
            if($this->driver == 'mysql') {
                $options = array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            }
            if($this->port != "") {
                $dns .= ";port=".$this->port;
            }
            $pdo = new \PDO($dns, $this->user, $this->pass, $options);
            if($this->driver == 'mysql') {
                return new MySqlSource($pdo);
            }
            else if ($this->driver == 'pgsql') {
                return new PostgresSource($pdo);
            }
            else {
                throw (new \Exception("Unsupported database driver"));
            }
        }
        else {
            throw (new \Exception("Invalid or missing database connection parameters"));
        }
    }

    public function generateQuery($identifier) {
        if(getenv($identifier.'_engine') == 'mysql') {
            return new MySqlQuery();
        }
        if(getenv($identifier.'_engine') == 'pgsql') {
            return new PostgresQuery();
        }
        throw (new \Exception("Database engine not supported, or no such database associated with that identifier"));
    }

    private function validateConfig() {
        if(!$this->driver) {
            return false;
        }
        if(!$this->host) {
            return false;
        }
        if(!$this->database) {
            return false;
        }
        if(!$this->user) {
            return false;
        }
        if(!$this->pass) {
            return false;
        }
        return true;
    }
}