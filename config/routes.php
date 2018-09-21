<?php 	# 路由
use NoahBuscher\Macaw\Macaw;

Macaw::get('/', 'App\Controller\HomeController@index');									// web首页

Macaw::post('/login', 'App\Controller\UserController@login');							// 登录获取token
Macaw::post('/refreshToken', 'App\Controller\UserController@refreshToken');				// 刷新token
Macaw::get('/userinfo', 'App\Controller\UserController@detail');						// 用户信息
Macaw::get('/user_exists', 'App\Controller\UserController@exists');						// uid是否存在

Macaw::post('/bindInviter', 'App\Controller\UserController@bindInviter');				// 绑定邀请者
Macaw::post('/bindQQ', 'App\Controller\UserController@bindQQ');							// 绑定QQ
Macaw::post('/unbindQQ', 'App\Controller\UserController@unbindQQ');						// 解绑QQ

Macaw::post('/attendance', 'App\Controller\AttendanceController@create');				// 签到
Macaw::get('/attendances', 'App\Controller\AttendanceController@index');				// 签到列表

Macaw::get('/products', 'App\Controller\ProductController@index');						// 产品列表
Macaw::get('/qqProducts', 'App\Controller\ProductController@qq');						// QQ刷赞转发等类型
Macaw::get('/v2/qqProducts', 'App\Controller\ProductController@qqv2');					// QQ刷赞转发等类型,v2
Macaw::get('/qqProductList', 'App\Controller\ProductController@qqList');				// QQ某个刷赞类型的产品列表

Macaw::post('/recharge', 'App\Controller\RechargeController@create');					// 充值
Macaw::post('/recharge/direct', 'App\Controller\RechargeController@direct');			// 微信充值
Macaw::post('/recharge/wechat', 'App\Controller\RechargeController@wechat');			// 微信支付
Macaw::post('/recharge/update', 'App\Controller\RechargeController@update');			// 充值更新
Macaw::post('/recharge/callback', 'App\Controller\RechargeController@callback');		// 充值支付回调
Macaw::post('/recharge/71_callback', 'App\Controller\RechargeController@callback_71pay');// 充值支付回调
Macaw::get('/recharge/you_callback', 'App\Controller\RechargeController@callback_you');	// 优支付回调
Macaw::get('/recharge/you_callback2', 'App\Controller\RechargeController@callback_you2');	// 优支付回调2
Macaw::get('/recharge/you_callback3', 'App\Controller\RechargeController@callback_you3');	// 优支付回调3
Macaw::get('/recharge/wm_callback', 'App\Controller\RechargeController@callback_wm');	// 完美支付回调
Macaw::post('/recharge/qj_callback', 'App\Controller\RechargeController@callback_qj');	// 钱进支付回调
Macaw::post('/recharge/wx_callback', 'App\Controller\RechargeController@callback_wx');	// 微信支付回调
Macaw::get('/recharges', 'App\Controller\RechargeController@index');					// 充值列表
Macaw::get('/recharge/first', 'App\Controller\RechargeController@first');				// 首充大礼拜
Macaw::post('/recharge/first', 'App\Controller\RechargeController@first');				// 首充
Macaw::get('/recharge/(:num)', 'App\Controller\RechargeController@detail');				// 订单状态

Macaw::get('/scores', 'App\Controller\ScoreLogController@index');						// 积分获取列表

Macaw::post('/order', 'App\Controller\OrderController@create');							// 下订单(异步)
Macaw::post('/order_directly', 'App\Controller\OrderController@createDirectly');		// 下订单(同步)
Macaw::get('/orders', 'App\Controller\OrderController@index');							// 订单列表
Macaw::post('/order/progress', 'App\Controller\OrderController@updateProgress');		// 更新进度

Macaw::get('/banners', 'App\Controller\BannerController@index');						// banners

Macaw::get('/hot-people', 'App\Controller\HotPeopleController@index');					// 空间红人
Macaw::get('/shareRanks', 'App\Controller\HotPeopleController@shareRanks');				// 分享排行榜

Macaw::get('/shuoshuo', 'App\Controller\UserController@qqTwittees');					// 获取发表的说说
Macaw::get('/qqBlogs', 'App\Controller\UserController@qqBlogs');						// 获取发表的日志
Macaw::get('/broadcasts', 'App\Controller\UserController@cheatBroadcasts');				// 订单广播，假数据

Macaw::get('/config', 'App\Controller\ConfigController@index');							// 获取配置

Macaw::any('/laquanquan', 'App\Controller\UserController@laquanquan');					// 拉拉圈假业务接口

Macaw::post('/praise', 'App\Controller\UserController@praise');							// 应用市场好评送积分

// 留言相关
Macaw::post('/feedback', 'App\Controller\FeedbackController@create');					// 创建反馈
Macaw::get('/feedbacks', 'App\Controller\FeedbackController@index');					// 反馈列表

// 投诉
Macaw::post('/complaint', 'App\Controller\ComplaintController@create');					// 创建反馈
Macaw::get('/complaints', 'App\Controller\ComplaintController@index');					// 反馈列表

Macaw::get('/test', 'App\Controller\RechargeController@youPayNotify');

Macaw::dispatch();
