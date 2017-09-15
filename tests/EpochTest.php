<?php

namespace ShineUnited\Silex\Timeline\Tests;

use ShineUnited\Silex\Timeline\Epoch;
use ShineUnited\Silex\Timeline\Timeline;


class EpochTest extends \PHPUnit_Framework_TestCase {

	/**
	 *	@dataProvider	constructorProvider
	 */
	public function testConstructor($time) {
		$timezone = new \DateTimeZone('UTC');
		$epoch = new Epoch($time, $timezone);

		$this->assertInstanceOf(Epoch::class, $epoch);
	}

	public function constructorProvider() {
		$tests = [];

		// list of valid time strings

		// compound formats
		$tests['common'] = ['10/Oct/2000:13:55:36 -0700'];
		$tests['exif'] = ['2008:08:07 18:11:31'];
		$tests['iso-yw-1'] = ['2008W27'];
		$tests['iso-yw-2'] = ['2008-W28'];
		$tests['iso-ywd-1'] = ['2008W273'];
		$tests['iso-ywd-2'] = ['2008-W28-3'];
		$tests['mysql'] = ['2008-08-07 18:11:31'];
		$tests['pgsql-1'] = ['2008.197'];
		$tests['pgsql-2'] = ['2008197'];
		$tests['soap-1'] = ['2008-07-01T22:35:17.02'];
		$tests['soap-2'] = ['2008-07-01T22:35:17.03+08:00'];
		$tests['unixts'] = ['@1215282385'];
		$tests['xmlrpc-1'] = ['20080701T22:38:07'];
		$tests['xmlrpc-2'] = ['20080701T9:38:07'];
		$tests['xmlrpc-c1'] = ['20080701t223807'];
		$tests['xmlrpc-c2'] = ['20080701T093807'];
		$tests['wddx'] = ['2008-7-1T9:3:37'];

		return $tests;
	}

	/**
	 *	@dataProvider	hasTimelineProvider
	 */
	public function testHasTimeline(Epoch $epoch, $exists) {
		if($exists) {
			$this->assertTrue($epoch->hasTimeline());
		} else {
			$this->assertFalse($epoch->hasTimeline());
		}
	}

	public function hasTimelineProvider() {
		$tests = [];

		$timezone = new \DateTimeZone('UTC');
		$timeline = new Timeline($timezone);

		$timeline->epoch = '15-Aug-05 15:52:00 +0000';
		$tests['exists-1'] = [$timeline->epoch, true];

		$epoch1 = new Epoch('15-Aug-05 15:52:00 +0000', $timezone);
		$epoch1->setTimeline($timeline);
		$tests['exists-2'] = [$epoch1, true];

		$epoch2 = new Epoch('15-Aug-05 15:52:00 +0000', $timezone);
		$tests['missing-1'] = [$epoch2, false];

		return $tests;
	}

	public function testGetTimeline() {
		$timezone = new \DateTimeZone('UTC');
		$epoch = new Epoch('now', $timezone);

		$timeline = new Timeline($timezone);
		$epoch->setTimeline($timeline);

		$this->assertSame($timeline, $epoch->getTimeline());
	}

	/**
	 *	@expectedException	UnexpectedValueException
	 */
	public function testGetTimelineException() {
		$timezone = new \DateTimeZone('UTC');
		$epoch = new Epoch('now', $timezone);

		$epoch->getTimeline();
	}

	public function testSetTimeline() {
		$timezone = new \DateTimeZone('UTC');
		$epoch = new Epoch('now', $timezone);

		$timeline1 = new Timeline($timezone);
		$epoch->setTimeline($timeline1);

		$timeline2 = new Timeline($timezone);
		$epoch->setTimeline($timeline2);

		$this->assertSame($timeline2, $epoch->getTimeline());
	}

	/**
	 *	@dataProvider	comparisonProvider
	 */
	public function testCompareTo($datetime1, $datetime2, $diff) {
		$timezone = new \DateTimeZone('UTC');

		$epoch1 = new Epoch($datetime1, $timezone);
		$epoch2 = new Epoch($datetime2, $timezone);

		$this->assertEquals($diff, $epoch1->compareTo($epoch2));
	}

	/**
	 *	@dataProvider	comparisonProvider
	 */
	public function testIsBefore($datetime1, $datetime2, $diff) {
		$timezone = new \DateTimeZone('UTC');

		$epoch1 = new Epoch($datetime1, $timezone);
		$epoch2 = new Epoch($datetime2, $timezone);

		if($diff < 0) {
			$this->assertTrue($epoch1->isBefore($epoch2));
		} else {
			$this->assertFalse($epoch2->isBefore($epoch2));
		}
	}

