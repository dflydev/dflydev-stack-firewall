<?php

namespace unit;

use Symfony\Component\HttpFoundation\Request;

class MatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testPathMatching()
    {
        $this->matcher = new \Dflydev\Stack\Firewall\Matcher([
            ['path' => '/'],
            ['path' => '/users/', 'exact_match' => true],
            ['path' => '/messages/', 'method' => 'POST'],
            ['path' => '/cats/', 'method' => 'PUT', 'exact_match' => true],
        ]);

        $this->assertMatch('/', Request::create('/test'));
        $this->assertMatch('/', Request::create('/'));

        $this->assertMatch('/users/', Request::create('/users/'));
        $this->assertMatch('/',      Request::create('/users/123')); // no matching for exact_match

        $this->assertMatch('/messages/', Request::create('/messages/123', 'POST'));
        $this->assertMatch('/messages/', Request::create('/messages/',    'POST'));
        $this->assertMatch('/',          Request::create('/messages/',    'GET'));

        $this->assertMatch('/cats/', Request::create('/cats/',    'PUT'));
        $this->assertMatch('/',      Request::create('/cats/123', 'PUT'));
        $this->assertMatch('/',      Request::create('/cats/',    'DELETE'));
        $this->assertMatch('/',      Request::create('/cats/123', 'DELETE'));
    }

    public function testNotMatching()
    {
        $matcher = new \Dflydev\Stack\Firewall\Matcher([['path' => '/api/']]);
        $this->assertNull($matcher->match(Request::create('/blah')));
    }

    public function testSpecificMethodMatchingOrder()
    {
        $this->matcher = new \Dflydev\Stack\Firewall\Matcher([
            ['path' => '/users/', 'method' => 'POST'],
            ['path' => '/users/'],
        ]);

        $this->assertMatch('/users/', Request::create('/users/123'));
    }

    protected function assertMatch($expected, $request)
    {
        $this->assertEquals($expected, $this->matcher->match($request)['path']);
    }
}
