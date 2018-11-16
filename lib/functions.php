<?php

use Curl\Curl;

/**
 * 公用函数
 */

if (!function_exists('getPublicDir')) {
	function getPublicDir() {
		return realpath('.');
	}
}

if (!function_exists('getConfigDir')) {
	// config 目录
	function getConfigDir()
	{
		return dirname(realpath('.')) . '/config/';
	}
}

if (!function_exists('getAdminConfigDir')) {
	// admin config 目录
	function getAdminConfigDir()
	{
		return dirname(realpath('.')) . '/config/';
	}
}

if (!function_exists('getLogDir')) {
	// log 目录
	function getLogDir()
	{
		return dirname(realpath('.')) . '/log/';
	}
}

if (!function_exists('getResourceDir')) {
	// resource 目录
	function getResourceDir()
	{
		return dirname(realpath('.')) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'app';
	}
}

if (!function_exists('getAvatarUrl')) {
	// 获取头像url地址
	function getAvatarUrl($avatar)
	{
		return empty($avatar) ? getConfig('app.default_avatar') : ( strpos($avatar, 'http') === 0 ? $avatar : '/uploads/avatars/' . $avatar );
	}
}

if (!function_exists('getHouseUrl')) {
	// 获取爱巢合照url地址
	function getHouseUrl($pic)
	{
		return empty($pic) ? getConfig('app.default_avatar') : ( strpos($pic, 'http') === 0 ? $pic : '/uploads/houses/' . $pic );
	}
}

if (!function_exists('getPicUrl')) {
	// 获取图片url地址
	function getPicUrl($pic)
	{
		return getConfig('app.domain') . '/uploads/images/' . $pic;
	}
}

if (!function_exists('getIconUrl')) {
	// 获取图片url地址
	function getIconUrl($icon)
	{
		return strpos($icon, 'http') === 0 ? $icon : getConfig('app.domain') . '/uploads/icons/' . $icon;
	}
}

if (!function_exists('getQrcodeUrl')) {
	// 获取图片url地址
	function getQrcodeUrl($qrcode)
	{
		return '/uploads/qrcodes/' . $qrcode;
	}
}

if (!function_exists('generateRandomString')) {
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
}

if (!function_exists('getRealRandomStr')) {
	// 系统随机数
	function getRealRandomStr($length = 10)
	{
		return exec('head /dev/urandom | tr -dc _A-Za-z0-9 | head -c ' . intval($length) . ' ; echo ;');
	}
}

if (!function_exists('getCurrentTime')) {
	// 当前时间
	function getCurrentTime()
	{
		return $_SERVER['REQUEST_TIME'] ?: time();
	}
}

if (!function_exists('getConfig')) {
	// 获取配置, 如：getConfig('app.wechat.appid')
	function getConfig($path = '')
	{
		$arr_config = explode('.', $path);
		$config = include(getConfigDir() . array_shift($arr_config) . '.php');
		if (!empty($config)) {
			if (is_array($arr_config) && !empty($arr_config)) {
				foreach ($arr_config as $c) {
					$config = $config[$c];
				}
			}
		}

		return $config;
	}
}

// 控制台获取数据库配置文件
if (!function_exists('getConsoleDatabaseConfig')) {
	function getConsoleDatabaseConfig()
	{
		$config = include(dirname(dirname(__FILE__)) . '/config/database.php');

		return $config;
	}
}

if (!function_exists('getViewDir')) {
	//
	function getViewDir()
	{
		return dirname(realpath('.')) . '/admin/View';
	}
}

if (!function_exists('isAjax')) {
	//
	function isAjax()
	{
		return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}
}

if (!function_exists('dd')) {
	//
	function dd($var) {
		echo '<pre>';
		var_dump($var);
		exit;
	}
}

if (!function_exists('isSessionStart')) {
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
}

if (!function_exists('removeEmoji')) {
	//
	function removeEmoji($text) {
	    $clean_text = "";
	
	    // Match Emoticons
	    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
	    $clean_text = preg_replace($regexEmoticons, '', $text);
	
	    // Match Miscellaneous Symbols and Pictographs
	    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
	    $clean_text = preg_replace($regexSymbols, '', $clean_text);
	
	    // Match Transport And Map Symbols
	    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
	    $clean_text = preg_replace($regexTransport, '', $clean_text);
	
	    // Match Miscellaneous Symbols
	    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
	    $clean_text = preg_replace($regexMisc, '', $clean_text);
	
	    // Match Dingbats
	    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
	    $clean_text = preg_replace($regexDingbats, '', $clean_text);
	
	    return $clean_text;
	}
}

if (!function_exists('encryptString')) {
	function encryptString($text) {
	     $hash = md5('iloveu');
	     $method = "AES-256-CBC";
	     $iv_size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
	     $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	
	     $encrypted = openssl_encrypt($text, $method, $hash, 0, $iv);
	
	     return base64_encode($iv . $encrypted);
	}
}

