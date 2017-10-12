<?php

namespace SypherLev\Chassis\Data;

use SypherLev\Blueprint\QueryBuilders\MySql\MySqlSource;
use SypherLev\Blueprint\QueryBuilders\Postgres\PostgresSource;

class SourceBootstrapper
{
    public static function generateSource($identifier) {
        $driver = isset($_ENV[$identifier.'_engine']) ? $_ENV[$identifier.'_engine'] : '';
        $host = isset($_ENV[$identifier.'_host']) ? $_ENV[$identifier.'_host'] : '';
        $database = isset($_ENV[$identifier.'_dbname']) ? $_ENV[$identifier.'_dbname'] : '';
        $user = isset($_ENV[$identifier.'_username']) ? $_ENV[$identifier.'_username'] : '';
        $pass = isset($_ENV[$identifier.'_password']) ? $_ENV[$identifier.'_password'] : '';
        $port = isset($_ENV[$identifier.'_port']) ? $_ENV[$identifier.'_port'] : '';
        try {
            $dns = $driver . ':dbname=' . $database . ";host=" . $host;
            if($port != "") {
                $dns .= ";port=".$port;
            }
            $options = [];
            if($driver == 'mysql') {
                $options = array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            }
            $pdo = new \PDO($dns, $user, $pass, $options);
            if($driver == 'mysql') {
                return new MySqlSource($pdo);
            }
            else if ($driver == 'pgsql') {
                return new PostgresSource($pdo);
            }
            else {
                throw (new \Exception("Unsupported database driver"));
            }
        }
        catch (\Exception $e) {
            throw (new \Exception("Could not connect to the database or create PDO: ". $e->getMessage()));
        }
    }
}