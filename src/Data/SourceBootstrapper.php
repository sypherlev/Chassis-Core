<?php

namespace SypherLev\Chassis\Data;

use SypherLev\Blueprint\QueryBuilders\MySql\MySqlSource;
use SypherLev\Blueprint\QueryBuilders\Postgres\PostgresSource;

class SourceBootstrapper
{
    public $driver;
    public $host;
    public $database;
    public $user;
    public $pass;
    public $port;

    public function generateSource($identifier) {
        $this->driver = isset($_ENV[$identifier.'_engine']) ? $_ENV[$identifier.'_engine'] : '';
        $this->host = isset($_ENV[$identifier.'_host']) ? $_ENV[$identifier.'_host'] : '';
        $this->database = isset($_ENV[$identifier.'_dbname']) ? $_ENV[$identifier.'_dbname'] : '';
        $this->user = isset($_ENV[$identifier.'_username']) ? $_ENV[$identifier.'_username'] : '';
        $this->pass = isset($_ENV[$identifier.'_password']) ? $_ENV[$identifier.'_password'] : '';
        $this->port = isset($_ENV[$identifier.'_port']) ? $_ENV[$identifier.'_port'] : '';
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

    private function validateConfig() {
        if($this->driver == '') {
            return false;
        }
        if($this->host == '') {
            return false;
        }
        if($this->database == '') {
            return false;
        }
        if($this->user == '') {
            return false;
        }
        if($this->pass == '') {
            return false;
        }
        return true;
    }
}