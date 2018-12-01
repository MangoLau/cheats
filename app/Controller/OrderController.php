<?php

namespace App\Controller;

use App\Model\CheatProduct;
use App\Model\Order;
use App\Model\User;
use App\Model\Card;
use App\Model\Cheat;
use App\Model\ScoreLog;
use App\Middleware\Cheat as C;
use App\Middleware\Credis;

class OrderController extends BaseController
{
	const PRODUCT_STATUS_ONLINE = 1;			// 上架
	const PRODUCT_STATUS_OFFLINE = 0;			// 下架

	const ORDER_STATUS_DEALING = 1;				// 进行中
	const ORDER_STATUS_COMPLETE = 2;			// 完成
	const ORDER_STATUS_FAILED = 3;				// 失败

	const CARD_STATUS_ONLINE = 1;				// 卡密使用中
	const CARD_STATUS_OFFLINE = 0;				// 卡密已删除

	const CHEAT_STATUS_ONLINE = 1;				// 刷赞类型使用中
	const CHEAT_STATUS_OFFLINE = 0;				// 已废弃的刷赞类型

	const PLATFORM_ANDROID = 0;					// 安卓平台
	const PLATFORM_IOS = 1;						// iOS平台

	const ORDER_QUEUE_KEY = 'order-queue';		// 队列处理的key

	public function index()
	{
		$paging_data = $this->getPageCount();
		if (!empty($_GET['status'])) {
			$orders = Order::getAll('SELECT o.*, c.title FROM `orders` o, `cheats` c WHERE o.uid = ? AND o.status = ? AND o.cid = c.id ORDER BY o.id DESC LIMIT ?, ?', [ $this->token->uid, $_GET['status'], $paging_data['start'], $paging_data['count'] ]);
		} else {
			$orders = Order::getAll('SELECT o.*, c.title FROM `orders` o, `cheats` c WHERE o.uid = ? AND o.cid = c.id ORDER BY o.id DESC LIMIT ?, ?', [ $this->token->uid, $paging_data['start'], $paging_data['count'] ]);
		}
		
		$ret = [];

		if (foreachAble($orders)) {
			foreach ($orders as $k => $order) {
				$ret[$k]['id'] = $order['id'];
				$ret[$k]['qq'] = $order['qq'];
				$ret[$k]['type'] = $order['title'];
				$ret[$k]['amount'] = $order['amount'];
				$ret[$k]['real_amount'] = $order['real_amount'];
				$ret[$k]['scores'] = $order['scores'];
				$ret[$k]['status'] = strval($order['status']);
				$ret[$k]['created_at'] = strtotime($order['created_at']);
				$ret[$k]['reason'] = '';
			}
		}

		$this->return_success($ret);
	}

