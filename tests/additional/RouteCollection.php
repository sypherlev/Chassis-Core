<?php


namespace Tests\additional;

use SypherLev\Chassis\Router;

class RouteCollection extends Router
{
    public function __construct()
    {
        $this->addRoute('GET', '/test/action', 'Tests\\additional\\TestWebAction:testApi');
        $this->addRoute('GET', '/test/nodata', 'Tests\\additional\\TestWebAction:testNoData');
        $this->addRoute('GET', '/test/nulldata', 'Tests\\additional\\TestWebAction:testNullData');
        $this->addRoute('GET', '/test/badmessage', 'Tests\\additional\\TestWebAction:testBadMessage');
        $this->addRoute('GET', '/test/getenv', 'Tests\\additional\\TestWebAction:testGetEnv');
        $this->addRoute('GET', '/test/web', 'Tests\\additional\\TestWebAction:testWeb');
        $this->addRoute('POST', '/test/body', 'Tests\\additional\\TestWebAction:testBody');
        $this->addRoute('GET', '/test/indextest', 'Tests\\additional\\TestWebAction');
        $this->addRoute('POST', '/test/placeholder/{placeholder}', 'Tests\\additional\\TestWebAction:placeholder');
        $this->addRoute('GET', '/test/misroute', 'Tests\\additional\\TestCliAction:testAction');
        $this->addRoute('GET', '/test/exception', 'Tests\\additional\\TestWebAction:testChassisExceptionString');
        $this->addRoute('GET', '/test/cookiefile', 'Tests\\additional\\TestWebAction:testCookieFiles');
        $this->addRoute('GET', '/test/fileoutput', 'Tests\\additional\\TestWebAction:fileOutput');
        $this->addRoute('POST', '/test/slackoutput', 'Tests\\additional\\TestWebAction:testSlackOutput');
        $this->addRoute('POST', '/test/slackerror', 'Tests\\additional\\TestWebAction:testSlackOutput');
    }
}