	/**
	 *	@dataProvider	comparisonProvider
	 */
	public function testIsAfter($datetime1, $datetime2, $diff) {
		$timezone = new \DateTimeZone('UTC');

		$epoch1 = new Epoch($datetime1, $timezone);
		$epoch2 = new Epoch($datetime2, $timezone);

		if($diff < 0) {
			$this->assertFalse($epoch1->isAfter($epoch2));
		} else {
			$this->assertTrue($epoch2->isAfter($epoch2));
		}
	}

	/**
	 *	@dataProvider	comparisonProvider
	 */
	public function testIsUpcoming($datetime1, $datetime2, $diff) {
		$timeline = new Timeline('UTC');

		$timeline->now = $datetime1;
		$timeline->test = $datetime2;

		if($diff < 0) {
			$this->assertTrue($timeline->test->isUpcoming());
		} else {
			$this->assertFalse($timeline->test->isUpcoming());
		}
	}

	/**
	 *	@expectedException	UnexpectedValueException
	 */
	public function testIsUpcomingException() {
		$timezone = new \DateTimeZone('UTC');
		$epoch = new Epoch('now', $timezone);

		$epoch->isUpcoming();
	}

	/**
	 *	@dataProvider	comparisonProvider
	 */
	public function testIsComplete($datetime1, $datetime2, $diff) {
		$timeline = new Timeline('UTC');

		$timeline->now = $datetime1;
		$timeline->test = $datetime2;

		if($diff < 0) {
			$this->assertFalse($timeline->test->isComplete());
		} else {
			$this->assertTrue($timeline->test->isComplete());
		}
	}

	/**
	 *	@expectedException	UnexpectedValueException
	 */
	public function testIsCompleteException() {
		$timezone = new \DateTimeZone('UTC');
		$epoch = new Epoch('now', $timezone);

		$epoch->isComplete();
	}

	public function comparisonProvider() {
		$tests = [];

		// each test is an array of the following:
		// datetime1, datetime2, diff (seconds)

		$tests['before1'] = [
			'15-Aug-05 15:52:00 +0000',
			'15-Aug-05 15:52:01 +0000',
			-1
		];

		$tests['before2'] = [
			'15-Aug-05 15:52:00 +0000',
			'15-Aug-05 15:52:02 +0000',
			-2
		];

		$tests['before3'] = [
			'15-Aug-05 15:52:00 +0000',
			'15-Aug-06 15:52:00 +0000',
			-1 * 365 * 24 * 60 * 60
		];

		$tests['before4'] = [
			'15-Aug-05 15:52:00 +0000',
			'15-Sep-05 15:52:00 +0000',
			-1 * 31 * 24 * 60 * 60
		];

		$tests['before5'] = [
			'15-Aug-05 15:52:00 +0000',
			'16-Aug-05 15:52:00 +0000',
			-1 * 24 * 60 * 60
		];

		$tests['match1'] = [
			'15-Aug-05 15:52:00 +0000',
			'15-Aug-05 15:52:00 +0000',
			0
		];

		$tests['match2'] = [
			'15-Aug-05 15:52:00 +0000',
			'15-Aug-05 20:52:00 +0500',
			0
		];

		$tests['match3'] = [
			'15-Aug-05 15:52:00 +0000',
			'15-Aug-05 20:52:00 +0500',
			0
		];

		$tests['match4'] = [
			'15-Aug-05 15:52:00 +0000',
			'16-Aug-05 00:52:00 +0900',
			0
		];

		$tests['match5'] = [
			'15-Aug-05 15:52:00 +0000',
			'15-Aug-05 21:22:00 +0530',
			0
		];

		$tests['after1'] = [
			'15-Aug-05 15:52:01 +0000',
			'15-Aug-05 15:52:00 +0000',
			1
		];

		$tests['after2'] = [
			'15-Aug-05 15:52:02 +0000',
			'15-Aug-05 15:52:00 +0000',
			2
		];

		$tests['after3'] = [
			'15-Aug-06 15:52:00 +0000',
			'15-Aug-05 15:52:00 +0000',
			365 * 24 * 60 * 60
		];

		$tests['after4'] = [
			'15-Sep-05 15:52:00 +0000',
			'15-Aug-05 15:52:00 +0000',
			31 * 24 * 60 * 60
		];

		$tests['after5'] = [
			'16-Aug-05 15:52:00 +0000',
			'15-Aug-05 15:52:00 +0000',
			24 * 60 * 60
		];

		return $tests;
	}

	/**
	 *	@dataProvider	comparisonExceptionProvider
	 *	@expectedException	InvalidArgumentException
	 */
	public function testCompareToException($value) {
		$epoch = new Epoch('now', new \DateTimeZone('UTC'));

		$epoch->compareTo($value);
	}

	public function comparisonExceptionProvider() {
		$tests = [];

		$tests['int1'] = [1112231];
		$tests['int2'] = [34];
		$tests['float1'] = [23123.45234];
		$tests['float2'] = [-342.23];
		$tests['obj1'] = [new \stdClass()];
		$tests['array1'] = [[]];

		return $tests;
	}
}
