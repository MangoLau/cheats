<?php
/**
 * redis单例
 */

namespace Admin\Middleware;

class Credis
{
	private static $_conn;

	private function __construct() {}
	private function __clone() {}

	public static function getInstance()
	{
		if (!self::$_conn instanceof \Redis) {
			$redis = new \Redis;
			// 暂时写死配置
			$redis->connect('127.0.0.1', 6379);
			self::$_conn = $redis;
		}

		return self::$_conn;
	}
}