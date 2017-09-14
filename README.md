Silex Timeline
==============

A Silex package for managing time-dependent content in templates and routes.


Installation
------------

The recommend way to install is through composer:

```bash
$ composer require shineunited/silex-timeline
```

Configuration
-------------

```php
<?php

	require_once(__DIR__ . '/../vendor/autoload.php');

	use Silex\Application;
	use ShineUnited\Silex\Timeline\TimelineServiceProvider;

	$app = new Application();

	$app->register(new TimelineServiceProvider(), [
		'timeline.timezone' => 'UTC',
		'timeline.epochs'   => [
			'epoch1-name' => 'Monday, 15-Aug-2005 15:52:01',
			'epoch2-name' => '2005-08-15T15:52:01',
			'epoch3-name' => new DateTimeImmutable('15-Aug-05 15:52:01')
		]
	]);
```


Usage
-----

```php
	// add epochs directly
	$app['timeline']['epoch4-name'] = 'Monday, 15-Aug-2005 15:52:01';
	$app['timeline']['epoch5-name'] = '15 Aug 2005 15:52:01';
	$app['timeline']['epoch6-name'] = new DateTimeImmutable('15-Aug-05 15:52:01');
```
