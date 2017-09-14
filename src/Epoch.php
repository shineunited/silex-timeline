<?php

namespace ShineUnited\Silex\Timeline;

use ShineUnited\Silex\Timeline\Timeline;


class Epoch extends \DateTime {
	private $timeline;

	public function getTimeline() {
		if(!$this->timeline instanceof Timeline) {
			throw new \UnexpectedValueException('Missing/Invalid Timeline Object');
		}

		return $this->timeline;
	}

	public function setTimeline(Timeline $timeline) {
		$this->timeline = $timeline;

		return $this;
	}

	public function compareTo($time) {
		// =0: this and time are equal
		// <0: this is less than time (this is before time)
		// >0: this is greater than time (this is after time)

		$timestamp = $this->getTimestamp();

		if($time instanceof \DateTimeInterface) {
			return $timestamp - $time->getTimestamp();
		}

		if(!is_string($time)) {
			throw new \InvalidArgumentException('Invalid time specified, must be DateTimeInterface or string, ' . gettype($time) . ' given');
		}

		$timeline = $this->getTimeline();

		if(isset($timeline[$time])) {
			// string epoch exists in timeline
			return $timestamp - $timeline->offsetGet($time)->getTimestamp();
		}

		$time = new \DateTime($time, $timeline->offsetGet('now')->getTimezone());
		return $timestamp - $time->getTimestamp();
	}

	public function isBefore($time) {
		if($this->compareTo($time) < 0) {
			return true;
		}

		return false;
	}

	public function isAfter($time) {
		if($this->compareTo($time) >= 0) {
			return true;
		}

		return false;
	}

	/*
	public function isPast() {
		$timeline = $this->getTimeline();
		return $timeline->offsetGet('now')->isAfter($this);
	}

	public function isFuture() {
		$timeline = $this->getTimeline();
		return $timeline->offsetGet('now')->isBefore($this);
	}
	*/

	public static function createFromDateTime(\DateTimeInterface $datetime) {
		return new Epoch(
			'@' . $datetime->getTimestamp(),
			$datetime->getTimezone()
		);
	}
}
