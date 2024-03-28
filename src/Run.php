<?php
use Src\Error;
use Validate\Enums\Boolean;
use Validate\Enums\Error_Reports;

if ( strtoupper($app[ 'config' ]->setting('debug_mode') ) === Boolean::ON ) {
	// Start the timer for script exec time profiler
	$profiler = new Src\Profiler($app, []);
	$profiler->start_timer();
}

$maintenance_mode = Boolean::tryFrom(strtoupper($app[ 'config' ]->setting( 'maintenance_mode' )));
if( is_null( $maintenance_mode ) )
{
	exit("Invalid value for maintenance mode. Valid settings are: 'ON' or 'OFF'");
}

if ( $maintenance_mode->value === Boolean::ON ) {
	if ($app['router']->controller_class !== 'Maintenance_Controller' &&
		$app['router']->controller_class !== 'Contact_Controller') {
		header('Location: ' . $app['config']->setting('site_url') . 'maintenance');
	}
}

$system_startup_check = Boolean::tryFrom(strtoupper($app[ 'config' ]->setting( 'system_startup_check' )));
if( is_null( $system_startup_check ) )
{
	exit("Invalid value for system startup check. Valid settings are: 'ON' or 'OFF'");
}

if ( $system_startup_check->value === Boolean::ON) {
	require_once 'system_startup_check.php';
	exit;
}

if ( strtoupper($app[ 'config' ]->setting('debug_mode') ) === Boolean::ON ) {
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
