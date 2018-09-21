<?php
error_reporting(E_ALL^E_NOTICE);

// xhprof
// xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY); // start

// 公共函数
include '../admin/Middleware/functions.php';

// session_start
if (!isSessionStart()) {
	session_start();
}

// Autoload 自动载入
require '../vendor/autoload.php';

// 数据库连接
$db = getAdminConfig('database');
\RedBeanPHP\R::setup($db['driver'] . ':host=' . $db['host'] . ';dbname=' . $db['database'], $db['username'], $db['password']);
// \RedBeanPHP\R::debug( TRUE );

// 路由配置
require '../config/admin/routes.php';