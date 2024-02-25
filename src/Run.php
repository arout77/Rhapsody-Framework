<?php

if ($app['config']->setting('debug_mode') == 'ON' || $app['config']->setting('debug_mode') == 'on') {
	// Start the timer for script exec time profiler
	$profiler = new Src\Profiler($app);
	$profiler->start_timer();
}

# Core Smarty settings
#$app['template']->setTemplateDir(VIEWS_PATH);
#$app['template']->setCompileDir($app['config']->setting('var_path') . 'templates_c');
#$app['template']->setCacheDir($app['config']->setting('var_path') . 'cache' . DS . $app['router']->controller_class . DS . $app['router']->action . DS . $app['router']->parameter[1]);
// Uncomment to enable caching
// $app['template']->setCaching(Smarty::CACHING_LIFETIME_CURRENT);
// $app['template']->setCompileCheck(false);
#$app['template']->setConfigDir(SMARTY_PATH . 'configs');

# Assign some site settings used globally
#$app['template']->assign('site_url', $app['config']->setting('site_url'));
#$app['template']->assign('site_name', $app['config']->setting('site_name'));
#$app['template']->assign('slogan', $app['config']->setting('site_slogan'));
#$app['template']->assign('param1', $app['router']->parameter[1]);
#$app['template']->assign('param2', $app['router']->parameter[2]);
// if(!empty($app['session']->get('name')))
// {
// 	$app['template']->assign('name', $app['session']->get('name'));
// 	$app['template']->assign('email', $app['session']->get('email'));

// 	if($app['session']->get('permissions') == 'admin' || $app['session']->get('permissions') == 'employee')
// 	{
// 		$_name = $app['session']->get('name');
// 		$db = $app['database']->prepare("SELECT avatar FROM employees WHERE `name` = ?");
// 		$db->execute(["$_name"]);
// 		foreach($db as $row) {
// 			$app['template']->assign('avatar', $row['avatar']);
// 		}
// 		unset($_name);
// 		unset($db);
// 		unset($row);
// 	} else {
// 		$_name = $app['session']->get('name');
// 		$db = $app['database']->prepare("SELECT avatar FROM users WHERE `name` = ?");
// 		$db->execute(["$_name"]);
// 		foreach($db as $row) {
// 			$app['template']->assign('avatar', $row['avatar']);
// 		}
// 		unset($_name);
// 		unset($db);
// 		unset($row);
// 	}
// }

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
