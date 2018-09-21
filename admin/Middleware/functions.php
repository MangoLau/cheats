<?php
	// 
	function getPublicDir()
	{
		return realpath('.');
	}

	// config 目录
	function getAdminConfigDir()
	{
		return dirname(realpath('.')) . '/config/admin/';
	}

	// log 目录
	function getLogDir()
	{
		return dirname(realpath('.')) . '/log/';
	}

	// 获取头像url地址
	function getAvatarUrl($avatar)
	{
		return empty($avatar) ? getAdminConfig('common.default_avatar') : ( strpos($avatar, 'http:') === 0 ? $avatar : getAdminConfig('common.domain') . '/uploads/avatars/' . $avatar );
	}

	// 获取图片url地址
	function getPicUrl($pic)
	{
		return getAdminConfig('common.domain') . '/uploads/qrcodes/' . $pic;
	}

	// 获取图片url地址
	function getIconUrl($icon)
	{
		return getAdminConfig('common.domain') . '/uploads/icons/' . $icon;
	}

	// 随机数
	function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	// 系统随机数
	function getRealRandomStr($length = 10)
	{
		return exec('head /dev/urandom | tr -dc _A-Za-z0-9 | head -c ' . intval($length) . ' ; echo ;');
	}

	// 当前时间
	function getCurrentTime()
	{
		return $_SERVER['REQUEST_TIME'] ?: time();
	}

	// 获取配置, 如：getAdminConfig('app.wechat.appid')
	function getAdminConfig($path = '')
	{
		$arr_config = explode('.', $path);
		$config = include(getAdminConfigDir() . array_shift($arr_config) . '.php');
		if (!empty($config)) {
			if (is_array($arr_config) && !empty($arr_config)) {
				foreach ($arr_config as $c) {
					$config = $config[$c];
				}
			}
		}

		return $config;
	}

	// resource 目录
	function getResourceDir()
	{
		return dirname(realpath('.')) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'admin';
	}

	function dd($var) {
		echo '<pre>';
		var_dump($var);
		exit;
	}

	// 检测session是否开启
	function isSessionStart()
	{
		if (version_compare(phpversion(), '5.4.0', '<')) {
			$session_id = session_id();
		    return !empty($session_id);
		} else {
			return session_status() != PHP_SESSION_NONE;
		}
	}

	// path
	function getCurrentRequestPath() {
		$uri_info = parse_url($_SERVER['REQUEST_URI']);

		return $uri_info['path'];
	}

	if (!function_exists('array2CSV')) {
		function array2CSV(array &$array) {
		   if (count($array) == 0) {
		     return null;
		   }

	   	ob_start();
	   	$df = fopen("php://output", 'w');
	   	fputcsv($df, array_keys(reset($array)));
	   	foreach ($array as $row) {
	   	   fputcsv($df, $row);
	   	}
	   	fclose($df);
	   	return ob_get_clean();
		}
	}

	if (!function_exists('setCsvDownloadHeader')) {
		function setCsvDownloadHeader($filename = '') {
		    // disable caching
		    $now = gmdate("D, d M Y H:i:s");
		    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		    header("Last-Modified: {$now} GMT");
	
	    	// force download  
	    	header("Content-Type: application/force-download");
	    	header("Content-Type: application/octet-stream");
	    	header("Content-Type: application/download");
	
			if (empty($filename)) {
				$filename = date('Y-m-d His', time()) . '.csv';
			}

	    	// disposition / encoding on response body
	    	header("Content-Disposition: attachment;filename={$filename}");
	    	header("Content-Transfer-Encoding: binary");
		}
	}


