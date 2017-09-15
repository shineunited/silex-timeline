Silex Timeline
==============

A Silex package for managing time-dependent content in templates and routes.

[![Latest Stable Version](https://img.shields.io/packagist/v/shineunited/silex-timeline.svg?style=flat-square)](https://packagist.org/packages/shineunited/silex-timeline)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg?style=flat-square)](https://php.net/)
[![Build Status](https://img.shields.io/travis/shineunited/silex-timeline/master.svg?style=flat-square)](https://travis-ci.org/shineunited/silex-timeline)


## Installation ##

The recommend way to install is through composer:

```bash
$ composer require shineunited/silex-timeline
```

## Configuration ##

```php
<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Silex\Application;
use ShineUnited\Silex\Timeline\TimelineServiceProvider;

$app = new Application();

$app->register(new TimelineServiceProvider(), [
	'timeline.timezone' => 'UTC',
	'timeline.epochs'   => [
		'epoch1' => 'Monday, 15-Aug-2005 15:52:01',
		'epoch2' => '2005-08-15T15:52:01',
		'epoch3' => new \DateTimeImmutable('15-Aug-05 15:52:01')
	]
]);
```


## Usage ##

#### Adding Epochs ####
```php
<?php

// add epochs directly
$app['timeline']['epoch4'] = 'Monday, 15-Aug-2005 15:52:01';
$app['timeline']['epoch5'] = '15 Aug 2005 15:52:01';
$app['timeline']['epoch6'] = new \DateTimeImmutable('15-Aug-05 15:52:01');
```

#### Fetching Epochs ####


###### By Array Reference ######

```php
<?php

$app['timeline']['now'];
$app['timeline']['epoch1'];
$app['timeline']['epoch2'];
$app['timeline']['epoch3'];
```


###### By Param Reference ######

```php
<?php

$app['timeline']->now;
$app['timeline']->epoch1;
$app['timeline']->epoch2;
$app['timeline']->epoch3;
```


#### Comparing Epochs ####

###### Timeline Functions ######

```php
<?php

// isBefore
if($app['timeline']->isBefore('epoch1')) {
	// before 'epoch1'
}

// isAfter
if($app['timeline']->isAfter('epoch1')) {
	// before 'epoch1'
}

// isUpcoming
if($app['timeline']->isUpcoming('epoch1')) {
	// before 'epoch1'
}

// isComplete
if($app['timeline']->isComplete('epoch1')) {
	// before 'epoch1'
}
```

###### Epoch Functions ######

```php
<?php
// isBefore
if($epoch->isBefore('epoch1')) {
	// $epoch is before 'epoch1'
}

// isAfter
if($epoch->isAfter('epoch1')) {
	// $epoch is after 'epoch1'
}
```

###### Direct Comparison ######

```php
<?php

// by array reference
if($app['timeline']['now'] < $app['timeline']['epoch1']) {
	// before 'epoch1'
}

if($app['timeline']['now'] >= $app['timeline']['epoch1']) {
	// after 'epoch1'
}

// by param reference
if($app['timeline']->now < $app['timeline']->epoch1) {
	// before 'epoch1'
}

if($app['timeline']->now >= $app['timeline']->epoch1) {
	// before 'epoch1'
}
```


#### Twig Templates ####


###### Twig Operators ######

```twig
{# before operator #}
{% if before "epoch" %}
	BEFORE
{% else %}
	AFTER
{% endif %}
```

```twig
{# after operator #}
{% if after "epoch" %}
	AFTER
{% else %}
	BEFORE
{% endif %}
```

###### Twig Functions ######

```twig
{# isBefore function #}
{% if isBefore("epoch") %}
	BEFORE
{% else %}
	AFTER
{% endif %}
```

```twig
{# isAfter function #}
{% if isAfter("epoch") %}
	AFTER
{% else %}
	BEFORE
{% endif %}
```

```twig
{# isUpcoming function #}
{% if isUpcoming("epoch") %}
	UPCOMING
{% else %}
	COMPLETE
{% endif %}
```

```twig
{# isComplete function #}
{% if isComplete("epoch") %}
	COMPLETE
{% else %}
	UPCOMING
{% endif %}
```


###### Twig Tests ######

```twig
{# upcoming test #}
{% if "epoch" is upcoming %}
	UPCOMING
{% else %}
	COMPLETE
{% endif %}
```

```twig
{# complete test #}
{% if "epoch" is complete %}
	COMPLETE
{% else %}
	UPCOMING
{% endif %}
```
