<?php
error_reporting(E_ALL^E_NOTICE);

// xhprof
// xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY); // start

// Autoload
require '../vendor/autoload.php';

// 数据库连接
$db = getConfig('database');
\RedBeanPHP\R::setup($db['driver'] . ':host=' . $db['host'] . ';dbname=' . $db['database'], $db['username'], $db['password']);
// \RedBeanPHP\R::debug( TRUE );	// debug

// 路由配置
require '../config/routes.php';

// $xhprof_data = xhprof_disable(); // end
// // $XHPROF_ROOT = "/usr/local/Cellar/php56-xhprof/254eb24";		// 本地
// $XHPROF_ROOT = "/usr/share/xhprof";
// include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
// include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
// $xhprof_runs = new XHProfRuns_Default('/tmp');
// $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_wechat_app_store"); // 获取run_id

// error_log("http://xhprof-secret.bmob.cn/index.php?run={$run_id}&source=xhprof_wechat_app_store");