<?php

namespace SypherLev\Chassis\Data;

use SypherLev\Blueprint\QueryBuilders\MySql\MySqlQuery;
use SypherLev\Blueprint\QueryBuilders\MySql\MySqlSource;
use SypherLev\Blueprint\QueryBuilders\Postgres\PostgresQuery;
use SypherLev\Blueprint\QueryBuilders\Postgres\PostgresSource;
use SypherLev\Blueprint\QueryBuilders\QueryInterface;
use SypherLev\Blueprint\QueryBuilders\SourceInterface;
use SypherLev\Chassis\Error\ChassisException;

class SourceBootstrapper
{
    public $driver = "";
    public $host = "";
    public $database = "";
    public $user = "";
    public $pass = "";
    public $port = "";
    public $cliutil = "";

    public function generateSource(string $identifier, string $pdoclass = "\PDO") : SourceInterface {
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
            $dns = (string)$this->driver . ':dbname=' . (string)$this->database . ";host=" . (string)$this->host;
            $options = [];
            if($this->driver == 'mysql') {
                $options = array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            }
            if($this->port != "") {
                $dns .= ";port=".(string)$this->port;
            }
            $pdo = new $pdoclass($dns, (string)$this->user, (string)$this->pass, $options);
            if($this->driver == 'mysql') {
                return new MySqlSource($pdo);
            }
            else if ($this->driver == 'pgsql') {
                return new PostgresSource($pdo);
            }
            else {
                throw (new ChassisException("Unsupported database driver; cannot generate Source object"));
            }
        }
        else {
            throw (new ChassisException("Invalid or missing database connection parameters"));
        }
    }

    public function generateQuery(string $identifier) : QueryInterface {
        if(getenv($identifier.'_engine') == 'mysql') {
            return new MySqlQuery();
        }
        if(getenv($identifier.'_engine') == 'pgsql') {
            return new PostgresQuery();
        }
        throw (new ChassisException("Unsupported database driver or no database associated with identifier $identifier; cannot generate Query object"));
    }

    private function validateConfig() : bool {
        return !empty($this->driver) &&
            !empty($this->host) &&
            !empty($this->user) &&
            !empty($this->pass) &&
            !empty($this->database);
    }
}