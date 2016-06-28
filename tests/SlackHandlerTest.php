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
        $slack->filter(function (Exception $exception, Inspector $inspector) {
            return true;
        });

        $this->assertSame(SlackHandler::DONE, $slack->handle());
    }

    public function testHandleString()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('send')->with(Mockery::on(function ($contents) {
            $this->assertEquals(<<<TEXT
Array[
],

TEXT
, $contents);
            return true;
        }))->once();
        
        $slack = new SlackHandler($client, [
            'template' => __DIR__ . '/stub-depth-template.php'
        ]);
        $slack->setException($exception = new Exception('', 0, null));
        $slack->setInspector(new Inspector($exception));
        $slack->setArgument('tests', []);
        $slack->handle();
    }

    public function testHandleStringWithDepth()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('send')->with(Mockery::on(function ($contents) {
            $this->assertEquals(<<<TEXT
Array[
    [0] => Array[
        [depth2] => Array[
            ...many depth...
        ],
    ],
    [1] => Array[
        [name] => String(111),
    ],
    [2] => Array[
        [name] => String(222),
    ],
    ...many count...
],

TEXT
                , $contents);
            return true;
        }))->once();

        $slack = new SlackHandler($client, [
            'template' => __DIR__ . '/stub-depth-template.php',
            'max_array_depth' => 2,
            'max_array_count' => 3,
        ]);
        $slack->setException($exception = new Exception('', 0, null));
        $slack->setInspector(new Inspector($exception));
        $slack->setArgument('tests', [
            [
                'depth2' => [
                    'depth3' => [
                        'depth4' => [

                        ],
                    ],
                ],
            ],
            ['name' => '111',],
            ['name' => '222',],
            ['name' => '333',],
            ['name' => '444',],
            ['name' => '555',],
        ]);

        $slack->handle();
    }
}
