<?php


namespace Tests\additional;

require_once "MockPDO.php";

use SypherLev\Chassis\Action\CliAction;
use SypherLev\Chassis\Data\SourceBootstrapper;
use SypherLev\Chassis\Error\ChassisException;
use SypherLev\Chassis\Logger;
use SypherLev\Chassis\Response\CliResponse;
use SypherLev\Chassis\Response\EmailResponse;

class TestCliAction extends CliAction
{
    public function testAction() {
        $request_var = $this->getRequest()->fromLineVars(0);
        $cli_response = new CliResponse();
        $cli_response->insertOutputData('output', $request_var);
        $cli_response->out();
    }

    public function index() {
        $request_var = $this->getRequest()->fromLineVars(0);
        $cli_response = new CliResponse();
        $cli_response->setOutputMessage($request_var);
        $cli_response->out();
    }

    public function testOpts() {
        $a = $this->getRequest()->fromOpts("aaa");
        $f = $this->getRequest()->fromOpts("f");
        $cli_response = new CliResponse();
        $cli_response->insertOutputData("a", $a);
        $cli_response->insertOutputData('f', $f);
        $cli_response->out();
    }

    public function getScript() {
        $cli_response = new CliResponse();
        $cli_response->setOutputMessage($this->getRequest()->getScriptName());
        $cli_response->out();
    }

    public function getAllVars() {
        $cli_response = new CliResponse();
        $cli_response->insertOutputData('argv', $this->getRequest()->getAllLineVars());
        $cli_response->setOutputMessage($this->getRequest()->fromLineVars(1));
        $cli_response->out();
    }

    public function getVarExceptions() {
        $this->getRequest()->fromLineVars(10);
    }

    public function fileOutput() {
        $cli_response = new CliResponse();
        $cli_response->insertOutputData('Testing', 'datastring');
        $cli_response->setFileOutput(__DIR__."/testfile.txt");
        $cli_response->out();

        $cli_response = new CliResponse();
        $cli_response->insertOutputData('Testing Second', 'datastring2');
        $cli_response->setFileOutput(__DIR__."/testfile.txt", false);
        $cli_response->out();
    }

    public function arrayOutput() {
        $cli_response = new CliResponse();
        $cli_response->insertOutputData('array', ['one' => "var1", 'two' => 'var2']);
        $cli_response->out();
    }

    public function emailDevOutput() {
        $emailResponse = new EmailResponse();
        $emailResponse->setDevMode(true);
        $emailResponse->setEmailFolder(__DIR__."/emails");
        $emailResponse->attachFile("/path/to/file", "testfile.txt");
        $emailResponse->setEmailParams('test@test.local', 'SubjectLine', 'Message body', 'test2@test.local');
        $emailResponse->out();
    }

    public function emailLiveOutput() {
        $mailer = new MockMailer();
        $emailResponse = new EmailResponse($mailer);
        $emailResponse->setEmailFolder(__DIR__."/emails");
        $emailResponse->attachFile("/path/to/file");
        $emailResponse->setEmailParams('test@test.local,test2@test.local', 'SubjectLine', 'Message body', 'test2@test.local');
        $emailResponse->out();
        $emailResponse->sendPlainText();
        $emailResponse->out();
    }

    public function emailException() {
        $mailer = new MockMailer();
        $mailer->throwError = true;
        $emailResponse = new EmailResponse($mailer);
        $emailResponse->setEmailParams('test@test.local', 'SubjectLine', 'Message body', 'test2@test.local');
        $emailResponse->out();
    }

    public function testLogging() {
        $logger = new Logger();
        $logger->setLogType(Logger::TO_SLACK);
        $logger->log("This is a logging message");
        $logger->log(new \Exception("Testing exception logging"));
        $logger->log(array('test', 'array'));
        $logger->log(true);
        $logger->log(false);
        $logger->log(null);
        $logger->log(new StringTest());
        file_put_contents(__DIR__."/testfile.txt", "testing string");
        $handle = fopen(__DIR__."/testfile.txt", "r");
        $logger->log($handle);
        fclose($handle);
        unlink(__DIR__."/testfile.txt");
    }

    public function testFileLogging() {
        $logger = new Logger(Logger::TO_FILE);
        $logger->log('test file logging');
    }

    public function testLogUnloggableObject() {
        $logger = new Logger(Logger::TO_FILE);
        $testclass = new \stdClass();
        $testclass->property = "test property";
        $logger->log($testclass);
    }

    public function testLogException() {
        $logger = new Logger(Logger::TO_SLACK);
        $logger->log("This is a logging message");
    }

    public function testSourceMySql() {
        $source = new SourceBootstrapper();
        $source->generateSource('mysql', 'Tests\additional\MockPDO');
        $query = $source->generateQuery('mysql');
    }

    public function testSourcePostgres() {
        $source = new SourceBootstrapper();
        $source->generateSource('pgsql', 'Tests\additional\MockPDO');
        $query = $source->generateQuery('pgsql');
    }

    public function testConstructExceptions() {
        $source = new SourceBootstrapper();
        try {
            $source->generateSource('fake', 'Tests\additional\MockPDO');
        }
        catch (ChassisException $e) {
            $source->generateSource('mysql', 'Tests\additional\MockPDO');
        }
    }

    public function testQueryException() {
        $source = new SourceBootstrapper();
        $source->generateQuery('mysql');
    }
}

class MockMailer extends \PHPMailer {

    public $throwError = false;
    public $ErrorInfo = "PHPMailer Exception thrown";

    public function send()
    {
        if($this->throwError) {
            return false;
        }
        else {
            echo "Email sent";
            return true;
        }
    }
}

class StringTest {
    function __toString()
    {
        return self::class;
    }
}