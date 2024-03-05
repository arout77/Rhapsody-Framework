<?php

if ($app['config']->setting('debug_mode') == 'ON' || $app['config']->setting('debug_mode') == 'on') {
	// Start the timer for script exec time profiler
	$profiler = new Src\Profiler($app, []);
	$profiler->start_timer();
}

if ($app['config']->setting('maintenance_mode') === "TRUE") {
	if ($app['router']->controller_class !== 'Maintenance_Controller' &&
		$app['router']->controller_class !== 'Contact_Controller') {
		header('Location: ' . $app['config']->setting('site_url') . 'maintenance');
	}
}

if ($app['config']->setting('system_startup_check') === "TRUE") {
	require_once 'system_startup_check.php';
	exit;
}

if ($app['config']->setting('debug_mode') == 'ON' || $app['config']->setting('debug_mode') == 'on') {
	// Stop the timer for script exec time profiler
	$profiler->stop_timer();

	if ((round($profiler->ram_usage() / 1024)) <= 1023) {
		$ram_usage = round($profiler->ram_usage() / 1024) . ' kb';
	} else {
		$ram_usage = round($profiler->ram_usage() / 1024 / 1024, 2) . ' MB';
	}

	if ((round($profiler->ram_peak_usage() / 1024)) <= 1023) {
		$ram_peak_usage = round($profiler->ram_peak_usage() / 1024) . ' kb';
	} else {
		$ram_peak_usage = round($profiler->ram_peak_usage() / 1024 / 1024, 2) . ' MB';
	}

	// $sql = $profiler->get_sql();
	// var_dump($sql);exit;

	// $app['template']->assign('exec_time', $profiler->timer());
	// $app['template']->assign('ram_usage', $ram_usage);
	// $app['template']->assign('ram_peak_usage', $ram_peak_usage);
	// if( $app['config']->setting('debug_toolbar') == 'ON' ) 
	// {
	// 	$app['template']->display('template/debug_toolbar.tpl');
	// }
}
