<?php

namespace ShineUnited\Silex\Timeline;

use ShineUnited\Silex\Timeline\Timeline;
use ShineUnited\Silex\Timeline\Epoch;

use ShineUnited\Silex\Timeline\Twig\AfterOperator;
use ShineUnited\Silex\Timeline\Twig\BeforeOperator;


class TimelineExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface {
	private $timeline;

	public function __construct(Timeline $timeline) {
		$this->timeline = $timeline;
	}

	public function getName() {
		return 'timeline';
	}

	public function getGlobals() {
		return [
			'timeline' => $this->timeline
		];
	}

	public function getOperators() {
		return [
			[
				'before'    => ['precedence' => 50, 'class' => BeforeOperator::class],
				'after'     => ['precedence' => 50, 'class' => AfterOperator::class]
			],
			[]
		];
	}

	public function getFunctions() {
		return [
			new \Twig_SimpleFunction('isBefore', [$this, 'isBeforeFunction']),
			new \Twig_SimpleFunction('isAfter', [$this, 'isAfterFunction']),
			new \Twig_SimpleFunction('isComplete', [$this, 'isCompleteFunction']),
			new \Twig_SimpleFunction('isUpcoming', [$this, 'isUpcomingFunction'])
		];
	}

	public function getTests() {
		return [
			new \Twig_SimpleTest('complete', [$this, 'isCompleteTest']),
			new \Twig_SimpleTest('upcoming', [$this, 'isUpcomingTest'])
		];
	}

	public function isBeforeFunction($epoch) {
		return $this->timeline->compareEpochs('now', $epoch, 'isBefore');
	}

	public function isAfterFunction($epoch) {
		return $this->timeline->compareEpochs('now', $epoch, 'isAfter');
	}

	public function isCompleteFunction($epoch) {
		return $this->timeline->compareEpochs('now', $epoch, 'isAfter');
	}

	public function isUpcomingFunction($epoch) {
		return $this->timeline->compareEpochs('now', $epoch, 'isBefore');
	}

	public function isCompleteTest($epoch) {
		return $this->timeline->compareEpochs('now', $epoch, 'isAfter');
	}

	public function isUpcomingTest($epoch) {
		return $this->timeline->compareEpochs('now', $epoch, 'isBefore');
	}
}
