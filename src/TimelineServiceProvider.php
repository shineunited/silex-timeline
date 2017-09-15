<?php

namespace ShineUnited\Silex\Timeline;

use ShineUnited\Silex\Timeline\Timeline;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

use Silex\Application;
use Silex\Api\BootableProviderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;


class TimelineServiceProvider implements ServiceProviderInterface, BootableProviderInterface {

	public function register(Container $app) {
		$app['timeline'] = function() use ($app) {
			$timeline = new Timeline($app['timeline.timezone'], $app['timeline.epochs']);

			return $timeline;
		};

		if(!isset($app['timeline.timezone'])) {
			$app['timeline.timezone'] = 'UTC';
		}

		if(!isset($app['timeline.epochs'])) {
			$app['timeline.epochs'] = [];
		}

		if(!isset($app['timeline.debug'])) {
			$app['timeline.debug'] = $app->factory(function() use ($app) {
				return $app['debug'];
			});
		}

		if(!isset($app['timeline.debug.route'])) {
			$app['timeline.debug.route'] = '/debug';
		}

		if(!isset($app['timeline.debug.cookie'])) {
			$app['timeline.debug.cookie'] = 'debug';
		}
	}

	public function boot(Application $app) {
		// extend twig if present
		if(isset($app['twig'])) {
			$app['twig'] = $app->extend('twig', function($twig, $app) {
				$twig->addExtension(new TimelineExtension($app['timeline']));
			});
		}

		if($app['timeline.debug']) {
			$app->before(function(Request $request) use ($app) {
				if($request->cookies->has($app['timeline.debug.cookie'])) {
					$debug = $request->cookies->get($app['timeline.debug.cookie']);

					if($debug) {
						$app['timeline']['now'] = $debug;
					}
				}
			});

			$app->match($app['timeline.debug.route'], function(Request $request) use ($app) {
				if($request->query->has('reset')) {
					$response = $app->redirect($app['timeline.debug.route']);

					if($request->cookies->has($app['timeline.debug.cookie'])) {
						$response->headers->clearCookie($app['timeline.debug.cookie'], '/');
					}

					return $response;
				}

				if($request->query->has('ts')) {
					$timestamp = $request->query->get('ts');

					$response = $app->redirect($app['timeline.debug.route']);

					$cookie = new Cookie($app['timeline.debug.cookie'], '@' . $timestamp, 0, '/');
					$response->headers->setCookie($cookie);

					return $response;
				}

				if($request->query->has('date')) {
					$datetime = new \DateTime($request->query->get('date'));
					$timestamp = $datetime->getTimestamp();

					$response = $app->redirect($app['timeline.debug.route']);

					$cookie = new Cookie($app['timeline.debug.cookie'], '@' . $timestamp, 0, '/');
					$response->headers->setCookie($cookie);

					return $response;
				}

				$debug = false;
				if($request->cookies->has($app['timeline.debug.cookie'])) {
					$debug = true;
				}

				$dtFormat = 'Y-m-d H:i:s O';
				$diFormat = '%a days, %h hours, %i minutes';
				//$jsFormat = 'dddd, MMMM Do YYYY, h:mm:ss a ZZ';
				$jsFormat = 'YYYY-MM-DD HH:mm:ss ZZ';

				$app['timeline']->asort();

				$epochs = [];
				foreach($app['timeline'] as $name => $epoch) {
					$epochs[$name] = $epoch;
				}


				$output = [];

				$output[] = '<!DOCTYPE html>';
				$output[] = '<html>';
				$output[] = '<head>';

				$output[] = '	<title>Timeline Debug Console</title>';

				// Latest compiled and minified CSS
				$output[] = '	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">';

				// Optional theme
				$output[] = '	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">';

				// jQuery (necessary for Bootstrap's JavaScript plugins)
				$output[] = '	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>';

				// Latest compiled and minified JavaScript
				$output[] = '	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>';

				// Moment.js
				$output[] = '	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.3/moment.min.js"></script>';

				// Bootstrap DateTimePicker
				$output[] = '	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.14.30/css/bootstrap-datetimepicker.min.css">';
				$output[] = '	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.14.30/js/bootstrap-datetimepicker.min.js"></script>';

				$output[] = '	<script type="text/javascript">';
				$output[] = '		$(document).ready(function() {';
				$output[] = '			var options = {';
				$output[] = '				format: "' . $jsFormat . '",';
				$output[] = '				sideBySide: true,';
				$output[] = '				showTodayButton: true,';
				$output[] = '				showClose: true';
				$output[] = '			};';
				$output[] = '';
				$output[] = '			$(\'#datetimepicker\').datetimepicker(options);';
				$output[] = '';
				$output[] = '			$(\'#debug-epoch\').change(function() {';
				$output[] = '				var value = $(this).val();';
				$output[] = '				$("#datetimepicker").data("DateTimePicker").date(new Date(value));';
				$output[] = '				$(this).val("");';
				$output[] = '			});';
				$output[] = '		});';
				$output[] = '	</script>';

				$output[] = '</head>';
				$output[] = '<body>';


				$output[] = '	<div class="container-fluid" style="padding-top: 20px;">';

				if($debug) {
					$output[] = '		<div class="row">';
					$output[] = '			<div class="col-md-12">';
					$output[] = '				<div class="alert alert-danger" role="alert">';
					$output[] = '					<strong>Debug Mode Enabled</strong>';
					$output[] = '					-';
					$output[] = '					Debug mode is currently enabled for your session, click <a href="?reset=1" class="alert-link">disable</a> to deactivate or <a href="#" class="alert-link" data-toggle="modal" data-target="#debug-modal">edit</a> to change current date/time.';
				//	$output[] = '					<div class="btn-toolbar pull-right">';
				//	$output[] = '						<button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#debug-modal">Edit</button>';
				//	$output[] = '						<a href="?reset=1" class="btn btn-xs btn-danger pull-right">Disable</a>';
				//	$output[] = '					</div>';
					$output[] = '				</div>';
					$output[] = '			</div>';
					$output[] = '		</div>';
				} else {
					$output[] = '		<div class="row">';
					$output[] = '			<div class="col-md-12">';
					$output[] = '				<div class="alert alert-info" role="alert">';
					$output[] = '					<strong>Debug Mode Disabled</strong>';
					$output[] = '					-';
					$output[] = '					Debug mode is not currently enabled for your session, click <a href="#" class="alert-link" data-toggle="modal" data-target="#debug-modal">enable</a> to select a new date/time and activate debugging.';
				//	$output[] = '					<div class="btn-toolbar pull-right">';
				//	$output[] = '						<button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#debug-modal">Enable</button>';
				//	$output[] = '					</div>';
					$output[] = '				</div>';
					$output[] = '			</div>';
					$output[] = '		</div>';
				}

				$output[] = '		<div class="row">';
				$output[] = '			<div class="col-md-12">';
				$output[] = '				<div class="panel panel-default">';
				$output[] = '					<div class="panel-heading">';
				$output[] = '						<h4>Timeline</h4>';
				$output[] = '					</div>';
				$output[] = '					<table class="table table-striped">';
				$output[] = '						<tr>';
				$output[] = '							<th>Name</th>';
				$output[] = '							<th>Date / Time</th>';
				$output[] = '							<th>Status</th>';
				$output[] = '						</tr>';

				foreach($app['timeline'] as $name => $epoch) {
					if($name == 'now') {
						if($debug) {
							$output[] = '						<tr class="danger">';
						} else {
							$output[] = '						<tr class="info">';
						}

						$output[] = '							<th>' . $name . '</th>';
						$output[] = '							<td>' . $epoch->format($dtFormat) . '</td>';

						if($debug) {
							$output[] = '							<td>';
							$output[] = '								<span class="glyphicon glyphicon-warning-sign"></span> Debug Mode';
							$output[] = '								<div class="btn-toolbar pull-right">';
							$output[] = '									<button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#debug-modal">Edit</button>';
							$output[] = '									<a href="?reset=1" class="btn btn-xs btn-danger pull-right">Disable</a>';
							$output[] = '								</div>';
							$output[] = '							</td>';
						} else {
							$output[] = '							<td>';
							$output[] = '								<span class="glyphicon glyphicon-star"></span> Current';
							$output[] = '								<div class="btn-toolbar pull-right">';
							$output[] = '									<button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#debug-modal">Enable</button>';
							$output[] = '								</div>';
							$output[] = '							</td>';
						}
					} else {
						$link = '?ts=' . $epoch->getTimestamp();

						$output[] = '						<tr>';
						$output[] = '							<th><a href="' . $link . '">' . $name . '</a></th>';
						$output[] = '							<td><a href="' . $link . '">' . $epoch->format($dtFormat) . '</a></td>';

						if($app['timeline']['now'] < $epoch) {
							$remaining = $epoch->diff($app['timeline']['now']);

							$output[] = '							<td><span class="glyphicon glyphicon-time"></span> Upcoming (' . $remaining->format($diFormat) . ')</td>';
						} else {
							$output[] = '							<td><span class="glyphicon glyphicon-ok"></span> Complete</td>';
						}
					}
					$output[] = '						</tr>';
				}

				$output[] = '					</table>';
				$output[] = '				</div>';
				$output[] = '			</div>';
				$output[] = '		</div>';
				$output[] = '	</div>';

				$output[] = '	<div class="modal fade" id="debug-modal" tabindex="-1" role="dialog" aria-labelledby="debug-modal-label">';
				$output[] = '		<div class="modal-dialog" role="document">';
				$output[] = '			<div class="modal-content">';
				$output[] = '				<form>';
				$output[] = '					<div class="modal-header">';
				$output[] = '						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
				$output[] = '						<h4 class="modal-title" id="debug-modal-label">Debug Mode</h4>';
				$output[] = '					</div>';
				$output[] = '					<div class="modal-body">';
				$output[] = '						<div class="form-group">';
				$output[] = '							<label for="recipient-name" class="control-label">Datetime:</label>';
				$output[] = '							<div class="panel-title input-group date" id="datetimepicker">';
				$output[] = '								<input class="form-control" name="date" id="debug-datetime" value="' . $app['timeline']['now']->format($dtFormat) . '" />';
				$output[] = '								<span class="input-group-addon">';
				$output[] = '									<span class="glyphicon glyphicon-calendar"></span>';
				$output[] = '								</span>';
				$output[] = '							</div>';
				$output[] = '						</div>';
				$output[] = '						<div class="form-group">';
				$output[] = '							<label for="message-text" class="control-label">Epoch:</label>';
				$output[] = '							<select class="form-control" name="epoch" id="debug-epoch">';
				$output[] = '								<option value="" disabled selected>-- Select Epoch --</option>';

				foreach($app['timeline'] as $name => $epoch) {
					if($name == 'now') {
						continue;
					}

					$output[] = '								<option value="' . $epoch->format($dtFormat) . '">' . $name . ' (' . $epoch->format($dtFormat) . ')</option>';
				}

				$output[] = '							</select>';
				$output[] = '						</div>';
				$output[] = '					</div>';
				$output[] = '					<div class="modal-footer">';
				$output[] = '						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';
				$output[] = '						<input type="submit" class="btn btn-primary" value="Save" />';
				$output[] = '					</div>';
				$output[] = '				</form>';
				$output[] = '			</div>';
				$output[] = '		</div>';
				$output[] = '	</div>';

				$output[] = '</body>';
				$output[] = '</html>';

				return new Response(implode("\n", $output), 200);
			});
		}
	}
}
