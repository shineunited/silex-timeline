<?php

namespace ShineUnited\Silex\Timeline\Tests;

use ShineUnited\Silex\Timeline\Timeline;
use ShineUnited\Silex\Timeline\TimelineServiceProvider;

use Silex\Application;
use Silex\WebTestCase;


class TimelineServiceProviderTest extends WebTestCase {

	public function createApplication() {
		$app = new Application();

		$app->register(new TimelineServiceProvider());

		return $app;
	}

	public function testBeforeBoot() {
		$this->assertInstanceOf(Timeline::class, $this->app['timeline']);
	}

	public function testAfterBoot() {
		$this->app->boot();

		$this->assertInstanceOf(Timeline::class, $this->app['timeline']);
	}
}
