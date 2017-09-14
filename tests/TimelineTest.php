<?php

namespace ShineUnited\Silex\Timeline\Tests;

use ShineUnited\Silex\Timeline\Epoch;
use ShineUnited\Silex\Timeline\Timeline;


class TimelineTest extends \PHPUnit_Framework_TestCase {


	public function listEvents() {
		return [
			'perigee'  => '12-Dec-2016 23:28:00',
			'fullmoon' => '14-Dec-2016 00:07:00',
			'apogee'   => '25-Dec-2016 05:56:00',
			'newmoon'  => '29-Dec-2016 06:54:00'
		];
	}

	public function testConstructor() {
		$timeline = new Timeline('UTC', $this->listEvents());
		$this->assertInstanceOf(Timeline::class, $timeline);
	}

	/**
	 *	@dataProvider	offsetExistsProvider
	 */
	public function testOffsetExists($index, $found) {
		$timeline = new Timeline('UTC', $this->listEvents());

		$this->assertEquals($found, $timeline->offsetExists($index));
	}

	/**
	 *	@dataProvider	offsetExistsProvider
	 */
	public function testOffsetExistsArray($index, $found) {
		$timeline = new Timeline('UTC', $this->listEvents());

		$this->assertEquals($found, isset($timeline[$index]));
	}

	/**
	 *	@dataProvider	offsetExistsProvider
	 */
	public function testOffsetExistsObject($index, $found) {
		$timeline = new Timeline('UTC', $this->listEvents());

		$this->assertEquals($found, isset($timeline->$index));
	}

	public function offsetExistsProvider() {
		$tests = [];

		foreach(array_keys($this->listEvents()) as $index) {
			$tests['valid-' . $index] = [$index, true];
		}

		$tests['invalid-01'] = ['not-a-real-key', false];
		$tests['invalid-02'] = ['another-fake-key', false];
		$tests['invalid-03'] = ['yet-another-fake', false];
		$tests['invalid-04'] = ['also-missing', false];

		return $tests;
	}

	/**
	 *	@dataProvider	offsetGetProvider
	 */
	public function testOffsetGet($index, $time) {
		$timeline = new Timeline('UTC', $this->listEvents());

		$timezone = new \DateTimeZone('UTC');
		$datetime = new \DateTime($time, $timezone);

		$this->assertEquals($datetime, $timeline->offsetGet($index));
	}

	/**
	 *	@dataProvider	offsetGetProvider
	 */
	public function testOffsetGetArray($index, $time) {
		$timeline = new Timeline('UTC', $this->listEvents());

		$timezone = new \DateTimeZone('UTC');
		$datetime = new \DateTime($time, $timezone);

		$this->assertEquals($datetime, $timeline[$index]);
	}

	/**
	 *	@dataProvider	offsetGetProvider
	 */
	public function testOffsetGetObject($index, $time) {
		$timeline = new Timeline('UTC', $this->listEvents());

		$timezone = new \DateTimeZone('UTC');
		$datetime = new \DateTime($time, $timezone);

		$this->assertEquals($datetime, $timeline->$index);
	}

	public function offsetGetProvider() {
		$tests = [];

		$timezone = new \DateTimeZone('UTC');
		foreach($this->listEvents() as $index => $time) {
			$tests['check-' . $index] = [$index, $time];
		}

		return $tests;
	}

	/**
	 *	@dataProvider	offsetSetProvider
	 */
	public function testOffsetSet($index, $time) {
		$timeline = new Timeline('UTC', $this->listEvents());

		$timezone = new \DateTimeZone('UTC');
		$datetime = new \DateTime($time, $timezone);

		$timeline->offsetSet($index, $time);

		$this->assertEquals($datetime, $timeline->offsetGet($index));
	}

	/**
	 *	@dataProvider	offsetSetProvider
	 */
	public function testOffsetSetArray($index, $time) {
		$timeline = new Timeline('UTC', $this->listEvents());

		$timezone = new \DateTimeZone('UTC');
		$datetime = new \DateTime($time, $timezone);

		$timeline[$index] = $time;

		$this->assertEquals($datetime, $timeline->offsetGet($index));
	}

	/**
	 *	@dataProvider	offsetSetProvider
	 */
	public function testOffsetSetObject($index, $time) {
		$timeline = new Timeline('UTC', $this->listEvents());

		$timezone = new \DateTimeZone('UTC');
		$datetime = new \DateTime($time, $timezone);

		$timeline->$index = $time;

		$this->assertEquals($datetime, $timeline->offsetGet($index));
	}

	public function offsetSetProvider() {
		$tests = [];

		$indexes = array_keys($this->listEvents());
		$times = array_values($this->listEvents());

		$count = 1;
		foreach($times as $time) {
			$index = 'new-' . str_pad($count++, 3, '0', STR_PAD_LEFT);
			$tests['create-' . $index] = [$index, $time];
		}

		foreach($indexes as $index) {
			$time = array_pop($times);
			$tests['update-' . $index] = [$index, $time];
		}

		return $tests;
	}

	/**
	 *	@dataProvider	offsetUnsetProvider
	 */
	public function testOffsetUnset($index) {
		$timeline = new Timeline('UTC', $this->listEvents());

		$timeline->offsetUnset($index);

		$this->assertFalse($timeline->offsetExists($index));
	}

	/**
	 *	@dataProvider	offsetUnsetProvider
	 */
	public function testOffsetUnsetArray($index) {
		$timeline = new Timeline('UTC', $this->listEvents());

		unset($timeline[$index]);

		$this->assertFalse($timeline->offsetExists($index));
	}

	/**
	 *	@dataProvider	offsetUnsetProvider
	 */
	public function testOffsetUnsetObject($index) {
		$timeline = new Timeline('UTC', $this->listEvents());

		unset($timeline->$index);

		$this->assertFalse($timeline->offsetExists($index));
	}

	public function offsetUnsetProvider() {
		$tests = [];

		foreach(array_keys($this->listEvents()) as $index) {
			$tests['real-' . $index] = [$index];
		}

		for($count = 1; $count <= 10; $count++) {
			$index = 'missing-' . str_pad($count++, 3, '0', STR_PAD_LEFT);
			$tests['fake-' . $index] = [$index];
		}

		return $tests;
	}
}
