<?php


namespace Tests\additional;

use SypherLev\Chassis\Action\WebAction;
use SypherLev\Chassis\Error\ChassisException;
use SypherLev\Chassis\Response\ApiResponse;
use SypherLev\Chassis\Response\FileResponse;
use SypherLev\Chassis\Response\SlackResponse;
use SypherLev\Chassis\Response\WebResponse;

class TestWebAction extends WebAction
{
    /* @var WebResponse */
    private $responder;

    public function init() {
        $disable = 0;
        try {
            $disable = $this->getRequest()->fromQuery('disable');
        }
        catch (ChassisException $e) {
            // disable not present
            $this->enableExecution();
        }
        if ($disable == 1) {
            $responder = new ApiResponse();
            $responder->setOutputMessage('Disabled');
            $this->disableExecution($responder);
        }
    }

    public function testApi() {
        $request_var = $this->getRequest()->fromQuery('check');
        $api_response = new ApiResponse();
        $api_response->insertOutputData('check', $request_var);
        $api_response->setOutputMessage('Check found');
        $api_response->out();
    }

    public function testNoData() {
        $api_response = new ApiResponse();
        $api_response->dataResponse('check', []);
    }

    public function testNullData() {
        $api_response = new ApiResponse();
        $api_response->dataResponse('check', null);
    }

    public function testBadMessage() {
        $api_response = new ApiResponse();
        $api_response->messageResponse("error message", false);
    }

    public function testBody() {
        $request_var = $this->getRequest()->fromBody('body');
        $api_response = new ApiResponse();
        $api_response->dataResponse('body', $request_var);
    }

    public function testGetEnv() {
        $request_var = $this->getRequest()->fromEnvironment('baseurl');
        $api_response = new ApiResponse();
        $api_response->dataResponse('baseurl', $request_var);
    }

    public function index() {
        $api_response = new ApiResponse();
        $segment = $this->getRequest()->fromSegments(1);
        $api_response->insertOutputData('segment', $segment);
        $api_response->messageResponse('Index targeted correctly');
    }

    public function placeholder() {
        $api_response = new ApiResponse();
        $api_response->dataResponse('placeholder', $this->getRequest()->fromPlaceholders('placeholder'));
    }

    public function testWeb() {
        $responder = new WebResponse();
        $responder->setTemplateDirectory(__DIR__."/templates");
        $responder->setCacheDirectory(__DIR__."/cache");
        $responder->setTemplate('template.twig');
        $responder->insertOutputData('header', 'Website Header');
        $responder->insertBatchData(['subheader' => 'This is a subheading']);
        $responder->out();
    }

    public function fileOutput() {
        $file = new FileResponse();
        $file->setHTTPCode(200);
        file_put_contents(__DIR__."/testfile.txt", "Testing file responses");
        $file->insertOutputData('textfile.txt',__DIR__."/testfile.txt");
        $file->setFileTypeHeader('text/plain');
        $file->out();
    }

    public function testSlackOutput() {
        $slack = new SlackResponse();
        $slack->setSlackParameters(getenv('slack_webhook'), getenv('slack_channel'), 'fakeuser', ':interrobang:');
        $slack->insertOutputData('message', $this->getRequest()->fromBody('to_output'));
        $slack->insertMessage("this is a message");
        $slack->out();
    }

    public function testCookieFiles() {
        $cookie = $this->getRequest()->fromCookie('cookie');
        $file = $this->getRequest()->fromFiles('file1');
        $api_response = new ApiResponse();
        $api_response->insertOutputData('cookie', $cookie);
        $api_response->insertOutputData('filename', $file['name']);
        $api_response->setOutputMessage('Data retrieved');
        $api_response->out();
    }

    public function testChassisExceptionString() {
        try {
            $fakevar = $this->getRequest()->fromQuery('fakevar');
        }
        catch (ChassisException $a) {
            try {
                $fakevar = $this->getRequest()->fromBody('fakevar');
            }
            catch (ChassisException $b) {
                try {
                    $fakevar = $this->getRequest()->fromPlaceholders('fakevar');
                }
                catch (ChassisException $c) {
                    try {
                        $fakevar = $this->getRequest()->fromCookie('fakevar');
                    }
                    catch (ChassisException $d) {
                        try {
                            $fakevar = $this->getRequest()->fromSegments(10);
                        }
                        catch (ChassisException $e) {
                            try {
                                $fakevar = $this->getRequest()->fromFiles('fakevar');
                            }
                            catch (ChassisException $f) {
                                try {
                                    $fakevar = $this->getRequest()->fromEnvironment('fakevar');
                                }
                                catch (ChassisException $g) {
                                    $message = $g->getFormattedMessage();
                                    $api = new ApiResponse();
                                    $api->messageResponse($message);
                                    return;
                                }
                            }
                        }
                    }
                }
            }
        }
        $message = "Exception catch failure in getting parameters";
        $api = new ApiResponse();
        $api->messageResponse($message);
    }
}