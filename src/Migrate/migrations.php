<?php

$autoload = realpath(__DIR__ .'/../../../../autoload.php');
$env_file = realpath(__DIR__ .'/../../../../../');
require $autoload;

use Dotenv\Dotenv;

echo "Starting Chassis Migration...\n";
if(count($argv) > 1) {
    $switch = $argv[1];
    if(count($argv) <= 2) {
        // no other arguments, exit out
        echo "Error: Database prefix missing\n";
        exit(1);
    }
    // ready to start migrations, let's fire it up
    $dotenv = new Dotenv($env_file);
    $dotenv->load();

    $migrate = new \SypherLev\Chassis\Migrate\Migrate(new \SypherLev\Chassis\Request\Cli());

    try {
        switch ($switch) {
            case "-b" :
            case "--backup" :
                $migrate->backup();
                break;
            case "-c" :
            case "--create" :
                $migrate->createMigration();
                break;
            case "-m" :
            case "--migrate" :
                $migrate->migrateUnapplied();
                break;
            case "-s" :
            case "--bootstrap" :
                $migrate->bootstrap();
                break;
            default :
                throw new Exception("Option not recognized, please refer to usage (execute [bin/chassis] with no other commands");
        }
    }
    catch (Exception $e) {
        echo $e->getMessage()."\n";
        exit(1);
    }
}
else {
    $output = <<<EOT

Usage:
-b <database_prefix>
   -create a backup of the database specified

-m <database_prefix>  
   -run all unapplied migrations on the database specified by the prefix in the .env file

-c <database_prefix> <migration_name>
   -create a migration in the migrations folder with the given name (A-Za-z_0-9)
    for the database specified in the prefix from the .env file

-s <database_prefix> <bootstrap_file.sql>
   -run a bootstrap file on the database specified by the prefix in the .env file

EOT;
    print_r($output);
}
exit(0);
