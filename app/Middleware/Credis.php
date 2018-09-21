<?php
/**
 * redis单例
 */

namespace App\Middleware;

class Credis
{
	private static $_conn;

	private function __construct() {}
	private function __clone() {}

	public static function getInstance()
	{
		if (!isset(self::$_conn) || !self::$_conn instanceof \Redis) {
			try {
				$redis = new \Redis;
				// 暂时写死配置
				$redis->connect('127.0.0.1', 6379);
				self::$_conn = $redis;
			} catch (Exception $e) {
				error_log('Redis init failed: ' . $e->getMessage());
				throw new Exception('redis connect failed', 1);
				
			}
		}

		return self::$_conn;
	}
}