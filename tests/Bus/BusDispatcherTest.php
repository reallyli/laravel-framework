<?php

use Mockery as m;
use Illuminate\Bus\Dispatcher;
use Illuminate\Container\Container;

class BusDispatcherTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testBasicDispatchingOfCommandsToHandlers()
	{
		$container = new Container;
		$handler = m::mock('StdClass');
		$handler->shouldReceive('handle')->once()->andReturn('foo');
		$container->instance('Handler', $handler);
		$dispatcher = new Dispatcher($container);
		$dispatcher->mapUsing(function() { return 'Handler@handle'; });

		$result = $dispatcher->dispatch(new BusDispatcherTestBasicCommand);
		$this->assertEquals('foo', $result);
	}


	public function testCommandsThatShouldBeQueuedAreQueued()
	{
		$container = new Container;
		$dispatcher = new Dispatcher($container, function() {
			$mock = m::mock('Illuminate\Contracts\Queue\Queue');
			$mock->shouldReceive('push')->once();
			return $mock;
		});

		$dispatcher->dispatch(m::mock('Illuminate\Contracts\Queue\ShouldBeQueued'));
	}


	public function testDispatchNowShouldNeverQueue()
	{
		$container = new Container;
		$handler = m::mock('StdClass');
		$handler->shouldReceive('handle')->once()->andReturn('foo');
		$container->instance('Handler', $handler);
		$dispatcher = new Dispatcher($container);
		$dispatcher->mapUsing(function() { return 'Handler@handle'; });

		$result = $dispatcher->dispatch(m::mock('Illuminate\Contracts\Queue\ShouldBeQueued'));
		$this->assertEquals('foo', $result);
	}

}

class BusDispatcherTestBasicCommand {

}

class BusDispatcherTestBasicHandler {
	public function handle(BusDispatcherTestBasicCommand $command)
	{

	}
}