	/**
	 * 下单
	 */
	public function create()
	{
		$cpid = $_POST['cpid'];
		$channel = $_POST['channel'] ?: '';
		// $qq = $_POST['qq'];
		
		$ssid = $_POST['ssid'] ?: '';			// 说说id
		$ssnr = $_POST['ssnr'] ?: '';			// 说说内容
        $plnr = $_POST['plnr'] ?: '';           // 说说评论内容

		$rzid = $_POST['rzid'] ?: '';			// 日志id
		$rznr = $_POST['rznr'] ?: '';			// 日志内容

		$ksid = $_POST['ksid'] ?: '';			// 快手用户ID
		$zpid = $_POST['zpid'] ?: '';			// 快手作品ID
		$qmkg_gqid = $_POST['qmkg_gqid'] ?: ''; // K歌歌曲ID

		$douyin_uid = $_POST['douyin_uid'] ? : '';		// 抖音用户ID
		$douyin_zpid = $_POST['douyin_zpid'] ? : '';	// 抖音作品ID, 客户端传过来的是链接，需要处理

		$ks_url = $_POST['ks_url'];				// 快手链接
		$qmkg_url = $_POST['qmkg_url'];			// 全民K歌链接
        $kszp_url = $douyin_url = '';                       //抖音链接

		// 快手链接
		if (!empty($ks_url)) {
			if (!filter_var($ks_url, FILTER_VALIDATE_URL)) {
				$this->return_error(400, '快手链接不合法');
				return;
			}

//			$ks_params = getKuaishouZpidAndUid($ks_url);
//			$ksid = $ks_params['userId'];
//			$zpid = $ks_params['photoId'];
			$kszp_url = $ks_url;// 直接把链接链接提交到卡盟
            $ksid = '';
            $zpid = '';
		}

		// 全民K歌链接
		if (!empty($qmkg_url)) {
			if (!filter_var($qmkg_url, FILTER_VALIDATE_URL)) {
			    $qmkg_url = getUrlByStr($qmkg_url);
			    if (empty($qmkg_url) || !filter_var($qmkg_url, FILTER_VALIDATE_URL)) {
                    $this->return_error(400, '全民K歌链接不合法');
                    return;
                }
			}
			$qmkg_gqid = getQmkgId($qmkg_url);
		}

		if (empty($cpid)) {
			$this->return_error(400, '商品不存在');
			return;
		}

		// 获取商品信息
        $product = CheatProduct::findOne('cheatproducts', ' id = ? AND status = ? ', [ $cpid, self::PRODUCT_STATUS_ONLINE ]);
        if (empty($product)) {
            $this->return_error(401, '商品不存在');
            return;
        }

        // 说说评论
        if ($product->cid == 26) {
            if (empty($plnr)) {
                $this->return_error(407, '请填写评论内容');
                return;
            }
            if (mb_strlen($plnr) > 35) {
                $this->return_error(407, '评论内容在35个字符以内');
                return;
            }
            if (empty($ssid)) {
                $this->return_error(407, '说说ID有误');
                return;
            }
        }

        // 抖音作品评论
        if ($product->cid == 30) {
            if (empty($douyin_zpid)) {
                $this->return_error(408, '请输入抖音链接');
                return;
            }
            if (!filter_var($douyin_zpid, FILTER_VALIDATE_URL)) {
                $douyin_url = getDouyinUrl($douyin_zpid);
                if (!$douyin_url) {
                    $this->return_error(406, '抖音链接不合法');
                    return;
                }
            } else {
                $douyin_url = $douyin_zpid;
            }
            $douyin_url = getEffectiveUrl($douyin_url);
            $douyin_zpid = (explode('/', $douyin_url))[5];
            $douyin_url = '';
            if (empty($douyin_zpid)) {
                $this->return_error(406, '抖音链接不合法');
                return;
            }

        } else {
            // 抖音作品链接
            if (!empty($douyin_zpid)) {
                if (!filter_var($douyin_zpid, FILTER_VALIDATE_URL)) {
                    $douyin_url = getDouyinUrl($douyin_zpid);
                    if (!$douyin_url) {
                        $this->return_error(406, '抖音链接不合法');
                        return;
                    }
                } else {
                    $douyin_url = $douyin_zpid;
                }
                $douyin_zpid = '';
            }

            // 抖音个人中心链接
            if (!empty($douyin_uid)) {
                if (!filter_var($douyin_uid, FILTER_VALIDATE_URL)) {
                    $douyin_url = getUrlByStr($douyin_uid);
                    if (!$douyin_url) {
                        $this->return_error(406, '抖音链接不合法');
                        return;
                    }
                } else {
                    $douyin_url = $douyin_uid;
                }
                $douyin_uid = '';
            }
        }

        $user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);
        if (empty($user)) {
            $this->return_error();
            return;
        }
        if (empty($user->qq)) {
            $this->return_error(403, '请先绑定QQ');
            return;
        }

        // 获取用户积分是否足够
        if ($user->remaining_scores < $product->scores) {
            $this->return_error(403, '积分不足请先充值');
            return;
        }

