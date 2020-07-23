<?php

namespace Tests\unit;

require_once __DIR__ . "/../additional/global_function_overrides.php";
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = new \Dotenv\Dotenv(__DIR__.'/../../');
$dotenv->load();

use SypherLev\Chassis\Error\ChassisException;
use SypherLev\Chassis\Ignition;
use Tests\additional\RouteCollection;

class IgnitionWebTest extends \Codeception\Test\Unit
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
    public function testFromQuery() {
        ob_start();
        global $_SERVER, $_GET;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/action?check=1";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/action?check=1";
        $_GET['check'] = 1;

        $output = [
            'message' => 'Check found',
            'data' => ['check' => 1]
        ];

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame(json_encode($output), $content);
    }

    public function testFromPlaceholders() {
        ob_start();
        global $_SERVER, $_GET;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/placeholder/testingstring";
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = "/test/placeholder/testingstring";

        $output = [
            'message' => 'Data retrieved',
            'data' => ['placeholder' => 'testingstring']
        ];

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame(json_encode($output), $content);
    }

    public function testFromBody() {
        ob_start();
        global $_SERVER, $_POST;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/body";
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = "/test/body";
        $_POST['body'] = "postbody";

        $output = [
            'message' => 'Data retrieved',
            'data' => ['body' => 'postbody']
        ];

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame(json_encode($output), $content);
    }

    public function testNoData() {
        ob_start();
        global $_SERVER;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/nodata";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/nodata";

        $output = [
            'message' => 'No data',
            'data' => ['check' => []]
        ];

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame(json_encode($output), $content);
    }

    public function testNullData() {
        ob_start();
        global $_SERVER;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/nulldata";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/nulldata";

        $output = [
            'message' => 'Data not found',
            'data' => []
        ];

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame(json_encode($output), $content);
    }

    public function testBadMessage() {
        ob_start();
        global $_SERVER;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/badmessage";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/badmessage";

        $output = [
            'message' => 'error message',
            'data' => []
        ];

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame(json_encode($output), $content);
    }

    public function testFromCookieAndFiles() {
        ob_start();
        global $_SERVER, $_COOKIE, $_FILES;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/cookiefile";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/cookiefile";
        $_COOKIE['cookie'] = 'cookiecontent';
        $_FILES['file1'] = [
            'name' => 'testfile.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/tempname',
            'error' => UPLOAD_ERR_OK,
            'size' => 98174
        ];

        $output = [
            'message' => 'Data retrieved',
            'data' => ['cookie' => 'cookiecontent', 'filename' => 'testfile.txt']
        ];

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame(json_encode($output), $content);
    }

    public function testFromEnv() {
        global $_SERVER;
        ob_start();
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/getenv";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/getenv";

        $output = 'http:\/\/chassis.local';

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testWebResponse() {
        ob_start();
        global $_SERVER, $_GET;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/web";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/web";
        $output = <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Template</title>
</head>
<body>
<h1>Website Header</h1>
<h3>This is a subheading</h3>
</body>
</html>
EOD;

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame($output, $content);
    }

    public function testWebWithCache() {
        global $_SERVER, $_GET;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/web";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/web";
        putenv('devmode=false');

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        putenv('devmode=true');
        $files = scandir(__DIR__."/../additional/cache");
        $this->assertTrue(count($files) > 2);
        $this->delTree(__DIR__."/../additional/cache");
    }

    public function testDisabledExecution() {
        ob_start();
        global $_SERVER, $_GET;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/indextest?disable=1";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/indextest";
        $_GET['disable'] = 1;

        $output = [
            'message' => 'Disabled',
            'data' => []
        ];

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame(json_encode($output), $content);
    }

    public function testWebIndexMessageResponse() {
        ob_start();
        global $_SERVER, $_GET;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/indextest";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/indextest";

        $output = [
            'message' => 'Index targeted correctly',
            'data' => [
                'segment' => 'indextest'
            ]
        ];

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame(json_encode($output), $content);
    }

    public function testWebNoHTTPHost() {
        ob_start();
        $output = "Error: No host detected; exiting";
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame($output, $content);
    }

    public function testWebOPTIONS() {
        ob_start();
        $output = "";
        global $_SERVER, $_GET;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/indextest";
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame($output, $content);
    }

    public function testRouterNotSet() {
        ob_start();
        global $_SERVER, $_GET;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/indextest";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/indextest";

        $output = "500 Internal server error: Application router is not available.";

        $ignition = new Ignition();
        $ignition->run();
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame($output, $content);
    }

    public function testMissingRoute404() {
        ob_start();
        global $_SERVER, $_GET;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/fake/route";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/fake/route";

        $output = "404 Page not found.";

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame($output, $content);
    }

    public function testRequestMethodNotAllowed() {
        ob_start();
        global $_SERVER, $_GET;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/indextest";
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = "/test/indextest";

        $output = "405 HTTP method not allowed.";

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame($output, $content);
    }

    public function testHTTPSRedirect() {
        ob_start();
        global $_SERVER, $_GET;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/indextest";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/indextest";
        putenv("alwaysssl=true");

        $output = "Location: https://testing.local/test/indextest";

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        putenv("alwaysssl=false");
        $this->assertSame($output, $content);
    }

    public function testHTTPSSetting() {
        global $_SERVER;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "https://testing.local/test/indextest";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/indextest";
        $_SERVER['HTTPS'] = 'on';
        putenv("alwaysssl=true");

        $output = [
            'message' => 'Index targeted correctly',
            'data' => [
                'segment' => 'indextest'
            ]
        ];

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertSame(json_encode($output), $content);

        unset($_SERVER['HTTPS']);
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['HTTP_X_FORWARDED_SSL'] = 'on';

        ob_start();
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        putenv("alwaysssl=false");
        $this->assertSame(json_encode($output), $content);
    }

    public function testCliMisroute() {
        global $_SERVER;
        ob_start();
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/misroute";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/misroute";

        $output = "Routing config failure; this route will only respond to command line input";

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertEquals($output, $content);
    }

    public function testChassisGetException() {
        global $_SERVER;
        ob_start();
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/exception";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/exception";

        $output = "Cannot get parameter fakevar from environment";

        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $this->assertTrue(strpos($content, $output) > 0);
    }

    public function testFileResponse() {
        global $_SERVER, $_GET;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/fileoutput";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = "/test/fileoutput";
        putenv("ob_counter=1");

        $output = "Testing file responses\nReadfile success";
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $file = file_get_contents(__DIR__."/../additional/testfile.txt");
        $this->assertSame($output, $file);
        if (ob_get_contents()) ob_end_clean();
        unlink(__DIR__."/../additional/testfile.txt");
    }

    public function testSlackResponse() {
        global $_SERVER, $_POST;
        ob_start();
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/slackoutput";
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = "/test/slackoutput";
        $_POST['to_output'] = "testing output string";
        $ignition = new Ignition();
        $ignition->run(new RouteCollection(), false);
        $content = ob_get_flush();
        ob_clean();
        $output = '"https://slack.com"';
        $this->assertSame($content, $output);
    }

    public function testSlackError() {
        global $_SERVER, $_POST;
        $_SERVER['HTTP_HOST'] = "testing.local";
        $_SERVER['HTTP_REFERER'] = "http://testing.local/test/slackoutput";
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = "/test/slackoutput";
        $_POST['to_output'] = ['testing','array','loading'];
        putenv('curl_throw_exception=true');
        try {
            $ignition = new Ignition();
            $ignition->run(new RouteCollection(), false);
        }
        catch (ChassisException $e) {
            $error = "Curl request to Slack has failed with error: ";
            $this->assertSame($error, $e->getMessage());
            return;
        }
        $this->fail('Slack exception failed to be triggered');
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