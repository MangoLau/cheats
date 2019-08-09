<?php

namespace App\Controller;

use App\Model\User;
use App\Model\Token;
use App\Model\Product;
use App\Model\ScoreLog;
use App\Model\Config;
use App\Model\Card;
use App\Middleware\Cheat;
use App\Middleware\Qzone;

/**
* 用户
*/
class UserController extends BaseController
{
	CONST PLATFORM_QQ = 'qq';						// qq平台
		
	CONST PRODUCT_TYPE_SCORE = 1;					// 积分产品
	CONST PRODUCT_TYPE_VIP = 2;						// vip产品
		
	CONST STATUS_ONLINE = 1;						// 启用
	CONST STATUS_OFFLINE = 0;						// 停用

	CONST QZONE_STATUS_PRIVATE_ERROR_CODE = 403;	// 说说、日志列表无权限错误码

	CONST TYPE_PRAISE = 5;							// score_log 应用市场好评赠送积分
	CONST TYPE_INVITE = 6;							// score_log 邀请赠送积分
	CONST TYPE_INVITED = 7;							// score_log 被邀请者赠送积分


	/**
	 * 登录
	 */
	public function login()
	{
		$openid = $_POST['openid'];
		$nickname = $_POST['nickname'];
		$avatar = $_POST['avatar'];

		if (empty($openid) || empty($nickname) || empty($avatar)) {
			$this->return_error(400, '参数不完整');
		} else {
			$user = User::findOne('users', ' openid = ? AND platform = ? ', [ $openid, self::PLATFORM_QQ ]);
			if (empty($user)) {
				$user = User::dispense('users');
				$user->openid = $openid;
				$user->platform = self::PLATFORM_QQ;
				$user->username = mb_substr(removeEmoji($nickname), 0, 40);		// 字段长度为40
				$user->nickname = mb_substr($nickname, 0, 80);
				$user->avatar = $avatar;
				$user->created_day = date('Ymd', getCurrentTime());

				if (User::store($user)) {
					$token = Token::dispense('tokens');
					$token->uid = $user->id;
					$token->key = stristr(PHP_OS, 'WIN') ? generateRandomString(28) : getRealRandomStr(28);
					$token->expires_in = intval(getCurrentTime() + getConfig('app.token.expire'));

					if (Token::store($token)) {
						$this->return_success(
							[
								'id' => $user->id,
								'access_token' => $token->key,
								'qq' => $user->qq ?: '',
								'nickname' => $user->nickname,
								'avatar' => $avatar,
								'vip_deadline' => $user->vip_deadline ?: '0',
								'scores' => $user->remaining_scores ?: '0',
								'inviter' => $user->inviter,
							]
						);
					} else {
						$this->error('create token failed', (array)$token);
						$this->return_error();
					}
				} else {
					$this->error('create user failed', (array)$user);
					$this->return_error();
				}
			} else {
				$token = Token::findOne('tokens', ' uid = ? AND expires_in > ? ', [ $user->id, getCurrentTime() ]);
				if (empty($token)) {
					$token = Token::dispense('tokens');
					$token->uid = $user->id;
					$token->key = stristr(PHP_OS, 'WIN') ? generateRandomString(28) : getRealRandomStr(28);
					$token->expires_in = intval(getCurrentTime() + getConfig('app.token.expire'));

					if (Token::store($token)) {
						$this->return_success(
							[
								'id' => $user->id,
								'access_token' => $token->key,
								'qq' => $user->qq ?: '',
								'nickname' => $nickname,
								'avatar' => $avatar,
								'vip_deadline' => $user->vip_deadline,
								'scores' => $user->remaining_scores,
								'inviter' => $user->inviter,
							]
						);
					} else {
						$this->error('create token failed', (array)$token);
						$this->return_error();
					}
				} else {
					$this->return_success(
						[
							'id' => $user->id,
							'access_token' => $token->key,
							'qq' => $user->qq ?: '',
							'nickname' => $nickname,
							'avatar' => $avatar,
							'vip_deadline' => $user->vip_deadline,
							'scores' => $user->remaining_scores,
							'inviter' => $user->inviter,
						]
					);
				}
			}
		}
	}

