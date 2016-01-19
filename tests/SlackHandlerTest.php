<?php
namespace Oponiti\Whoops;

use Exception;
use Maknz\Slack\Client;
use Mockery;
use PHPUnit_Framework_TestCase;
use Whoops\Exception\Inspector;

class SlackHandlerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testDefault()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('send')->once()->with(Mockery::any());

        $slack = new SlackHandler($client);
        $slack->setException(new Exception());

        $this->assertSame(SlackHandler::DONE, $slack->handle());
    }

    public function testFilterWithFalse()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('send')->never();

        $slack = new SlackHandler($client);
        $slack->setException($exception = new Exception());
        $slack->setInspector(new Inspector($exception));

        $slack->filter(function (Exception $exception, Inspector $inspector) {
            return false;
        });

        $this->assertSame(SlackHandler::DONE, $slack->handle());
    }

    public function testFilterWithTrue()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('send')->once();

        $slack = new SlackHandler($client);
        $slack->setException($exception = new Exception());
        $slack->setInspector(new Inspector($exception));

        $slack->setException(new Exception());
        $slack->filter(function (Exception $exception, Inspector $inspector) {
            return true;
        });

        $this->assertSame(SlackHandler::DONE, $slack->handle());
    }
}
