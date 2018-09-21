<?php 	# 路由
use NoahBuscher\Macaw\Macaw;

Macaw::get('login', 'Admin\Controller\AdministorController@login');									// 登录
Macaw::post('login', 'Admin\Controller\AdministorController@login');								// 登录
Macaw::get('logout', 'Admin\Controller\AdministorController@logout');								// 登出
			
Macaw::get('/', 'Admin\Controller\HomeController@index');											// 首页概览
			
Macaw::get('administors', 'Admin\Controller\AdministorController@index');							// 管理员列表
Macaw::get('profile', 'Admin\Controller\AdministorController@profile');								// 管理员信息
Macaw::post('profile', 'Admin\Controller\AdministorController@profile');							// 管理员信息
Macaw::get('add', 'Admin\Controller\AdministorController@add');										// 新增管理员
Macaw::post('add', 'Admin\Controller\AdministorController@add');									// 新增管理员
		
Macaw::get('users', 'Admin\Controller\UserController@index');										// 用户列表
Macaw::get('user/qrcodes', 'Admin\Controller\UserController@qrcodes');								// 用户发布的小程序列表
			
Macaw::get('attendances', 'Admin\Controller\AttendanceController@index');							// 签到列表
			
Macaw::get('banners', 'Admin\Controller\BannerController@index');									// banner列表
Macaw::post('banner', 'Admin\Controller\BannerController@add');										// 增加banner
Macaw::put('banner/(:num)', 'Admin\Controller\BannerController@pushOnline');						// 上线
Macaw::delete('banner/(:num)', 'Admin\Controller\BannerController@pushOffline');					// 下线
			
Macaw::get('cards', 'Admin\Controller\CardController@index');										// 卡密列表
Macaw::post('card', 'Admin\Controller\CardController@add');											// 增加卡密
Macaw::post('card/update', 'Admin\Controller\CardController@update');								// 更新卡密
Macaw::put('card/(:num)', 'Admin\Controller\CardController@pushOnline');							// 上线
Macaw::delete('card/(:num)', 'Admin\Controller\CardController@pushOffline');						// 下线

Macaw::get('cheatproducts', 'Admin\Controller\CheatProductController@index');						// 刷赞刷赞商品
Macaw::post('cheatproduct', 'Admin\Controller\CheatProductController@add');							// 增加刷赞商品
Macaw::post('cheatproduct/update', 'Admin\Controller\CheatProductController@update');				// 更新产品
Macaw::put('cheatproduct/(:num)', 'Admin\Controller\CheatProductController@pushOnline');			// 上线
Macaw::delete('cheatproduct/(:num)', 'Admin\Controller\CheatProductController@pushOffline');		// 下线

Macaw::get('cheats', 'Admin\Controller\CheatController@index');										// 刷赞类型
Macaw::post('cheat/url', 'Admin\Controller\CheatController@updateUrl');								// 更新业务url
Macaw::put('cheat/(:num)', 'Admin\Controller\CheatController@pushOnline');							// 上线
Macaw::delete('cheat/(:num)', 'Admin\Controller\CheatController@pushOffline');						// 下线

Macaw::get('channels', 'Admin\Controller\ChannelController@index');									// 刷赞类型
Macaw::post('channel', 'Admin\Controller\ChannelController@add');									// 新增
Macaw::post('channel/update', 'Admin\Controller\ChannelController@update');							// 更新
Macaw::put('channel/(:num)', 'Admin\Controller\ChannelController@pushOnline');						// 上线
Macaw::delete('channel/(:num)', 'Admin\Controller\ChannelController@pushOffline');					// 下线

Macaw::get('configs', 'Admin\Controller\ConfigController@index');									// 配置列表
Macaw::post('config', 'Admin\Controller\ConfigController@add');										// 增加配置
Macaw::post('config/update', 'Admin\Controller\ConfigController@update');							// 更新配置
Macaw::put('config/(:num)', 'Admin\Controller\ConfigController@pushOnline');						// 上线
Macaw::delete('config/(:num)', 'Admin\Controller\ConfigController@pushOffline');					// 下线

Macaw::get('hotpeoples', 'Admin\Controller\HotPeopleController@index');								// 已启用的空间红人
Macaw::post('hotpeople', 'Admin\Controller\HotPeopleController@add');								// 增加红人
Macaw::put('hotpeople/(:num)', 'Admin\Controller\HotPeopleController@pushOnline');					// 上线
Macaw::delete('hotpeople/(:num)', 'Admin\Controller\HotPeopleController@pushOffline');				// 下线

Macaw::get('orders', 'Admin\Controller\OrderController@index');										// 订单列表
Macaw::get('orders/laquanquan', 'Admin\Controller\OrderController@laquanquan');						// 拉圈圈订单
Macaw::get('orders/dealing', 'Admin\Controller\OrderController@dealing');							// 正在队列中处理的订单
Macaw::post('order/createByHand', 'Admin\Controller\OrderController@orderByHand');					// 队列中的任务转为手动
Macaw::delete('order/(:num)', 'Admin\Controller\OrderController@returnScores');						// 删除订单退回积分

Macaw::get('products', 'Admin\Controller\ProductController@index');									// 已启用的充值产品
Macaw::post('product', 'Admin\Controller\ProductController@add');									// 增加充值产品
Macaw::post('product/update', 'Admin\Controller\ProductController@update');							// 更新产品
Macaw::put('product/(:num)', 'Admin\Controller\ProductController@pushOnline');						// 上线
Macaw::delete('product/(:num)', 'Admin\Controller\ProductController@pushOffline');					// 下线

Macaw::get('recharges', 'Admin\Controller\RechargeController@index');								// 充值列表
Macaw::post('recharge/updateBmobOrderID', 'Admin\Controller\RechargeController@updateBmobOrderID');	// 更新bmob订单号
Macaw::post('recharge/rsyncStatus', 'Admin\Controller\RechargeController@updateOrderStatus');		// 手动同步订单状态
Macaw::get('recharges/export', 'Admin\Controller\RechargeController@export');						// 导出

Macaw::get('feedbacks', 'Admin\Controller\FeedbackController@index');								// 用户反馈
Macaw::put('feedback/(:num)', 'Admin\Controller\FeedbackController@pushOnline');
Macaw::delete('feedback/(:num)', 'Admin\Controller\FeedbackController@pushOffline');
Macaw::post('feedback/reply', 'Admin\Controller\FeedbackController@reply');

Macaw::get('complaints', 'Admin\Controller\ComplaintController@index');								// 投诉

Macaw::dispatch();