	/**
	 * 用户详情
	 */
	public function detail()
	{
		$user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);
		if (empty($user)) {
			$this->return_error();
		} else {
			// 邀请人数及收益
			$invite_count = User::count('users', ' inviter = ? ', [ $user->id ]);
			$invite_scores = User::getCell(' SELECT SUM(`amount`) FROM `scorelogs` WHERE `uid` = ' . $user->id . ' AND `type` = ' . self::TYPE_INVITE);

			$this->return_success(
				[
					'id' => $user->id,
					'qq' => $user->qq ?: '',
					'nickname' => $user->nickname,
					'avatar' => $user->avatar,
					'vip_deadline' => $user->vip_deadline,
					'scores' => $user->remaining_scores,
					'inviter' => $user->inviter,
					'invite_count' => intval($invite_count),
					'invite_scores' => intval($invite_scores),	
				]
			);
		}
	}

	// uid是否存在
	public function exists()
	{
		$uid = $_GET['uid'];
		$exists = !empty(User::findOne('users', ' id = ? ', [$uid]));

		$this->return_success([
			'exists' => $exists,
		]);
	}

	/**
	 * 绑定邀请者
	 */
	public function bindInviter()
	{
		$uid = $_POST['inviter'];

		if (empty($uid)) {
			$this->return_error(400, '参数不完整');
		} elseif ($uid == $this->token->uid) {
			$this->return_error(403, '不能邀请自己');
		} else {
			$current_user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);
			if (empty($current_user)) {
				$this->error('bindInviter failed current_user not exists', [ $uid, $this->token ]);
				$this->return_error();
			} elseif (!empty($current_user->inviter)) {
				$this->return_error(403, '您已绑定过邀请者，不能重复绑定');
			} else {
				// 限定绑定次数
				if ($uid == 466789) {
					$this->return_error(405, '绑定次数超过限制，请绑定其他ID');
					return;
				}
				
				$inviter = User::findOne('users', ' id = ? ', [ $uid ]);
				if (empty($inviter)) {
					$this->return_error(405, '邀请者不存在');
				} else {
					$present_invite_scores = Config::getInviteFriendScores();
					$present_invited_scores = Config::getInvitedScores();
					if (empty($present_invite_scores) || empty($present_invited_scores)) {
						$this->error('bindInviter failed config not exists', [ $uid, $this->token ]);
						$this->return_error();
					} else {
						User::begin();
						try {
							// 设置被邀请者的邀请者id
							$current_user->inviter = $uid;
							$current_user->remaining_scores += $present_invited_scores;
							$current_user->total_scores += $present_invited_scores;
							User::store($current_user);

							// 增加邀请者的积分
							$inviter->remaining_scores += $present_invite_scores;
							$inviter->total_scores += $present_invite_scores;
							User::store($inviter);

							// 积分变动记录
							$arr_score_logs = ScoreLog::dispense('scorelogs', 2);
							$arr_score_logs[0]->uid = $current_user->id;
							$arr_score_logs[0]->type = self::TYPE_INVITED;
							$arr_score_logs[0]->amount = $present_invited_scores;
							ScoreLog::store($arr_score_logs[0]);

							$arr_score_logs[1]->uid = $inviter->id;
							$arr_score_logs[1]->type = self::TYPE_INVITE;
							$arr_score_logs[1]->amount = $present_invite_scores;
							ScoreLog::store($arr_score_logs[1]);

							User::commit();

							$this->return_success();
						} catch (Exception $e) {
							User::rollback();
							$this->error('bindInviter failed', [ $this->token, $uid, $e->getMessages() ]);
							$this->return_error();
						}
					}
				}
			}
		}
	}

	/**
	 * 绑定QQ
	 */
	public function bindQQ()
	{
		$qq = $_POST['qq'];

		if (isQQ($qq)) {
			$user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);
			if (empty($user)) {
				$this->return_error();
			} else {
				$user->qq = $qq;
				if (User::store($user)) {
					$this->return_success();
				} else {
					$this->return_error();
				}
			}
		} else {
			$this->return_error(400, 'qq不合法');
		}
	}

	/**
	 * 解绑QQ
	 */
	public function unbindQQ()
	{
		$user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);

		if (empty($user)) {
			$this->return_error();
		} else {
			if (empty($user->qq)) {
				$this->return_success();
			} else {
				$user->qq = 0;

				if (User::store($user)) {
					$this->return_success();
				} else {
					$this->return_error();
				}
			}
		}
	}

	/**
	 * 刷新token
	 * 如果传入的token并未过期，暂时原值返回
	 */
	public function refreshToken()
	{
		$access_token = $this->getCurrentAccesstoken();
		if (empty($access_token)) {
			$this->return_error(400, 'access_token不能为空');
		} else {
			$token = Token::findOne('tokens', ' `key` = ? ', [ $access_token ]);
			if (empty($token)) {
				$this->return_error(400, 'access_token不存在');
			} elseif ($token->expires_in > getCurrentTime()) {
				$this->return_success(
					[
						'access_token' => $access_token,
					]
				);
			} else {
				$new_token = Token::dispense('tokens');
				$new_token->uid = $token->uid;
				$new_token->key = stristr(PHP_OS, 'WIN') ? generateRandomString(28) : getRealRandomStr(28);
				$new_token->expires_in = intval(getCurrentTime() + getConfig('app.token.expire'));
	
				if (Token::store($new_token)) {
					$this->return_success(
						[
							'access_token' => $new_token->key,
						]
					);
				} else {
					$this->return_error();
				}
			}
		}
	}

	/**
	 * 用户应用市场好评送积分
	 */
	public function praise()
	{
		$score_log = ScoreLog::findOne('scorelogs', ' uid = ? AND type = ? ', [ $this->token->uid, self::TYPE_PRAISE ]);
		if (empty($score_log)) {
			$present_scores = Config::getFiveStarsCommentScores();
			if (empty($present_scores)) {
				$this->error('five stars comment scores config not exists', [ $this->token->uid ]);
				$this->return_error();
			} else {
				User::begin();
				try {
					$score_log = ScoreLog::dispense('scorelogs');
					$score_log->uid = $this->token->uid;
					$score_log->type = self::TYPE_PRAISE;
					$score_log->amount = $present_scores;
					ScoreLog::store($score_log);

					User::exec('UPDATE `users` SET `remaining_scores` = `remaining_scores` + ' . $present_scores . ', `total_scores` = `total_scores` + ' . $present_scores . ' WHERE id = ' . $this->token->uid);
					
					User::commit();

					$this->return_success();
				} catch (Exception $e) {
					User::rollback();
					$this->return_error();
					$this->error('five stars comment scores failed', [ $this->token->uid, $e->getMessages() ]);
				}
			}
		} else {
			$this->return_success();
		}
	}

	/**
	 * 获取发表的说说
	 */
	public function qqTwittees()
	{
		$qzone_right_public_status = 1;				// 空间权限状态码 - 所有人可见
		$qzone_right_private_status = 2;			// 空间权限状态码 - 非所有人可见
		$qzone_right_private_code = -10031;			// 空间权限非所有人可见时的code

		$user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);
		if (empty($user->qq)) {
			$this->return_error(401, '请先绑定QQ');
		} else {
			$ret = [];
			$page = $_GET['page'] ?: 1;
			/*$data = Qzone::getQQTwittees($user->qq, $page);
			if ($data['code'] > 0) {
				$this->return_error(self::QZONE_STATUS_PRIVATE_ERROR_CODE, '请先开启绑定QQ对所有人可见的权限(点击右上角图标，查看打开权限步骤)');
			} elseif (empty($data['result'])) {
				$this->return_error(404, '你还未发表过说说');
			} else {
				foreach ($data['result'] as $k => $v) {
					$ret[$k]['ssid'] = $v['tid'];
					$ret[$k]['content'] = $v['content'];
					$ret[$k]['created_time'] = strval($v['created_at']);
				}

				$this->return_success($ret);
			}*/

 			$cheat = new Cheat();
 			$data = $cheat->xdzkQQTwittess($user->qq, $page);
 			if (!empty($data->error)) {
 				$this->return_error();
 			} else {
 				$data = json_decode($data->response);

                 if ($data->right == $qzone_right_private_status || $data->code == $qzone_right_private_code) {
                     $this->return_error(self::QZONE_STATUS_PRIVATE_ERROR_CODE, '请先开启
 绑定QQ对所有人可见的权限(点击右上角图标，查看打开权限步骤)');
                 } else {
                     $data = $data->msglist;
                     if (empty($data)) {
                             $this->return_error(404, '你还未发表过说说');
                     } else {
                         if (foreachAble($data)) {
                             foreach ($data as $k => $v) {
                                 $ret[$k]['ssid'] = (string)$v->tid;
                                 $ret[$k]['content'] = $v->content ?: $v->rt_title;
                                 $ret[$k]['created_time'] = (string)$v->created_time;
                             }
                         }

                         $this->return_success($ret);
                 	}
                 }
 			}
		}
	}

	/**
	 * 获取发表的日志
	 * count = 15
	 */
	public function qqBlogs()
	{
		$user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);
		if (empty($user->qq)) {
			$this->return_error(401, '请先绑定QQ');
		} else {
			$ret = [];
			$page = $_GET['page'] ?: 1;
			$data = Cheat::xdzkQQArticles($user->qq, $page);

			if ($data->error) {
				$this->return_error();
			} else {
				$response = $data->response;
				$data = json_decode($response)->data;
				
				if (!empty($response->message) || !isset($data->totalNum)) {
					$this->return_error(self::QZONE_STATUS_PRIVATE_ERROR_CODE, '请先开启绑定QQ对所有人可见的权限(点击右上角图标，查看打开权限步骤)');
				} elseif ($data->totalNum == 0) {
					$this->return_error(404, '你还未发表过日志');
				} else {
					if (foreachAble($data->list)) {
						foreach ($data->list as $k => $v) {
							$ret[$k]['rzid'] = (string)$v->blogId;
							$ret[$k]['content'] = $v->title ?: '无';
							$ret[$k]['created_time'] = (string)strtotime($v->pubTime);
						}
						$this->return_success($ret);
					}
				}
			}
		}
	}

	/**
	 * 广播假数据
	 * 格式: 38845****  18元购买10000积分
	 */
	public function cheatBroadcasts()
	{
		$count = intval($_GET['count']) > 0 ? intval($_GET['count']) : 15;
		$products = Product::findAll('products', ' status = ? ', [ self::STATUS_ONLINE ]);

		$product_ids = array_keys($products);
		$product_counts = count($product_ids);

		$ret = [];
		for ($i = 0; $i < $count; $i++) {
			$product = $products[$product_ids[mt_rand(0, $product_counts-1)]];
			$ret[] = mt_rand(12345, 98765) . '*****' . sprintf('%.2f', $product->money/100) . '元购买了' . $product->amount . ($product->type == self::PRODUCT_TYPE_SCORE ? '积分' : '个月VIP');
		}

		$this->return_success($ret);
	}

	/**
	 * 拉圈圈假接口
	 */
	public function laquanquan()
	{
		$this->json_encode_output([
			'status' => 1,
			'after_use_cardnum' => 10000,
			'info' => '下单成功',
			'order_id' => 0,
			'remark' => '此接口未执行任何业务'
		]);
	}
}