if (!function_exists('decryptString')) {
	function decryptString($text) {
	     $text = base64_decode($text);
	
	     $hash = md5('iloveu');
	     $method = "AES-256-CBC";
	     $iv_size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
	     $iv = substr($text, 0, $iv_size);
	
	     $decrypted = openssl_decrypt(substr($text, $iv_size), $method, $hash, 0, $iv);
	
	     return $decrypted;
	}
}

// return /login, /, /qrcode
if (!function_exists('getCurrentRequestPath')) {
	function getCurrentRequestPath() {
		$uri_info = parse_url($_SERVER['REQUEST_URI']);

		return $uri_info['path'];
	}
}

if (!function_exists('is_array_or_alike')) {
	function is_array_or_alike($var) {
	  	return is_array($var) ||
	        ($var instanceof ArrayAccess  &&
	         $var instanceof Traversable  &&
	         $var instanceof Serializable &&
	         $var instanceof Countable);
	}
}

if (!function_exists('foreachAble')) {
	function foreachAble($var) {
		return is_array($var) || $var instanceof Traversable;
	}
}

if (!function_exists('isQQ')) {
	function isQQ($qq = '') {
		return preg_match("/^[1-9][0-9]{4,12}$/", $qq);
	}
}

if (!function_exists('isAndroid')) {
	function isAndroid()
	{
		return stripos($_SERVER['HTTP_USER_AGENT'],"Android") !== false;
	}
}

if (!function_exists('isIOS')) {
	function isiOS()
	{
		return stripos($_SERVER['HTTP_USER_AGENT'], "iPhone") !== false || stripos($_SERVER['HTTP_USER_AGENT'], "iPad") !== false;
	}
}

// 获取bmob订单信息
if (!function_exists('getBmobOrderInfo')) {
	function getBmobOrderInfo($order_id)
	{
		if (empty($order_id)) {
			return '';
		}

		$url = 'https://api.bmob.cn/1/pay/' . $order_id;
		$curl = new Curl;
		$curl->setOpt(CURLOPT_RETURNTRANSFER, true);
		$curl->setHeader('X-Bmob-Application-Id', 'ce23513c774236e1f7815c8be8a6547a');
		$curl->setHeader('X-Bmob-REST-API-Key', '6b14c47361695b96bec148700e303dc0');
		$curl->get($url);

		if ($curl->error) {
			return '';
		}

		return $curl->response;
	}
}

// 获取话付通(71pay)订单信息
if (!function_exists('get71PayOrderInfo')) {
	function get71PayOrderInfo($order_id, $app_id = 2821, $md5_key = '896785df40334ac4a514812e9b0df5bb') {
		if (empty($order_id)) {
			return '';
		}

		$url = 'http://gateway.71pay.cn/Pay/ThridPayQuery.shtml';
		$md5_key = $md5_key;
		$params = [
			'app_id' => $app_id,
			'order_id' => $order_id,
			'time_stamp' => date('YmdHis', time()),
		];

		$sign = md5(http_build_query($params) . '&key=' . md5($md5_key));
		
		$params['sign'] = $sign;

		$curl = new Curl;
		$curl->post($url, $params);

		if ($curl->error) {
			return '';
		}

		return $curl->response;
	}
}

if (!function_exists('getYouPayOrderInfo')) {
	function getYouPayOrderInfo($app_id, $order_id, $sign) {
		if (empty($app_id) || empty($order_id) || empty($sign)) {
			return '';
		}

		$url = 'http://sanfang.youpay.cc/dealpay_queryorder.php';
		$params = [
			'orderid' => $order_id,
			'appid' => $app_id,
			'sign' => $sign,
		];

		$curl = new Curl;
		$curl->get($url . '?' . http_build_query($params));

		if ($curl->error) {
			// error_log();
			return '';
		}

		return $curl->response;
	}
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

// 通过链接获取快手用户ID和作品ID
if (!function_exists('getKuaishouZpidAndUid')) {
	function getKuaishouZpidAndUid($url) {
		if (strpos($url, 'www.gifshow.com/s/') !== false) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$headers = curl_exec($ch); // $a will contain all headers
			$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		}
		
		parse_str(parse_url($url, PHP_URL_QUERY), $query);

		return [
			'userId' => empty($query['userId']) ? '' : $query['userId'],
			'photoId' => empty($query['photoId']) ? '' : $query['photoId'],
		];
	}
}

// 通过链接获取全民K歌ID
if (!function_exists('getQmkgZpid')) {
	function getQmkgId($url) {
		parse_str(parse_url($url, PHP_URL_QUERY), $query);
		return $query['s'];
	}
}

// 通过抖音复制的链接获取url
if (!function_exists('getDouyinUrl')) {
	function getDouyinUrl($str)
    {
        $r = preg_match_all("#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#", $str, $matchs);
        return $r ? $matchs[0][0] : false;
	}
}
