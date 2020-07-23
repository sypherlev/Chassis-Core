<?php

namespace Tests\unit;

use SypherLev\Chassis\Ignition;
use Tests\additional\RouteCollection;

require_once __DIR__ . "/../additional/global_function_overrides.php";
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = new \Dotenv\Dotenv(__DIR__.'/../../');
$dotenv->load();

class MigrateTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
        mkdir(getenv('fileroot')."/migrations");
        mkdir(getenv('fileroot')."/databackups");
    }

    protected function _after()
    {
        $this->delTree(getenv('fileroot')."/migrations");
        $this->delTree(getenv('fileroot')."/databackups");
    }

    // tests
    public function testMigrationInfo()
    {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "CHASSIS FRAMEWORK MIGRATIONS";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testHelp() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "help";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "CHASSIS FRAMEWORK MIGRATIONS";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testUnrecognizedOption()
    {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "fake";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Option not recognized";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testCreateMigration()
    {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "create";
        $argv[] = "-d mysql";
        $argv[] = "-n add_tables";
        $this->delTree(getenv('fileroot')."/migrations");

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Migration created";
        $this->assertTrue(strpos($content, $output) > 0);
        $this->assertTrue(file_exists(getenv('fileroot')."/migrations"));
        $files = scandir(getenv('fileroot')."/migrations");
        $this->assertTrue(count($files) == 3);
    }

    public function testMissingDatabaseParam() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "migrate";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Message: Required option named d not found";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testBootstrap() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "bootstrap";
        $argv[] = "-d mysql";
        $argv[] = "-b bootstrap.sql";
        touch(getenv('fileroot')."/migrations/bootstrap.sql");

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Message: Bootstrap complete";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testBootstrapInsertFailure() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "bootstrap";
        $argv[] = "-d badinsert";
        $argv[] = "-b bootstrap.sql";
        touch(getenv('fileroot')."/migrations/bootstrap.sql");

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Bootstrap process completed but bootstrap record not added to the migrations table";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testBootstrapOptionException() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "bootstrap";
        $argv[] = "-d mysql";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Message: Required option named b not found";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testMissingBootstrap() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "bootstrap";
        $argv[] = "-d mysql";
        $argv[] = "-b bootstrap.sql";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Can't find bootstrap file";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testCreateMigrationException() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "create";
        $argv[] = "-d merror";
        $argv[] = "-n add_tables";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Migration not added to the database";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testMigrate() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "migrate";
        $argv[] = "-d mysql";
        touch(getenv('fileroot')."/migrations/mysql_1234567890_add_tables.sql");
        file_put_contents(getenv('fileroot')."/migrations/mysql_1234567890_add_tables.sql",
            "CREATE TABLE `tablename`;"
        );

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "file => \"mysql_1234567890_add_tables.sql\"";
        $output2 = "No errors found";
        $this->assertTrue(strpos($content, $output) > 0);
        $this->assertTrue(strpos($content, $output2) > 0);
    }

    public function testRunNewMigration() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "migrate";
        $argv[] = "-d newm";
        touch(getenv('fileroot')."/migrations/newm_1234567890_add_tables.sql");
        file_put_contents(getenv('fileroot')."/migrations/newm_1234567890_add_tables.sql",
            "CREATE TABLE `tablename`;"
        );

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "file => \"newm_1234567890_add_tables.sql\"";
        $output2 = "No errors found";
        $this->assertTrue(strpos($content, $output) > 0);
        $this->assertTrue(strpos($content, $output2) > 0);
    }

    public function testRunNewPostrgesMigration() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "migrate";
        $argv[] = "-d newp";
        touch(getenv('fileroot')."/migrations/newp_1234567890_add_tables.sql");
        file_put_contents(getenv('fileroot')."/migrations/newp_1234567890_add_tables.sql",
            "CREATE TABLE `tablename`;"
        );

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "file => \"newp_1234567890_add_tables.sql\"";
        $output2 = "No errors found";
        $this->assertTrue(strpos($content, $output) > 0);
        $this->assertTrue(strpos($content, $output2) > 0);
    }

    public function testMigrationFileNotFound() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "migrate";
        $argv[] = "-d mysql";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Migration halt on missing file";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testNoMigrations() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "migrate";
        $argv[] = "-d empty";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "No migrations waiting to be applied";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testMigrateException() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "migrate";
        $argv[] = "-d erecord";
        touch(getenv('fileroot')."/migrations/mysql_1234567890_add_tables_error.sql");
        file_put_contents(getenv('fileroot')."/migrations/mysql_1234567890_add_tables_error.sql",
            "CREATE TABLE `error`;"
        );

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "ERROR";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testBackup() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "backup";
        $argv[] = "-d mysql";
        $this->delTree(getenv('fileroot')."/databackups");

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Backup output stored in /databackups";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testPostgresBackup() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "backup";
        $argv[] = "-d pgsql";
        $this->delTree(getenv('fileroot')."/databackups");

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Backup output stored in /databackups";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testBackupError() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestMigrateAction";
        $argv[] = "backup";
        $argv[] = "-d merror";


        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "ERROR";
        $this->assertTrue(strpos($content, $output) > 0);
    }

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}