        // 获取该用何种类型的卡密
        $card = Card::findOne('cards', ' remaining >= ? AND type = ? AND status = ? ', [ $product->amount, $product->cid, self::CARD_STATUS_ONLINE ]);
        if (empty($card)) {
            $this->error('order create failed - no card', [ $cpid, $channel, $this->token->uid ]);
            $this->return_error(500, '该业务暂时不可用，请稍后再试');
            return;
        }

        $cheat = Cheat::findOne('cheats', ' id = ? AND status = ? ', [ $product->cid, self::CHEAT_STATUS_ONLINE ]);
        if (empty($cheat)) {
            $this->return_error();
        } else {
            // 判断该业务是否需要vip身份
            if ($cheat->need_vip && $user->vip_deadline < getCurrentTime()) {
                $this->return_error(405, '该业务只开放给VIP用户');
            } else {
                // 事务处理
                Order::begin();
                try {
                    // 是否是拉圈圈业务
                    $is_laquanquan = $cheat->remark == 'laquanquan';

                    // 更新卡密剩余额度
                    $card->remaining =  $card->total-$product->amount;
                    Card::store($card);

                    // 更新用户积分
                    $user->remaining_scores -= $product->scores;
                    User::store($user);

                    // 积分变动记录
                    ScoreLog::orderSpending($user->id, $product->scores, $cheat->title);

                    // 生成订单
                    $order = Order::dispense('orders');
                    $order->identify = $card->identify;
                    $order->uid = $user->id;
                    $order->qq = $user->qq;
                    $order->cid = $product->cid;
                    $order->amount = $product->amount;
                    $order->real_amount = 0;
                    $order->scores = $product->scores;
                    $order->ssid = $ssid;
                    $order->rzid = $rzid;
                    $order->ksid = $ksid;
                    $order->zpid = $zpid;
                    $order->kszp_url = $kszp_url;
                    $order->qmkg_gqid = $qmkg_gqid;
                    $order->douyin_uid = $douyin_uid;
                    $order->douyin_zpid = $douyin_zpid;
                    $order->douyin_url = $douyin_url;
                    $order->channel = $channel;
                    $order->plnr = $plnr;
                    $order->platform = strtolower($this->platform) == 'android' ? self::PLATFORM_ANDROID : self::PLATFORM_IOS;
                    $order->status = $is_laquanquan ? self::ORDER_STATUS_COMPLETE : self::ORDER_STATUS_DEALING;	//
                    $order->created_day = date('Ymd', getCurrentTime());
                    Order::store($order);

                    Order::commit();

                    // 进入队列进行处理
                    $redis = Credis::getInstance();
                    $redis->rpush(self::ORDER_QUEUE_KEY, $order->id);

                    $ret = [];
                    if (foreachAble($order)) {
                        $ret['id'] = $order->id;
                        $ret['qq'] = $order->qq;
                        $ret['type'] = $cheat->title;
                        $ret['amount'] = $product->amount;
                        $ret['scores'] = $order->scores;
                        $ret['status'] = strval($order->status);
                        $ret['created_at'] = strval(getCurrentTime());
                    }

                    $this->return_success($ret);
                } catch (Exception $e) {
                    $this->error('order create rollbak', [$e->getMessages()]);
                    Order::rollback();

                    $this->return_error();
                }
            }
        }
	}

	/**
	 * 更新订单进度
	 */
	public function updateProgress()
	{
		$order_complete_state = 3;			// 订单已完成的状态码
		$handle_count = 100;				// 每次处理数量

		$orders = Order::findAll('orders', ' WHERE `status` = ? ORDER BY id ASC LIMIT ? ', [ self::ORDER_STATUS_DEALING, $handle_count ]);

		if (!empty($orders) && foreachAble($orders)) {
			foreach ($orders as $k => $order) {
				$this->debug('order progress update step 1', $orders);
				$page = 1;
				$cheat = Cheat::findOne('cheats', ' id = ? ', [ $order->cid ]);
				if (empty($cheat)) {
					$this->error('order progress update error - no cheat', (array)$order);
					continue;
				}

				$c = new C($order->identify);
				$c->setLoginUrl($cheat->login_url);
				$c->setProgressUrl($cheat->progress_url);
				$ret = $c->orderProgress($page, $order->qq);
				if ($ret->error) {
					$this->error('order progress update error - curl error', [ $ret->error, $curl->response, $order ]);
				} else {
					$response = is_object($ret->response) ? $ret->response : json_decode($ret->response);
					$data = $response->exhibitDatas;
					$this->debug('order progress update step 2', [ $response ]);
					if (!empty($data)) {
						$this->debug('order progress update step 3', [ $data ]);
						if (foreachAble($data)) {
							foreach ($data as $d) {
								if ($d->id == $order->order_id) {
									$order->real_amount = $d->now_num - $d->start_num;
									if ($order->real_amount >= $order->amount || $d->order_state == $order_complete_state) {
										$order->status = self::ORDER_STATUS_COMPLETE;
									}

									if (!Order::store($order)) {
										$this->error('order progress update error - store order error', (array)$order);
									}

									$this->debug('order progress update step 4', [ $order ]);
								}
							}
						}
					} else {
						$this->debug('order progress update step 5', []);
					}
				}
			}
		} else {
			$this->warning('order progress update - no orders', []);
		}
	}

	/**
	 * 直接下单
	 */
	public function createDirectly()
	{
		$cpid = $_POST['cpid'];
		$channel = $_POST['channel'] ?: '';
		// $qq = $_POST['qq'];
		
		$ssid = $_POST['ssid'] ?: '';			// 说说id
		$ssnr = $_POST['ssnr'] ?: '';			// 说说内容

		$rzid = $_POST['rzid'] ?: '';			// 日志id
		$rznr = $_POST['rznr'] ?: '';			// 日志内容

		$ksid = $_POST['ksid'] ?: '';			// 快手用户ID
		$zpid = $_POST['zpid'] ?: '';			// 快手作品ID
		$qmkg_gqid = $_POST['qmkg_gqid'] ?: ''; // K歌歌曲ID
		$douyin_uid = $_POST['douyin_uid'] ? : '';		// 抖音用户ID
		$douyin_zpid = $_POST['douyin_zpid'] ? : '';	// 抖音作品ID, 客户端传过来的是链接，需要处理
		// 处理抖音链接
		if (!empty($douyin_zpid)) {
			if (!filter_var($douyin_zpid, FILTER_VALIDATE_URL)) {
				$this->return_error(400, '抖音链接不合法');
				return;
			}

			$douyin_zpid = explode('/', $douyin_zpid)[5];
			if (empty($douyin_zpid)) {
				$this->return_error(401, '抖音链接不合法');
				return;
			}
		}

		if (empty($cpid)) {
			$this->return_error(400, '商品不存在');
		} else {
			$product = CheatProduct::findOne('cheatproducts', ' id = ? AND status = ? ', [ $cpid, self::PRODUCT_STATUS_ONLINE ]);

			if (empty($product)) {
				$this->return_error(401, '商品不存在');
			} else {
				$user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);

				if (empty($user)) {
					$this->return_error();
				} elseif (empty($user->qq)) {
					$this->return_error(403, '请先绑定QQ');
				} else {
					// 获取用户积分是否足够
					if ($user->remaining_scores < $product->scores) {
						$this->return_error(403, '积分不足请先充值');
					} else {
						// 获取该用何种类型的卡密
						$card = Card::findOne('cards', ' remaining >= ? AND type = ? AND status = ? ', [ $product->amount, $product->cid, self::CARD_STATUS_ONLINE ]);

						if (empty($card)) {
							$this->error('order create failed - no card', [ $cpid, $channel, $this->token->uid ]);
							$this->return_error(500, '该业务暂时不可用，请稍后再试');
						} else {
							$cheat = Cheat::findOne('cheats', ' id = ? AND status = ? ', [ $product->cid, self::CHEAT_STATUS_ONLINE ]);
							if (empty($cheat)) {
								$this->return_error();
							} else {
								// 判断该业务是否需要vip身份
								if ($cheat->need_vip && $user->vip_deadline < getCurrentTime()) {
									$this->return_error(405, '该业务只开放给VIP用户');
								} else {
									// 是否是拉圈圈业务
									$is_laquanquan = $cheat->remark == 'laquanquan';

									$c = new C($card->identify);
									$c->setLoginUrl($cheat->login_url);
									$c->setUrl($cheat->url);
	
									// 其他参数
									$extra = [];
									if (!empty($ssid)) {
										$extra['ssid'] = $ssid;
									}
									if (!empty($rzid)) {
										$extra['rzid'] = $rzid;
									}
									if (!empty($ksid)) {
										$extra['ksid'] = $ksid;
									}
									if (!empty($zpid)) {
										$extra['zpid'] = $zpid;
									}
									if (!empty($qmkg_gqid)) {
										$extra['qmkg_gqid'] = $qmkg_gqid;
									}
									if (!empty($douyin_uid)) {
										$extra['zh'] = $douyin_uid;
									}
									if (!empty($douyin_zpid)) {
										$extra['zh'] = $douyin_zpid;
									}
	
									$ret = $c->handle($user->qq, $product->amount, $extra);
									if ($ret->error) {
										$this->error('order curl error', (array)$ret);
										$this->return_error(222, [ $ret->error, $ret->errorMessage, $ret->curlErrorMessage, $cheat->login_url, $cheat->url, $ret ]);
									} else {
										$response = $ret->response;
	
										if ($response->status == 0) {
											$this->error('order curl response status 0', [$response, $_POST]);
											$this->return_error(501, $response->info);
										} else {
											// 事务处理
											Order::begin();
											try {
												// 更新卡密剩余额度
												$card->remaining =  $response->after_use_cardnum;
												Card::store($card);
	
												// 更新用户积分
												$user->remaining_scores -= $product->scores;
												User::store($user);

												// 积分变动记录
												ScoreLog::orderSpending($user->id, $product->scores, $cheat->title);
	
												// 生成订单
												$order = Order::dispense('orders');
												$order->order_id = $response->order_id;
												$order->identify = $card->identify;
												$order->uid = $user->id;
												$order->qq = $user->qq;
												$order->cid = $product->cid;
												$order->amount = $product->amount;
												$order->real_amount = 0;
												$order->scores = $product->scores;
												$order->ssid = $ssid;
												$order->rzid = $rzid;
												$order->ksid = $ksid;
												$order->zpid = $zpid;
												$order->qmkg_gqid = $qmkg_gqid;
												$order->channel = $channel;
												$order->platform = strtolower($this->platform) == 'android' ? self::PLATFORM_ANDROID : self::PLATFORM_IOS;
												$order->status = $is_laquanquan ? self::ORDER_STATUS_COMPLETE : self::ORDER_STATUS_DEALING;	// 
												$order->created_day = date('Ymd', getCurrentTime());
												Order::store($order);
		
												Order::commit();
												
												$ret = [];
												if (foreachAble($order)) {
													$ret['id'] = $order->id;
													$ret['qq'] = $order->qq;
													$ret['type'] = $cheat->title;
													$ret['amount'] = $product->amount;
													$ret['scores'] = $order->scores;
													$ret['status'] = strval($order->status);
													$ret['created_at'] = strval(getCurrentTime());
												}
	
												$this->return_success($ret);
											} catch (Exception $e) {
												$this->error('order create rollbak', [$e->getMessages()]);
												Order::rollback();
							
												$this->return_error();
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}

