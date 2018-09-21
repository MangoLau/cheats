<?php

return [
	'log' => [
		// 'level' => 'DEBUG',
		'level' => 'WARNING',
		'file' => 'all.log',
	],

	// 'domain' => 'http://www.dianzanyun.com',
	'domain' => 'http://106.75.77.8',

	'token' => [
		'expire' => 604800,				// token过期时间：一周
	],

	// 微信登录
	'wechat' => [
		'appid' => 'wxf315ae280d691cb2',
		'appsecret' => '6762cd4534eed56e0835c41b3334d923',
	],

	// 微博登录
	'weibo' => [
		'appid' => '2808651924',
		'appsecret' => 'd4490bb0f4147de4a06cd9d2f76739e9',
	],

	// qq登录
	'qq' => [
		'appid' => '101372895',
		'appsecret' => '30f2a7219c164468c49c2dec63cc0440',
	],

	'site' => [
		'domain' => 'http://lover.com',				// 域名
		'default_avatar' => '/uploads/avatars/default.png',		// 默认头像
	],

	'auth_key' => 'sessionKey',						// header中权限校验的key

	'upload' => [
		'upload_file_name' => 'xinyou_file',			// post file name
	],

	'default_avatar' => 'http://love.local/uploads/avatars/default.png',			// 默认头像
];
