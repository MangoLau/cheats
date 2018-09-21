<?php 
/**
 * 下单进程守护进程，每5分钟检测一次如果脚本没有在运行就重启
 */
	// 获取相关进程
	$handle = popen('ps -ef|grep app:order-create', 'r');
	$content = '';
	while($line = fgets($handle, 4096)) {
		$content = $content . $line;
	}
	pclose($handle);

	$ret = preg_match('#/www/cheats/bin/console#', $content);
	if (empty($ret)) {
		$handle = popen('nohup php /www/cheats/bin/console app:order-create &', 'w');
		pclose($handle);
	}

	$ret = preg_match('#/www/beautify/bin/console#', $content);
	if (empty($ret)) {
		$handle = popen('nohup php /www/beautify/bin/console app:order-create &', 'w');
		pclose($handle);
	}
