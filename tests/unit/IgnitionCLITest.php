<?php

namespace Tests\unit;

require_once __DIR__ . "/../additional/global_function_overrides.php";
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = new \Dotenv\Dotenv(__DIR__.'/../../');
$dotenv->load();

use SypherLev\Chassis\Error\ChassisException;
use SypherLev\Chassis\Ignition;
use Tests\additional\RouteCollection;

class IgnitionCLITest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testArgv()
    {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:testAction";
        $argv[] = "test param string";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        $this->assertTrue(strpos($content, 'test param string') > 0);
    }

    public function testIndexRoute() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction";
        $argv[] = "Index resolved correctly";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        $this->assertTrue(strpos($content, 'Index resolved correctly') > 0);
    }

    public function testScriptName() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:getScript";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        $this->assertTrue(strpos($content, 'index.php') > 0);
    }

    public function testAllVars() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:getAllVars";
        $argv[] = "firstvar";
        $argv[] = "secondvar";

        $output1 = "firstvar";
        $output2 = "secondvar";
        $output3 = "Message: secondvar";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        $this->assertTrue(strpos($content, $output1) > 0);
        $this->assertTrue(strpos($content, $output2) > 0);
        $this->assertTrue(strpos($content, $output3) > 0);
    }

    public function testOpts() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:testOpts";
        $argv[] = "-f firstvar";
        $argv[] = "-aaa";

        $output1 = "a => false";
        $output2 = "f => \"firstvar\"";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $this->assertTrue(strpos($content, $output1) > 0);
        $this->assertTrue(strpos($content, $output2) > 0);
    }

    public function testLinevarPositionException() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:getVarExceptions";

        try {
            $ignition = new Ignition();
            $ignition->run(new RouteCollection(), true);
        }
        catch (ChassisException $e) {
            $error = "No line var found at position 10";
            $this->assertSame($error, $e->getMessage());
            return;
        }
        $this->fail('testLinevarPositionException failed to catch ChassisException on line var position out of bounds');
    }

    public function testArray() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:arrayOutput";

        $output1 = 'one => "var1"';
        $output2 = 'two => "var2"';
        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        $this->assertTrue(strpos($content, $output1) > 0);
        $this->assertTrue(strpos($content, $output2) > 0);
    }

    public function testFileOutput() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:fileOutput";

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $this->assertTrue(file_exists(__DIR__."/../additional/testfile.txt"));
        $contents = \file_get_contents(__DIR__."/../additional/testfile.txt");
        $this->assertTrue(strpos($contents, 'Testing => "datastring"') > 0);
        $this->assertTrue(strpos($contents, 'Testing Second => "datastring2"') > 0);
        unlink(__DIR__."/../additional/testfile.txt");
    }

    public function testEmailResponse() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:emailDevOutput";

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $storedemails = scandir(__DIR__."/../additional/emails", 1);
        $testemail = array_shift($storedemails);
        $contents = \file_get_contents(__DIR__."/../additional/emails/".$testemail);
        $this->assertTrue(strpos($contents, 'To: test@test.local') >= 0);
        unlink(__DIR__."/../additional/emails/".$testemail);
        rmdir(__DIR__."/../additional/emails");
    }

    public function testLiveEmail() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:emailLiveOutput";
        putenv("devmode=false");

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        putenv("devmode=true");

        $this->assertSame('Email sentEmail sent', $content);
    }

    public function testEmailException() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:emailException";
        putenv("devmode=false");

        try {
            $ignition = new Ignition();
            $ignition->run(new RouteCollection(), true);
        }
        catch (\Exception $e) {
            $this->assertSame('PHPMailer Exception thrown', $e->getMessage());
            putenv("devmode=true");
            return;
        }
        putenv("devmode=true");
        $this->fail("PHPMailer Exception was not caught");
    }

    public function testNonexistantMethod() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:fakemethod";

        try {
            $ignition = new Ignition();
            $ignition->run(new RouteCollection(), true);
        }
        catch (ChassisException $e) {
            $error = "Error: method fakemethod in Tests\additional\TestCliAction does not exist";
            $this->assertSame($error, $e->getMessage());
            return;
        }
        $this->fail('testNonexistantMethod failed to catch ChassisException');
    }

    public function testSlackLogging() {
        global $argv;
        global $_SERVER;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:testLogging";
        $_SERVER['REMOTE_ADDR'] = "0.0.0.0";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        $count = substr_count($content, "https://slack.com");
        $this->assertSame(8, $count);
    }

    public function testFileLogging() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:testFileLogging";

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $this->assertTrue(file_exists('chassis.log'));
        $contents = file_get_contents('chassis.log');
        $this->assertTrue(strpos($contents, 'test file logging') > 0);
        unlink('chassis.log');
    }

    public function testNoLogFileException() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:testFileLogging";
        putenv("logfile=");

        try {
            $ignition = new Ignition();
            $ignition->run(new RouteCollection(), true);
        }
        catch (ChassisException $e) {
            $error = "Logfile location not found in .env; cannot log any output";
            $this->assertSame($error, $e->getMessage());
            putenv('logfile=chassis.log');
            return;
        }
        putenv('logfile=chassis.log');
        $this->fail('testNoLogFileException failed to catch ChassisException on no defined logfile');
    }

    public function testLogException() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:testLogException";
        putenv("slack_webhook=");

        try {
            $ignition = new Ignition();
            $ignition->run(new RouteCollection(), true);
        }
        catch (ChassisException $e) {
            $error = "Cannot log to Slack; channel or webhook setting is missing";
            $this->assertSame($error, $e->getMessage());
            putenv('slack_webhook="https://slack.com"');
            return;
        }
        putenv('slack_webhook="https://slack.com"');
        $this->fail('testChassisLogException failed to catch ChassisException on missing Slack channel');
    }

    public function testUnloggableObjectException() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:testLogUnloggableObject";

        try {
            $ignition = new Ignition();
            $ignition->run(new RouteCollection(), true);
        }
        catch (ChassisException $e) {
            $error = "String cast failure: cannot log object of class stdClass";
            $this->assertSame($error, $e->getMessage());
            return;
        }
        $this->fail('testChassisUnloggableObjectException failed to catch ChassisException on uncastable object');
    }

    public function testSourceMySQL() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:testSourceMysql";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Created mysql:dbname=dbname;host=localhost;port=3306";

        $this->assertSame($output, $content);
    }

    public function testSourcePostgres() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:testSourcePostgres";

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), true);
        $content = ob_get_flush();
        ob_clean();
        $output = "Created pgsql:dbname=dbname;host=localhost;port=5432";

        $this->assertSame($output, $content);
    }

    public function testSourceConstructError() {
        global $argv;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:testConstructExceptions";
        putenv("mysql_engine=notsql");

        try {
            $ignition = new Ignition();
            $ignition->run(new RouteCollection(), true);
        }
        catch (ChassisException $e) {
            $error = "Unsupported database driver; cannot generate Source object";
            $this->assertSame($error, $e->getMessage());
            putenv("mysql_engine=mysql");
            return;
        }
        $this->fail('testSourceConstructError failed to throw ChassisException on unknown database driver');
    }

    public function testSourceQueryException() {
        global $argv;
        global $_SERVER;
        $argv = [];
        $argv[] = "index.php";
        $argv[] = "Tests\additional\TestCliAction:testQueryException";
        putenv("mysql_engine=notsql");

        try {
            $ignition = new Ignition();
            $ignition->run(new RouteCollection(), true);
        }
        catch (ChassisException $e) {
            $error = "Unsupported database driver or no database associated with identifier mysql; cannot generate Query object";
            $this->assertSame($error, $e->getMessage());
            putenv("mysql_engine=mysql");
            return;
        }
        $this->fail('testSourceQueryException failed to throw ChassisException on unknown database driver');
    }
}