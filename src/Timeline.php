<?php

namespace ShineUnited\Silex\Timeline;

use ShineUnited\Silex\Timeline\Epoch;


class Timeline extends \ArrayObject {

	public function __construct($timezone = 'UTC', array $epochs = []) {
		parent::__construct();

		// support epochs as properties
		$this->setFlags(\ArrayObject::ARRAY_AS_PROPS);

		if(!$timezone instanceof \DateTimeZone) {
			$timezone = new \DateTimeZone($timezone);
		}

		// set this ahead of time, so timezone is available
		$this->offsetSet('now', new Epoch('now', $timezone));

		foreach($epochs as $index => $epoch) {
			$this->offsetSet($index, $epoch);
		}
	}

	public function isBefore($time) {
		return $this->offsetGet('now')->isBefore($time);
	}

	public function isAfter($time) {
		return $this->offsetGet('now')->isAfter($time);
	}

	public function compareEpochs($epoch1, $epoch2, $function = 'compareTo') {
		$epoch1 = $this->testEpoch($epoch1);
		$epoch2 = $this->testEpoch($epoch2);

		return $epoch1->$function($epoch2);
	}

	public function offsetExists($index) {
		$index = $this->normalizeIndex($index);
		return parent::offsetExists($index);
	}

	public function offsetGet($index) {
		$index = $this->normalizeIndex($index);
		return parent::offsetGet($index);
	}

	public function offsetSet($index, $epoch) {
		$index = $this->normalizeIndex($index);
		$epoch = $this->normalizeEpoch($epoch);

		parent::offsetSet($index, $epoch);
		$epoch->setTimeline($this);
	}

	public function offsetUnset($index) {
		$index = $this->normalizeIndex($index);
		if($index == 'now') {
			throw new \InvalidArgumentException('Unsetting the \"now\" epoch is forbidden');
		}
		parent::offsetUnset($index);
	}

	protected function normalizeIndex($index) {
		return strtolower(trim($index));
	}

	protected function normalizeEpoch($epoch) {
		if($epoch instanceof Epoch) {
			return $epoch;
		}

		if($epoch instanceof \DateTimeInterface) {
			return Epoch::createFromDateTime($epoch);
		}

		return new Epoch($epoch, $this->offsetGet('now')->getTimezone());
	}

	protected function testEpoch($epoch) {
		if(!$epoch) {
			return $this->offsetGet('now');
		}

		if($epoch instanceof Epoch) {
			return $epoch;
		}

		if($epoch instanceof self) {
			return $epoch->offsetGet('now');
		}

		if($epoch instanceof \DateTimeInterface) {
			return Epoch::createFromDateTime($epoch);
		}

		if(is_object($epoch)) {
			throw new \Exception('Invalid epoch class: ' . get_class($epoch));
		}

		if(!is_string($epoch)) {
			throw new \Exception('Invalid epoch type: ' . gettype($epoch));
		}

		if($this->offsetExists($epoch)) {
			return $this->offsetGet($epoch);
		}

		return new Epoch($epoch, $this->offsetGet('now')->getTimezone());
	}
}
