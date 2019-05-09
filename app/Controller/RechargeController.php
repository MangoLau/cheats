<?php

namespace App\Controller;

use App\Model\Product;
use App\Model\Recharge;
use App\Model\User;
use App\Model\Attendance;
use App\Model\Config;
use App\Model\ScoreLog;
use EasyWeChat\Core\Exception;
use EasyWeChat\Foundation\Application;
use function EasyWeChat\Payment\get_client_ip;
use EasyWeChat\Payment\Order;
use \Curl\Curl;

class RechargeController extends BaseController
{
    const PRODUCT_STATUS_ONLINE = 1;			// 产品状态-上架
    const PRODUCT_STATUS_OFFLINE = 0;			// 产品状态-下架

    const TYPE_SCORE = 1;						// 积分类型
    const TYPE_VIP = 2;							// VIP类型

    const PAY_STATUS_YES = 1;					// 已支付
    const PAY_STATUS_NO = 0;					// 未支付

    const PLATFORM_ANDROID = 0;					// 安卓平台
    const PLATFORM_IOS = 1;						// iOS平台

    const PAY_TYPE_WECHAT = 1;					// 微信支付
    const PAY_TYPE_ALIPAY = 2;					// 支付宝支付
    const PAY_TYPE_OTHER = 3;					// 其他方式

    // 积分变动记录类型
    const TYPE_ATTENDANCE = 1;					// 签到赠送
    const TYPE_RECHARGE = 2;					// 充值积分
    const TYPE_VIP_PRESENT = 3;					// 充值VIP赠送
    const TYPE_ORDER = 4;						// 下单消费

    // 比目订单查询接口返回的支付类型
    const BMOB_PAY_TYPE_WECHAT = 'WECHATPAY';	// 微信支付
    const BMOB_PAY_TYPE_ALIPAY = 'ALIPAY';		// 支付宝支付

    // 71pay
    const HFT_PAY_APPID = 2821;					// appid
    const HFT_PAY_APPID_2 = 2822;				// appid
    const HFT_PAY_APPID_MD5KEY = '896785df40334ac4a514812e9b0df5bb';	// md5_key
    const HFT_PAY_APPID_MD5KEY_2 = 'a5a176ab073e4ec3afc353de4fc441fb'; // md5_key
    // 话付通渠道
    const HFT_PAY_CHANNEL = [
        '2000113' => '5ad01bdbdad44cdda36e105f8a453ea4',
        '2000127' => '233d614ecec64f2a970ebb4b2a3f80fc',
        '2000128' => '6e42386b491c43a08542bcf540cfc5e2',
    ];

    // 优支付 账号配置
    const YOU_PAY_APPID = 10205;
    const YOU_PAY_APPID2 = 10215;
    const YOU_PAY_APPID3 = 10227;
    const YOU_PAY_APPKEY = '59ba1bfb7aa5cad976403aedfb3c5811';
    const YOU_PAY_APPKEY2 = '5b80e12c8d1c714b302aebb6a0aae924';
    const YOU_PAY_APPKEY3 = '86c393c06e0ec9a47d56789cfa237279';

    // 优支付回调订单状态
    const YOU_PAY_RESULT_SUCCESS = 1;
    const YOU_PAY_RESULT_FAILED = 2;
    const YOU_PAY_RESULT_ORDER_NOT_EXIST = 3;
    const YOU_PAY_RESULT_PARAM_WRONG = 4;

    // 优支付支付方式
    const YOU_PAY_PAYTYPE_WECHAT = 11;
    const YOU_PAY_PAYTYPE_ALIPAY = 12;

    // 完美点卡支付
    const WM_APPID = 15154817065411934;
    const WM_APPSECRET = '2b6a166be270342a9e465a183354661a';
    const WM_PAY_SUCCESS = 1;
    const WM_PAY_FAILED = 2;

    // config
    const CONFIG_ONLINE = 1;

    // 回调地址
    const PAY_CALLBACK_URL = [
        'youpay' => 'http://106.75.77.8/recharge/you_callback3',
        '71pay' => 'http://106.75.77.8/recharge/71_callback',
        'wmpay' => 'http://106.75.77.8/recharge/wm_callback',
        'qjpay' => 'http://106.75.77.8/recharge/qj_callback',
    ];
    const PAY_CPPARAM = [
        '71pay' => '',
        'youpay' => 'dianzanyun',
        'wmpay' => 'dianzanyun-wm',
        'qjpay' => '',
    ];

    // 钱进支付
    const QJ_APPID = '887758015408';
    const QJ_APPSECRET = '11fc7f14cd70cdd82ba3b1ff9e64764e';
    const QJ_WECHAT_H5_PAY = 'wxhtml';		// 微信h5支付
    const QJ_QQ_H5_PAY = 'qqweb';			// QQ h5支付
    const QJ_ALI_H5_PAY = 'aliwap';			// 支付宝h5支付

    //先付
    const XF_APPID = 10227;
    const XF_APPKEY = '86c393c06e0ec9a47d56789cfa237279';
    const XF_CALLBACK = 'http://106.75.77.8/recharge/xf_callback';

    //先付2
    const XF_PARA_ID = 10972;
    const XF_APPID2 = 12772;
    const XF_APPKEY2 = 'b3f14bde604ac15ca3cb742caa39d2bd';
    const XF_CALLBACK2 = 'http://106.75.77.8/recharge/xf_callback';

    /**
     * 充值--废弃
     */
    public function create()
    {
        $bmob_order_id = strval($_POST['bmob_order_id'] ?: $_POST['71pay_order_id']);
        $product_id = $_POST['product_id'];
        $money = $_POST['money'];
        $channel = $this->channel;			// 渠道

        // 支付通道
        $pay_choice = Config::getPayChoice();
        if (empty($pay_choice)) {
            $this->return_error(500, '后台未配置支付通道');
            return;
        }

        if (empty($product_id) || empty($money)) {
            $this->error('rechare create failed: params wrong', [ $_POST ]);
            $this->return_error(400, '参数不完整');
        } else {
            $product = Product::findOne('products', ' id = ? AND status = ? ', [  $_POST['product_id'], self::PRODUCT_STATUS_ONLINE ]);
            if (empty($product)) {
                $this->return_error(400, '充值产品不存在');
            } else {
                $user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);
                if (empty($user)) {
                    $this->return_error();
                } else {
                    // 验证充值金额是否合法
                    $is_vip = $user->vip_deadline >= getCurrentTime();
                    $discount = $is_vip ? Config::getVipRechargeDiscount() : 100;
                    $real_money = ceil($product->money * $discount / 100);
                    if ($real_money != $money) {
                        $this->error('recharge money not valid', [ $user, $is_vip, $discount, $real_money, $_POST ]);
                        $this->return_error(401, '充值金额不合法');
                        exit;
                    }

                    $recharge = Recharge::dispense('recharges');
                    $recharge->bmob_order_id = $bmob_order_id;
                    $recharge->uid = $user->id;
                    $recharge->pid = $product->id;
                    $recharge->money = $real_money;
                    $recharge->type = $product->type;
                    $recharge->amount = $product->amount;
                    $recharge->attach = $product->attach;
                    $recharge->status = self::PAY_STATUS_NO;
                    $recharge->platform = strtolower($this->platform) == 'android' ? self::PLATFORM_ANDROID : self::PLATFORM_IOS;
                    $recharge->channel = strval($channel);

                    if (Recharge::store($recharge)) {
                        $this->return_success(
                            [
                                'id' => $recharge->id,
                                'product_id' => $recharge->pid,
                                'content' => $recharge->type == self::TYPE_SCORE ? $recharge->amount . '积分' : ($recharge->type == self::TYPE_VIP ? $recharge->amount . '个月VIP' : ''),
                                'type' => $recharge->type,
                                'amount' => $recharge->amount,
                                'status' => $recharge->status,
                                'created_at' => $recharge->created_at ?: getCurrentTime(),
                                'vip_deadline' => $user->vip_deadline ?: '0',
                                'scores' => $user->remaining_scores ?: '0',
                                'pay' => [
                                    'type' => $pay_choice,									// 支付方式
                                    'callback_url' => self::PAY_CALLBACK_URL[$pay_choice],	// 支付回调地址
                                    'back_url' => 'http://www.dianzanyun.com',				// 官网
                                    'cpparam' => self::PAY_CPPARAM[$pay_choice],			// 支付透传参数
                                ],
                                'channel' => [												// 话付通支付渠道
                                    'id' => array_keys(self::HFT_PAY_CHANNEL)[0],
                                    'key' => array_values(self::HFT_PAY_CHANNEL)[0],
                                ],
                            ]
                        );
                    } else {
                        $this->error('recharge failed', (array)$recharge);
                        $this->return_error();
                    }
                }
            }
        }

    }

    /**
     * 先付充值
     */
    public function xianfu_bak()
    {
        $product_id = $_POST['product_id'];
        $money = $_POST['money'];
        $paytype = $_POST['paytype'];

        if (!isset($_POST['product_id']) || empty($money) || empty($paytype)) {
            $this->error('rechare create failed: params wrong', [ $_POST ]);
            $this->return_error(400, '参数不完整');
            return false;
        }
        $user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);
        if (empty($user)) {
            $this->return_error();
            return false;
        }
        if ($product_id == 0) {// 首充
            $first_recharge_params = Config::getFirstRechargeParams();
            if (empty($first_recharge_params)) {
                $this->return_error(404, '无首充大礼包');
                return false;
            }
            if (!empty(Recharge::count('recharges', ' uid = ? AND status = ? ', [ $user->id, self::PAY_STATUS_YES ]))) {
                $this->return_error(403, '已经充值过，没有首充资格');
                return false;
            }
            $first_recharge_params = json_decode($first_recharge_params);
            $real_money = $first_recharge_params->money;

            $recharge = Recharge::dispense('recharges');
            $recharge->uid = $user->id;
            $recharge->money = $first_recharge_params->money;
            $recharge->type = self::TYPE_SCORE;
            $recharge->amount = $first_recharge_params->scores + $first_recharge_params->attach;
            $recharge->attach = $first_recharge_params->vip;
            $recharge->status = self::PAY_STATUS_NO;
            $recharge->platform = strtolower($this->platform) == 'android' ? self::PLATFORM_ANDROID : self::PLATFORM_IOS;
        } else {// 非首充
            $product = Product::findOne('products', ' id = ? AND status = ? ', [  $product_id, self::PRODUCT_STATUS_ONLINE ]);
            if (empty($product)) {
                $this->return_error(400, '充值产品不存在');
                return false;
            }
            // 验证充值金额是否合法
            $is_vip = $user->vip_deadline >= getCurrentTime();
            $discount = $is_vip ? Config::getVipRechargeDiscount() : 100;
            $real_money = ceil($product->money * $discount / 100);
            if ($real_money != $money) {
                $this->error('recharge money not valid', [ $user, $is_vip, $discount, $real_money, $_POST ]);
                $this->return_error(401, '充值金额不合法');
                exit;
            }

            $recharge = Recharge::dispense('recharges');
            $recharge->uid = $user->id;
            $recharge->pid = $product->id;
            $recharge->money = $real_money;
            $recharge->type = $product->type;
            $recharge->amount = $product->amount;
            $recharge->attach = $product->attach;
            $recharge->status = self::PAY_STATUS_NO;
            $recharge->platform = strtolower($this->platform) == 'android' ? self::PLATFORM_ANDROID : self::PLATFORM_IOS;
        }

        $payurl = Config::getXfPayUrl();
        if (empty($payurl)) {
            $this->error('xfPayUrl config is empty', [ $user, $real_money, $_POST ]);
            $this->return_error(401, '充值配置有误');
            return false;
        }

        if (Recharge::store($recharge)) {
            $payOpt = [
                'appid' => self::XF_APPID,
                'orderid' => $recharge->id,
                'fee' => $real_money,
                'tongbu_url' => self::XF_CALLBACK,
                'clientip' => get_client_ip(),
                'back_url' => 'http://www.dianzanyun.com',
                'sign' => md5(self::XF_APPID . $recharge->id . $real_money . self::XF_CALLBACK . self::XF_APPKEY),
                'sfrom' => 'wap',
                'paytype' => $paytype,
            ];
            $curl = new Curl();
            $curl->get($payurl, $payOpt);
            if ($curl->error) {
                $this->error('xianfu curl failed', $payOpt);
                $this->return_error(402, '支付请求错误');
                return false;
            } else {
                $r = $curl->response;
                try {
                    $r = json_decode($r, true);
                    //                    $this->error('curl debug:', [$r, $payOpt]);
                    $returncode = isset($r['code']) ? $r['code'] : null;
                    if ($returncode != 'success') {
                        $this->error('curl return error', [(array)$recharge, (array)$r]);
                        $this->return_error(403, '支付返回错误');
                        return false;
                    }
                    $ret = [
                        'id' => $recharge->id,
                        'product_id' => $recharge->pid,
                        'content' => $recharge->type == self::TYPE_SCORE ? $recharge->amount . '积分' : ($recharge->type == self::TYPE_VIP ? $recharge->amount . '个月VIP' : ''),
                        'type' => $recharge->type,
                        'amount' => $recharge->amount,
                        'status' => $recharge->status,
                        'created_at' => $recharge->created_at ?: getCurrentTime(),
                        'vip_deadline' => $user->vip_deadline ?: '0',
                        'scores' => $user->remaining_scores ?: '0',
                        'pay' => [
                            'payurl' => isset($r['msg']) ? $r['msg'] : '',
                            'paytype' => $paytype,
                        ],
                    ];
                    $this->return_success($ret);
                } catch (Exception $e) {
                    $this->error('curl return error----', (array)$recharge);
                    $this->return_error();
                    return false;
                }
            }
        } else {
            $this->error('recharge failed', (array)$recharge);
            $this->return_error();
        }
        return true;
    }

    /**
     * 先付充值
     */
    public function xianfu()
    {
        $product_id = $_POST['product_id'];
        $money = $_POST['money'];
        $paytype = $_POST['paytype'];

        if (!isset($_POST['product_id']) || empty($money) || empty($paytype)) {
            $this->error('rechare create failed: params wrong', [ $_POST ]);
            $this->return_error(400, '参数不完整');
            return false;
        }
        $user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);
        if (empty($user)) {
            $this->return_error();
            return false;
        }
        if ($product_id == 0) {// 首充
            $first_recharge_params = Config::getFirstRechargeParams();
            if (empty($first_recharge_params)) {
                $this->return_error(404, '无首充大礼包');
                return false;
            }
            if (!empty(Recharge::count('recharges', ' uid = ? AND status = ? ', [ $user->id, self::PAY_STATUS_YES ]))) {
                $this->return_error(403, '已经充值过，没有首充资格');
                return false;
            }
            $first_recharge_params = json_decode($first_recharge_params);
            $real_money = $first_recharge_params->money;

            $recharge = Recharge::dispense('recharges');
            $recharge->uid = $user->id;
            $recharge->money = $first_recharge_params->money;
            $recharge->type = self::TYPE_SCORE;
            $recharge->amount = $first_recharge_params->scores + $first_recharge_params->attach;
            $recharge->attach = $first_recharge_params->vip;
            $recharge->status = self::PAY_STATUS_NO;
            $recharge->platform = strtolower($this->platform) == 'android' ? self::PLATFORM_ANDROID : self::PLATFORM_IOS;
        } else {// 非首充
            $product = Product::findOne('products', ' id = ? AND status = ? ', [  $product_id, self::PRODUCT_STATUS_ONLINE ]);
            if (empty($product)) {
                $this->return_error(400, '充值产品不存在');
                return false;
            }
            // 验证充值金额是否合法
            $is_vip = $user->vip_deadline >= getCurrentTime();
            $discount = $is_vip ? Config::getVipRechargeDiscount() : 100;
            $real_money = ceil($product->money * $discount / 100);
            if ($real_money != $money) {
                $this->error('recharge money not valid', [ $user, $is_vip, $discount, $real_money, $_POST ]);
                $this->return_error(401, '充值金额不合法');
                exit;
            }

            $recharge = Recharge::dispense('recharges');
            $recharge->uid = $user->id;
            $recharge->pid = $product->id;
            $recharge->money = $real_money;
            $recharge->type = $product->type;
            $recharge->amount = $product->amount;
            $recharge->attach = $product->attach;
            $recharge->status = self::PAY_STATUS_NO;
            $recharge->platform = strtolower($this->platform) == 'android' ? self::PLATFORM_ANDROID : self::PLATFORM_IOS;
        }

        $payurl = 'http://pay.payfubao.com/sdk_transform/wx_wap_sdk';//Config::getXfPayUrl();
        if (empty($payurl)) {
            $this->error('xfPayUrl config is empty', [ $user, $real_money, $_POST ]);
            $this->return_error(401, '充值配置有误');
            return false;
        }

        if (Recharge::store($recharge)) {
            $productBody = self::TYPE_SCORE ? $recharge->amount . '积分' : ($recharge->type == self::TYPE_VIP ? $recharge->amount . '个月VIP' : '');
            $payOpt = [
                'para_id' => strval(self::XF_PARA_ID),
                'app_id' => strval(self::XF_APPID2),
                'body' => $productBody,
                'total_fee' => strval($real_money),
                'order_no' => strval($recharge->id),
                'notify_url' => strval(self::XF_CALLBACK2),
                'returnurl' => 'http://www.dianzanyun.com',
                'sign' => strtolower(md5(self::XF_PARA_ID . self::XF_APPID2 . $recharge->id . $real_money . self::XF_APPKEY2)),
                'device_id' => "2",// 应用类型:1 是安卓 ,2 是 ios
                'mch_create_ip' => get_client_ip(),
                'mch_app_id' => 'http://www.dianzanyun.com',
                'mch_app_name' => 'dianzanyun',
                'attach' => strval($paytype),
                'child_para_id' => "1",
                'userIdentity' => "1557411137EXjzbj3Fem3IRKgZ",
            ];
            $curl = new Curl();
            $curl->post($payurl, $payOpt);
            if ($curl->error) {
                $this->error('xianfu curl failed', $payOpt);
                $this->return_error(402, '支付请求错误');
                return false;
            } else {
                $r = $curl->response;
                try {
                    $r = json_decode($r, true);
                    //                    $this->error('curl debug:', [$r, $payOpt]);
                    $returncode = isset($r['code']) ? $r['code'] : null;
                    if ($r['status'] != 0 || !$returncode) {
                        $this->error('curl return error', [(array)$recharge, (array)$r]);
                        $this->return_error(403, '支付返回错误');
                        return false;
                    }
                    $ret = [
                        'id' => $recharge->id,
                        'product_id' => $recharge->pid,
                        'content' => $recharge->type == self::TYPE_SCORE ? $recharge->amount . '积分' : ($recharge->type == self::TYPE_VIP ? $recharge->amount . '个月VIP' : ''),
                        'type' => $recharge->type,
                        'amount' => $recharge->amount,
                        'status' => $recharge->status,
                        'created_at' => $recharge->created_at ?: getCurrentTime(),
                        'vip_deadline' => $user->vip_deadline ?: '0',
                        'scores' => $user->remaining_scores ?: '0',
                        'pay' => [
                            'payurl' => isset($r['pay_url']) ? $r['pay_url'] : '',
                            'paytype' => $paytype,
                        ],
                    ];
                    $this->return_success($ret);
                } catch (Exception $e) {
                    $this->error('curl return error----', (array)$recharge);
                    $this->return_error();
                    return false;
                }
            }
        } else {
            $this->error('recharge failed', (array)$recharge);
            $this->return_error();
        }
        return true;
    }

    public function xianfutest()
    {
        $productBody = '首充大礼包';
        $rechargeId = 'a1';
        $real_money = 100;
        $payOpt = [
            'para_id' => strval(self::XF_PARA_ID),
            'app_id' => strval(self::XF_APPID2),
            'body' => $productBody,
            'total_fee' => strval($real_money),
            'order_no' => strval($rechargeId),
            'notify_url' => strval(self::XF_CALLBACK2),
            'returnurl' => 'http://www.dianzanyun.com',
            'pay_type' => "1",//1、微信支付 2、支付宝支付 3、 微信公众号 4、银联
            'sign' => strtolower(md5(self::XF_PARA_ID . self::XF_APPID2 . $rechargeId . $real_money . self::XF_APPKEY2)),
            'device_id' => "2",// 应用类型:1 是安卓 ,2 是 ios
            'mch_create_ip' => get_client_ip(),
            'mch_app_id' => 'http://www.dianzanyun.com',
            'mch_app_name' => 'dianzanyun',
            'attach' => strval(1),
            'child_para_id' => "1",
            'userIdentity' => "1557411137EXjzbj3Fem3IRKgZ",
        ];
        $payurl = 'http://pay.payfubao.com/sdk_transform/wx_wap_sdk';//Config::getXfPayUrl();
        $curl = new Curl();
        $curl->post($payurl, $payOpt);
        if ($curl->error) {
            $this->error('xianfu curl failed', $payOpt);
            $this->return_error(402, '支付请求错误');
            return false;
        } else {
            $r = $curl->response;
            $r = json_decode($r, true);
            $this->return_success($r);
        }
    }

    // 微信转账直接加积分
    public function direct()
    {
        $uid = $_POST['uid'];
        $money = $_POST['money'];
        $discount = Config::getWechatTransferPayDiscount();

        if (empty($uid) || empty($money)) {
            $this->return_error(400, '参数不完整');
            return;
        }

        if ($discount < 0) {
            $discount = 0;
        }

        $user = User::findOne('users', ' id = ? ', [ $uid ]);
        if (empty($user)) {
            $this->return_error(404, 'UID不存在');
            return;
        }

        // 事务处理
        Recharge::begin();
        try {
            // 一元1000积分，再加折扣
            $origin_scores = $money * 10;
            $attach_scores = intval($discount * $money * 10 / 100);
            $total_scores = $origin_scores + $attach_scores;

            // 订单记录
            $recharge = Recharge::dispense('recharges');
            $recharge->uid = $uid;
            $recharge->money = $money;
            $recharge->type = self::TYPE_SCORE;
            $recharge->amount = $origin_scores;
            $recharge->attach = $attach_scores;
            $recharge->status = self::PAY_STATUS_YES;
            Recharge::store($recharge);

            // 积分变动记录
            ScoreLog::recharge($uid, $total_scores);

            // 增加用户积分
            $user->total_scores += $total_scores;
            $user->remaining_scores += $total_scores;
            $user->updated_at = new \Datetime;
            User::store($user);

            Recharge::commit();

            $this->return_success();
        } catch (\Exception $e) {
            $this->error(__FUNCTION__ . ' failed rollbak', [ $e->getMessage(), $e->getTraceAsString() ]);
            Recharge::rollback();

            $this->return_error();
        }
    }

    /**
     * 微信充值
     */
    public function wechat()
    {
        $product_id = $_POST['product_id'];
        $money = $_POST['money'];
        $trade_type = isset($_POST['trade_type']) ? $_POST['trade_type'] : 'APP';
        $account = isset($_POST['account']) ? $_POST['account'] : 'dianzan';// dianzan：空间点赞大师，simi：私密相册

        if (!in_array($account, ['dianzan', 'simi'])) {
            $this->error('wechat rechare create failed: account wrong', [ $_POST ]);
            $this->return_error(400, '参数错误');
            return false;
        }
        if (!in_array($trade_type, ['APP', 'MWEB'])) {
            $this->error('wechat rechare create failed: trade_type wrong', [ $_POST ]);
            $this->return_error(400, '参数错误');
            return false;
        }

        if (!isset($_POST['product_id']) || empty($money)) {
            $this->error('rechare create failed: params wrong', [ $_POST ]);
            $this->return_error(400, '参数不完整');
            return false;
        }
        $user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);
        if (empty($user)) {
            $this->return_error();
            return false;
        }
        if ($product_id == 0) {// 首充
            $first_recharge_params = Config::getFirstRechargeParams();
            if (empty($first_recharge_params)) {
                $this->return_error(404, '无首充大礼包');
                return false;
            }
            if (!empty(Recharge::count('recharges', ' uid = ? AND status = ? ', [ $user->id, self::PAY_STATUS_YES ]))) {
                $this->return_error(403, '已经充值过，没有首充资格');
                return false;
            }
            $first_recharge_params = json_decode($first_recharge_params);
            $real_money = $first_recharge_params->money;

            $recharge = Recharge::dispense('recharges');
            $recharge->uid = $user->id;
            $recharge->money = $first_recharge_params->money;
            $recharge->type = self::TYPE_SCORE;
            $recharge->amount = $first_recharge_params->scores + $first_recharge_params->attach;
            $recharge->attach = $first_recharge_params->vip;
            $recharge->status = self::PAY_STATUS_NO;
            $recharge->platform = strtolower($this->platform) == 'android' ? self::PLATFORM_ANDROID : self::PLATFORM_IOS;
        } else {// 非首充
            $product = Product::findOne('products', ' id = ? AND status = ? ', [  $product_id, self::PRODUCT_STATUS_ONLINE ]);
            if (empty($product)) {
                $this->return_error(400, '充值产品不存在');
                return false;
            }
            // 验证充值金额是否合法
            $is_vip = $user->vip_deadline >= getCurrentTime();
            $discount = $is_vip ? Config::getVipRechargeDiscount() : 100;
            $real_money = ceil($product->money * $discount / 100);
            if ($real_money != $money) {
                $this->error('recharge money not valid', [ $user, $is_vip, $discount, $real_money, $_POST ]);
                $this->return_error(401, '充值金额不合法');
                exit;
            }

            $recharge = Recharge::dispense('recharges');
            $recharge->uid = $user->id;
            $recharge->pid = $product->id;
            $recharge->money = $real_money;
            $recharge->type = $product->type;
            $recharge->amount = $product->amount;
            $recharge->attach = $product->attach;
            $recharge->status = self::PAY_STATUS_NO;
            $recharge->platform = strtolower($this->platform) == 'android' ? self::PLATFORM_ANDROID : self::PLATFORM_IOS;
        }

        if (Recharge::store($recharge)) {
            if ($account == 'dianzan') {// 空间点赞大师支付
                $referer_url = 'http://www.kuzhikeji.com';
                $options = [
                    'app_id' => 'wx86768e03c307a935',
                    // payment
                    'payment' => [
                        'merchant_id'        => '1502090761',
                        'key'                => 'ce19cbd650152f3fa0e43b3a8e6f4687',
                        'notify_url'         => 'http://106.75.77.8/recharge/wx_callback',       // 你也可以在下单时单独设置来想覆盖它
                    ],
                ];

                $app = new Application($options);
                $attributes = [
                    'trade_type'       => $trade_type, // JSAPI，NATIVE，APP...
                    'body'             => '空间点赞大师充值',
                    'detail'           => '空间点赞大师充值',
                    'out_trade_no'     => $recharge->id,
                    'total_fee'        => $real_money, // 单位：分
                ];
                if ($trade_type == 'MWEB') {
                    $attributes['scene_info'] = json_encode([
                        'h5_info' => [
                            'type' => 'Wap',
                            'wap_url' => 'http://www.dianzanyun.com',
                            'wap_name' => '空间点赞大师充值'
                        ],
                    ]);
                }
            } else { // 私密相册支付
                $referer_url = 'http://www.dianzanyun.com';
                $options = [
                    'app_id' => 'wxaca09f0ad08d3962',
                    // payment
                    'payment' => [
                        'merchant_id'        => '1503094051',
                        'key'                => 'wL1tBb09rydskmpjsZTlfmULJ11pZ6CE',
                        'notify_url'         => 'http://106.75.77.8/recharge/wx_callback',       // 你也可以在下单时单独设置来想覆盖它
                    ],
                ];

                $app = new Application($options);
                $attributes = [
                    'trade_type'       => $trade_type, // JSAPI，NATIVE，APP...
                    'body'             => '私密相册精灵（）',
                    'detail'           => '私密相册精灵（）',
                    'out_trade_no'     => $recharge->id,
                    'total_fee'        => $real_money, // 单位：分
                ];
                if ($trade_type == 'MWEB') {
                    $attributes['scene_info'] = json_encode([
                        'h5_info' => [
                            'type' => 'Wap',
                            'wap_url' => 'http://www.dianzanyun.com',
                            'wap_name' => '私密相册精灵（）'
                        ],
                    ]);
                }
            }

            $order = new Order($attributes);
            $result = $app->payment->prepare($order);
            $prepayId = 0;
            if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
                $prepayId = $result->prepay_id;
            }
            if ($trade_type == 'MWEB') {
                $paymentConfig = [
                    'appid' => $result->appid,
                    'prepay_id' => $result->prepay_id,
                    'nonce_str' => $result->nonce_str,
                    'sign' => $result->sign,
                    'mweb_url' => $result->mweb_url,
                    'trade_type' => $result->trade_type,
                    'referer_url' => $referer_url,
                ];
                //$this->error('MWEB debug', $paymentConfig);
            } else {
                $paymentConfig = $app->payment->configForAppPayment($prepayId);
            }
            if (!empty($paymentConfig)) {
                $paymentConfig = array_map('strval', $paymentConfig);
            }

            $this->return_success(
                [
                    'id' => $recharge->id,
                    'product_id' => $recharge->pid,
                    'content' => $recharge->type == self::TYPE_SCORE ? $recharge->amount . '积分' : ($recharge->type == self::TYPE_VIP ? $recharge->amount . '个月VIP' : ''),
                    'type' => $recharge->type,
                    'amount' => $recharge->amount,
                    'status' => $recharge->status,
                    'created_at' => $recharge->created_at ?: getCurrentTime(),
                    'vip_deadline' => $user->vip_deadline ?: '0',
                    'scores' => $user->remaining_scores ?: '0',
                    'pay' => $paymentConfig,
                ]
            );
        } else {
            $this->error('recharge failed', (array)$recharge);
            $this->return_error();
        }
        return true;
    }

    /**
     * 充值订单更新
     */
    public function update()
    {
        $id = $_POST['id'];
        $bmob_order_id = $_POST['bmob_order_id'] ?: $_POST['71pay_order_id'];		// bmob或71pay支付订单id

        if (empty($id) || empty($bmob_order_id)) {
            $this->return_error(401, '参数不合法');
        } else {
            $recharge = Recharge::findOne('recharges', ' id = ? AND uid = ? ', [ $id, $this->token->uid ]);
            if (empty($recharge)) {
                $this->return_error(404, '该充值订单不存在');
            } else {
                if (empty($recharge->bmob_order_id)) {
                    $recharge->bmob_order_id = $bmob_order_id;

                    if (Recharge::store($recharge)) {
                        $this->return_success();
                    } else {
                        $this->return_error();
                    }
                } elseif ($recharge->bmob_order_id != $bmob_order_id) {
                    $this->error('update bmob_order_id invalid', [ $bmob_order_id, $recharge ]);
                    $this->return_error();
                } else {
                    $this->return_success();
                }
            }
        }
    }

    /**
     * 充值支付回调(bmob)--废弃
     */
    public function callback()
    {
        $trade_status = $_POST['trade_status'];
        $out_trade_no = $_POST['out_trade_no'];
        $trade_no = $_POST['trade_no'];

        $this->debug('pay callback params', [ $_POST, $trade_no, $out_trade_no, $trade_no ]);

        if (empty($trade_status) || empty($out_trade_no) || empty($trade_no)) {
            $this->error('pay callback unusual params disappearance', $_POST);
        } else {
            $recharge = Recharge::findOne('recharges', ' bmob_order_id = ? ', [ $out_trade_no ]);

            if (empty($recharge)) {
                $this->error('pay callback unusual recharge not exist', $_POST);
            } elseif ($recharge->status == self::PAY_STATUS_YES) {
                echo 'success';
            } else {
                // 调用bmob接口对订单进行二次验证
                $order_info = getBmobOrderInfo($recharge->bmob_order_id);
                if (empty($order_info) || $order_info->error) {
                    $this->error('pay callback unusual order id not exist', $_POST);
                } else {
                    if ($order_info->total_fee * 100 != $recharge->money) {
                        $this->error('pay callback unusual money wrong', $_POST);
                    } elseif ($order_info->trade_state != 'SUCCESS') {
                        $this->error('pay callback unusual state wrong', $_POST);
                    } else {
                        $user = User::findOne('users', ' id = ? ', [ $recharge->uid ]);
                        if (empty($user)) {
                            $this->error('recharge callback faild - user empty', (array)$recharge);
                        } else {
                            // 事务处理
                            Recharge::begin();
                            try {
                                // 更新用户积分及积分纪录
                                if ($recharge->type == self::TYPE_SCORE) {
                                    $user->total_scores += $recharge->amount;
                                    $user->remaining_scores += $recharge->amount;

                                    ScoreLog::recharge($user->id, $recharge->amount);
                                } elseif ($recharge->type == self::TYPE_VIP) {
                                    // 充值VIP时赠送积分(需判断当月是否已经因VIP赠送过积分)
                                    // 有两种情况送过积分, 根据积分变动记录统一判断即可
                                    // 1: 当月充过一次VIP
                                    // 2: 当月初由累计的充值VIP已赠送积分
                                    $this_month_start_timestamp = strtotime(date("Ymd", strtotime("first day of this month")));
                                    if (!ScoreLog::findOne('scorelogs', ' uid = ? AND type = ? AND created_at >= ? AND created_at <= ?', [ $user->id, self::TYPE_VIP_PRESENT, $this_month_start_timestamp, date('Y-m-d H:i:s', getCurrentTime()) ])) {
                                        $present_scores = Config::getVipMonthPresentScores();
                                        if (!empty($present_scores)) {
                                            $user->total_scores += $present_scores;
                                            $user->remaining_scores += $present_scores;

                                            ScoreLog::vipPresent($user->id, $present_scores);
                                        }
                                    }

                                    $add_vip_time = intval($recharge->amount * 30 * 24 * 60 * 60);
                                    $user->vip_deadline = $user->vip_deadline > getCurrentTime() ? $user->vip_deadline + $add_vip_time : getCurrentTime() + $add_vip_time;
                                }
                                $user->updated_at = new \DateTime;
                                User::store($user);

                                // 充值状态
                                $recharge->status = self::PAY_STATUS_YES;
                                // 支付方式
                                $recharge->pay_type = strtoupper($order_info->pay_type) == self::BMOB_PAY_TYPE_WECHAT ? self::PAY_TYPE_WECHAT : (strtoupper($order_info->pay_type) == self::BMOB_PAY_TYPE_ALIPAY ? self::PAY_TYPE_ALIPAY : self::PAY_TYPE_OTHER);
                                $recharge->updated_at = new \Datetime;
                                Recharge::store($recharge);

                                Recharge::commit();

                                echo 'success';
                            } catch (Exception $e) {
                                $this->error('recharge callback failed rollbak', [$e->getMessage()]);
                                Recharge::rollback();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 充值支付回调(话付通)--废弃
     */
    public function callback_71pay()
    {
        $app_id = $_POST['app_id'];
        $order_id = $_POST['order_id'];
        $pay_result = $_POST['pay_result'];

        $this->error('71pay callback params', [ $_POST ]);

        if (empty($app_id) || empty($order_id) || $pay_result === '' || ($app_id != self::HFT_PAY_APPID && $app_id != self::HFT_PAY_APPID_2)) {
            $this->error('71pay callback unusual params disappearance', $_POST);
        } else {
            $recharge = Recharge::findOne('recharges', ' id = ? ', [ $order_id ]);

            if (empty($recharge)) {
                $this->error('71pay callback unusual recharge not exist', $_POST);
            } elseif ($recharge->status == self::PAY_STATUS_YES) {
                echo 'ok';
            } else {
                // 调用查询接口对订单进行二次验证
                $md5_key = self::HFT_PAY_APPID_MD5KEY;
                if ($app_id == self::HFT_PAY_APPID_2) {
                    $md5_key = self::HFT_PAY_APPID_MD5KEY_2;
                }

                $order_info = get71PayOrderInfo($recharge->id, $app_id, $md5_key);
                $this->debug('order_info', [ $order_info ]);
                if (empty($order_info)) {
                    $this->error('71pay callback unusual order id not exist', $_POST);
                } else {
                    $order_info = json_decode($order_info);
                    if (intval($order_info->pay_amt * 100) != $recharge->money) {
                        $this->error('71pay callback unusual money wrong', [ $_POST, $order_info, $recharge ]);
                    } elseif ($order_info->status_code !== 0) {
                        $this->error('71pay callback unusual state wrong', $_POST);
                    } else {
                        $user = User::findOne('users', ' id = ? ', [ $recharge->uid ]);
                        if (empty($user)) {
                            $this->error('recharge callback faild - user empty', (array)$recharge);
                        } else {
                            // 事务处理
                            Recharge::begin();
                            try {
                                // 更新用户积分及积分纪录
                                if ($recharge->type == self::TYPE_SCORE) {
                                    $user->total_scores += $recharge->amount;
                                    $user->remaining_scores += $recharge->amount;

                                    ScoreLog::recharge($user->id, $recharge->amount);

                                    // 判断是否是首充, 赠送VIP
                                    if ($recharge->pid == 0 && $recharge->attach > 0) {
                                        $user->vip_deadline = getCurrentTime() + intval($recharge->attach * 30 * 24 * 60 * 60);
                                    }
                                } elseif ($recharge->type == self::TYPE_VIP) {
                                    // 充值VIP时赠送积分(需判断当月是否已经因VIP赠送过积分)
                                    // 有两种情况送过积分, 根据积分变动记录统一判断即可
                                    // 1: 当月充过一次VIP
                                    // 2: 当月初由累计的充值VIP已赠送积分
                                    $this_month_start_timestamp = strtotime(date("Ymd", strtotime("first day of this month")));
                                    if (!ScoreLog::findOne('scorelogs', ' uid = ? AND type = ? AND created_at >= ? AND created_at <= ?', [ $user->id, self::TYPE_VIP_PRESENT, $this_month_start_timestamp, date('Y-m-d H:i:s', getCurrentTime()) ])) {
                                        $present_scores = Config::getVipMonthPresentScores();
                                        if (!empty($present_scores)) {
                                            $user->total_scores += $present_scores;
                                            $user->remaining_scores += $present_scores;

                                            ScoreLog::vipPresent($user->id, $present_scores);
                                        }
                                    }

                                    $add_vip_time = intval($recharge->amount * 30 * 24 * 60 * 60);
                                    $user->vip_deadline = $user->vip_deadline > getCurrentTime() ? $user->vip_deadline + $add_vip_time : getCurrentTime() + $add_vip_time;
                                }
                                $user->updated_at = new \DateTime;
                                User::store($user);

                                // 充值状态
                                $recharge->status = self::PAY_STATUS_YES;
                                // 支付方式
                                $recharge->pay_type = self::PAY_TYPE_WECHAT;
                                $recharge->updated_at = new \Datetime;
                                Recharge::store($recharge);

                                Recharge::commit();

                                echo 'ok';
                            } catch (Exception $e) {
                                $this->error('recharge callback failed rollbak', [$e->getMessage()]);
                                Recharge::rollback();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 充值支付回调(优支付)--废弃
     */
    public function callback_you()
    {
        $order_id = $_GET['orderid'];
        $payed = $_GET['result'] == self::YOU_PAY_RESULT_SUCCESS;
        $fee = $_GET['fee'];
        $pay_type = $_GET['paytype'];
        $trade_time = $_GET['tradetime'];
        $cp_param = $_GET['cpparam'];
        $sign = $_GET['sign'];

        $this->error('you pay callback debug', $_GET);

        if (md5($order_id . $_GET['result'] . $fee . $trade_time . self::YOU_PAY_APPKEY) != $sign) {
            $this->error('you pay callback failed: sign failed', [$_GET, $order_id . $_GET['result'] . $fee . $trade_time . self::YOU_PAY_APPKEY]);
            echo 'failed';
        } elseif (!$payed) {
            $this->error('you pay callback failed: not payed', [$_GET]);
            echo 'failed';
        } elseif ($cp_param != self::PAY_CPPARAM['youpay']) {
            $this->error('you pay callback failed: cpparam wrong', [$_GET]);
            echo 'failed';
        } else {
            // 等待优支付数据库主从同步
            // sleep(2);

            // 查询订单状态
            // 暂时去掉 延时太高
            $t_sign = md5(self::YOU_PAY_APPID . $order_id . self::YOU_PAY_APPKEY);
            $info = getYouPayOrderInfo(self::YOU_PAY_APPID, $order_id, $t_sign);
            $info = json_encode(['result' => 1]);
            if (empty($info)) {
                $this->error('you pay callback failed: empty info', [$_GET]);
                echo 'failed';
            } else {
                $o_info = $info;
                $info = json_decode($info);
                if (isset($info->result) && $info->result == self::YOU_PAY_RESULT_SUCCESS) {
                    $recharge = Recharge::findOne('recharges', ' id = ? ', [ $order_id ]);

                    if (empty($recharge)) {
                        $this->error('you pay callback failed: recharge not exist', [$_GET]);
                        echo 'failed';
                    } elseif ($recharge->status == self::PAY_STATUS_YES) {
                        echo 'ok';
                    } elseif ($recharge->money != $fee) {
                        $this->error('you pay callback failed: wrong fee', [$_GET, $recharge]);
                        echo 'failed';
                    } else {
                        $user = User::findOne('users', ' id = ? ', [ $recharge->uid ]);
                        if (empty($user)) {
                            $this->error('you pay callback failed: empty user', [$_GET, $recharge]);
                        } else {
                            // 事务处理
                            Recharge::begin();
                            try {
                                // 更新用户积分及积分纪录
                                if ($recharge->type == self::TYPE_SCORE) {
                                    $user->total_scores += $recharge->amount;
                                    $user->remaining_scores += $recharge->amount;

                                    ScoreLog::recharge($user->id, $recharge->amount);

                                    // 判断是否是首充, 赠送VIP
                                    if ($recharge->pid == 0 && $recharge->attach > 0) {
                                        $user->vip_deadline = getCurrentTime() + intval($recharge->attach * 30 * 24 * 60 * 60);
                                    }
                                } elseif ($recharge->type == self::TYPE_VIP) {
                                    // 充值VIP时赠送积分(需判断当月是否已经因VIP赠送过积分)
                                    // 有两种情况送过积分, 根据积分变动记录统一判断即可
                                    // 1: 当月充过一次VIP
                                    // 2: 当月初由累计的充值VIP已赠送积分
                                    $this_month_start_timestamp = strtotime(date("Ymd", strtotime("first day of this month")));
                                    if (!ScoreLog::findOne('scorelogs', ' uid = ? AND type = ? AND created_at >= ? AND created_at <= ?', [ $user->id, self::TYPE_VIP_PRESENT, $this_month_start_timestamp, date('Y-m-d H:i:s', getCurrentTime()) ])) {
                                        $present_scores = Config::getVipMonthPresentScores();
                                        if (!empty($present_scores)) {
                                            $user->total_scores += $present_scores;
                                            $user->remaining_scores += $present_scores;

                                            ScoreLog::vipPresent($user->id, $present_scores);
                                        }
                                    }

                                    $add_vip_time = intval($recharge->amount * 30 * 24 * 60 * 60);
                                    $user->vip_deadline = $user->vip_deadline > getCurrentTime() ? $user->vip_deadline + $add_vip_time : getCurrentTime() + $add_vip_time;
                                }
                                $user->updated_at = new \DateTime;
                                User::store($user);

                                // 充值状态
                                $recharge->status = self::PAY_STATUS_YES;
                                // 支付方式
                                $recharge->pay_type = $pay_type == self::YOU_PAY_PAYTYPE_WECHAT ? self::PAY_TYPE_WECHAT : ( $pay_type == self::YOU_PAY_PAYTYPE_ALIPAY ? self::PAY_TYPE_ALIPAY : self::PAY_TYPE_OTHER );
                                $recharge->updated_at = new \Datetime;

                                // 使用该字段区分两个优支付渠道
                                $recharge->bmob_order_id = self::YOU_PAY_APPKEY;
                                Recharge::store($recharge);

                                Recharge::commit();

                                echo 'ok';
                            } catch (Exception $e) {
                                $this->error('recharge callback failed rollbak', [$e->getMessage()]);
                                Recharge::rollback();
                            }
                        }
                    }
                } else {
                    $this->error('you pay callback failed: wrong info', [ $_GET, $info, $o_info ]);
                    echo 'failed';
                }
            }
        }
    }

    /**
     * 充值支付回调2(优支付)--废弃
     */
    public function callback_you2()
    {
        $order_id = $_GET['orderid'];
        $payed = $_GET['result'] == self::YOU_PAY_RESULT_SUCCESS;
        $fee = $_GET['fee'];
        $pay_type = $_GET['paytype'];
        $trade_time = $_GET['tradetime'];
        $cp_param = $_GET['cpparam'];
        $sign = $_GET['sign'];

        $this->error('you pay callback debug', $_GET);

        if (md5($order_id . $_GET['result'] . $fee . $trade_time . self::YOU_PAY_APPKEY2) != $sign) {
            $this->error('you pay callback failed: sign failed', [$_GET, $order_id . $_GET['result'] . $fee . $trade_time . self::YOU_PAY_APPKEY2]);
            echo 'failed';
        } elseif (!$payed) {
            $this->error('you pay callback failed: not payed', [$_GET]);
            echo 'failed';
        } elseif ($cp_param != self::PAY_CPPARAM['youpay']) {
            $this->error('you pay callback failed: cpparam wrong', [$_GET]);
            echo 'failed';
        } else {
            // 等待优支付数据库主从同步
            // sleep(2);

            // 查询订单状态
            // 暂时去掉 延时太高
            $t_sign = md5(self::YOU_PAY_APPID2 . $order_id . self::YOU_PAY_APPKEY2);
            $info = getYouPayOrderInfo(self::YOU_PAY_APPID2, $order_id, $t_sign);
            $info = json_encode(['result' => 1]);
            if (empty($info)) {
                $this->error('you pay callback failed: empty info', [$_GET]);
                echo 'failed';
            } else {
                $o_info = $info;
                $info = json_decode($info);
                if (isset($info->result) && $info->result == self::YOU_PAY_RESULT_SUCCESS) {
                    $recharge = Recharge::findOne('recharges', ' id = ? ', [ $order_id ]);

                    if (empty($recharge)) {
                        $this->error('you pay callback failed: recharge not exist', [$_GET]);
                        echo 'failed';
                    } elseif ($recharge->status == self::PAY_STATUS_YES) {
                        echo 'ok';
                    } elseif ($recharge->money != $fee) {
                        $this->error('you pay callback failed: wrong fee', [$_GET, $recharge]);
                        echo 'failed';
                    } else {
                        $user = User::findOne('users', ' id = ? ', [ $recharge->uid ]);
                        if (empty($user)) {
                            $this->error('you pay callback failed: empty user', [$_GET, $recharge]);
                        } else {
                            // 事务处理
                            Recharge::begin();
                            try {
                                // 更新用户积分及积分纪录
                                if ($recharge->type == self::TYPE_SCORE) {
                                    $user->total_scores += $recharge->amount;
                                    $user->remaining_scores += $recharge->amount;

                                    ScoreLog::recharge($user->id, $recharge->amount);

                                    // 判断是否是首充, 赠送VIP
                                    if ($recharge->pid == 0 && $recharge->attach > 0) {
                                        $user->vip_deadline = getCurrentTime() + intval($recharge->attach * 30 * 24 * 60 * 60);
                                    }
                                } elseif ($recharge->type == self::TYPE_VIP) {
                                    // 充值VIP时赠送积分(需判断当月是否已经因VIP赠送过积分)
                                    // 有两种情况送过积分, 根据积分变动记录统一判断即可
                                    // 1: 当月充过一次VIP
                                    // 2: 当月初由累计的充值VIP已赠送积分
                                    $this_month_start_timestamp = strtotime(date("Ymd", strtotime("first day of this month")));
                                    if (!ScoreLog::findOne('scorelogs', ' uid = ? AND type = ? AND created_at >= ? AND created_at <= ?', [ $user->id, self::TYPE_VIP_PRESENT, $this_month_start_timestamp, date('Y-m-d H:i:s', getCurrentTime()) ])) {
                                        $present_scores = Config::getVipMonthPresentScores();
                                        if (!empty($present_scores)) {
                                            $user->total_scores += $present_scores;
                                            $user->remaining_scores += $present_scores;

                                            ScoreLog::vipPresent($user->id, $present_scores);
                                        }
                                    }

                                    $add_vip_time = intval($recharge->amount * 30 * 24 * 60 * 60);
                                    $user->vip_deadline = $user->vip_deadline > getCurrentTime() ? $user->vip_deadline + $add_vip_time : getCurrentTime() + $add_vip_time;
                                }
                                $user->updated_at = new \DateTime;
                                User::store($user);

                                // 充值状态
                                $recharge->status = self::PAY_STATUS_YES;
                                // 支付方式
                                $recharge->pay_type = $pay_type == self::YOU_PAY_PAYTYPE_WECHAT ? self::PAY_TYPE_WECHAT : ( $pay_type == self::YOU_PAY_PAYTYPE_ALIPAY ? self::PAY_TYPE_ALIPAY : self::PAY_TYPE_OTHER );
                                $recharge->updated_at = new \Datetime;

                                // 使用该字段区分两个优支付渠道
                                $recharge->bmob_order_id = self::YOU_PAY_APPKEY2;
                                Recharge::store($recharge);

                                Recharge::commit();

                                echo 'ok';
                            } catch (Exception $e) {
                                $this->error('recharge callback failed rollbak', [$e->getMessage()]);
                                Recharge::rollback();
                            }
                        }
                    }
                } else {
                    $this->error('you pay callback failed: wrong info', [ $_GET, $info, $o_info ]);
                    echo 'failed';
                }
            }
        }
    }

    /**
     * 充值支付回调3(优支付)--废弃
     */
    public function callback_you3()
    {
        $order_id = $_GET['orderid'];
        $payed = $_GET['result'] == self::YOU_PAY_RESULT_SUCCESS;
        $fee = $_GET['fee'];
        $pay_type = $_GET['paytype'];
        $trade_time = $_GET['tradetime'];
        $cp_param = $_GET['cpparam'];
        $sign = $_GET['sign'];

        $this->error('you pay callback debug', $_GET);

        if (md5($order_id . $_GET['result'] . $fee . $trade_time . self::YOU_PAY_APPKEY3) != $sign) {
            $this->error('you pay callback failed: sign failed', [$_GET, $order_id . $_GET['result'] . $fee . $trade_time . self::YOU_PAY_APPKEY3]);
            echo 'failed';
        } elseif (!$payed) {
            $this->error('you pay callback failed: not payed', [$_GET]);
            echo 'failed';
        } elseif ($cp_param != self::PAY_CPPARAM['youpay']) {
            $this->error('you pay callback failed: cpparam wrong', [$_GET]);
            echo 'failed';
        } else {
            // 等待优支付数据库主从同步
            // sleep(2);

            // 查询订单状态
            // 暂时去掉 延时太高
            $t_sign = md5(self::YOU_PAY_APPID3 . $order_id . self::YOU_PAY_APPKEY3);
            $info = getYouPayOrderInfo(self::YOU_PAY_APPID3, $order_id, $t_sign);
            $info = json_encode(['result' => 1]);
            if (empty($info)) {
                $this->error('you pay callback failed: empty info', [$_GET]);
                echo 'failed';
            } else {
                $o_info = $info;
                $info = json_decode($info);
                if (isset($info->result) && $info->result == self::YOU_PAY_RESULT_SUCCESS) {
                    $recharge = Recharge::findOne('recharges', ' id = ? ', [ $order_id ]);

                    if (empty($recharge)) {
                        $this->error('you pay callback failed: recharge not exist', [$_GET]);
                        echo 'failed';
                    } elseif ($recharge->status == self::PAY_STATUS_YES) {
                        echo 'ok';
                    } elseif ($recharge->money != $fee) {
                        $this->error('you pay callback failed: wrong fee', [$_GET, $recharge]);
                        echo 'failed';
                    } else {
                        $user = User::findOne('users', ' id = ? ', [ $recharge->uid ]);
                        if (empty($user)) {
                            $this->error('you pay callback failed: empty user', [$_GET, $recharge]);
                        } else {
                            // 事务处理
                            Recharge::begin();
                            try {
                                // 更新用户积分及积分纪录
                                if ($recharge->type == self::TYPE_SCORE) {
                                    $user->total_scores += $recharge->amount;
                                    $user->remaining_scores += $recharge->amount;

                                    ScoreLog::recharge($user->id, $recharge->amount);

                                    // 判断是否是首充, 赠送VIP
                                    if ($recharge->pid == 0 && $recharge->attach > 0) {
                                        $user->vip_deadline = getCurrentTime() + intval($recharge->attach * 30 * 24 * 60 * 60);
                                    }
                                } elseif ($recharge->type == self::TYPE_VIP) {
                                    // 充值VIP时赠送积分(需判断当月是否已经因VIP赠送过积分)
                                    // 有两种情况送过积分, 根据积分变动记录统一判断即可
                                    // 1: 当月充过一次VIP
                                    // 2: 当月初由累计的充值VIP已赠送积分
                                    $this_month_start_timestamp = strtotime(date("Ymd", strtotime("first day of this month")));
                                    if (!ScoreLog::findOne('scorelogs', ' uid = ? AND type = ? AND created_at >= ? AND created_at <= ?', [ $user->id, self::TYPE_VIP_PRESENT, $this_month_start_timestamp, date('Y-m-d H:i:s', getCurrentTime()) ])) {
                                        $present_scores = Config::getVipMonthPresentScores();
                                        if (!empty($present_scores)) {
                                            $user->total_scores += $present_scores;
                                            $user->remaining_scores += $present_scores;

                                            ScoreLog::vipPresent($user->id, $present_scores);
                                        }
                                    }

                                    $add_vip_time = intval($recharge->amount * 30 * 24 * 60 * 60);
                                    $user->vip_deadline = $user->vip_deadline > getCurrentTime() ? $user->vip_deadline + $add_vip_time : getCurrentTime() + $add_vip_time;
                                }
                                $user->updated_at = new \DateTime;
                                User::store($user);

                                // 充值状态
                                $recharge->status = self::PAY_STATUS_YES;
                                // 支付方式
                                $recharge->pay_type = $pay_type == self::YOU_PAY_PAYTYPE_WECHAT ? self::PAY_TYPE_WECHAT : ( $pay_type == self::YOU_PAY_PAYTYPE_ALIPAY ? self::PAY_TYPE_ALIPAY : self::PAY_TYPE_OTHER );
                                $recharge->updated_at = new \Datetime;

                                // 使用该字段区分两个优支付渠道
                                $recharge->bmob_order_id = self::YOU_PAY_APPKEY3;
                                Recharge::store($recharge);

                                Recharge::commit();

                                echo 'ok';
                            } catch (Exception $e) {
                                $this->error('recharge callback failed rollbak', [$e->getMessage()]);
                                Recharge::rollback();
                            }
                        }
                    }
                } else {
                    $this->error('you pay callback failed: wrong info', [ $_GET, $info, $o_info ]);
                    echo 'failed';
                }
            }
        }
    }

    /**
     * 充值支付回调(完美点卡支付)--废弃
     */
    public function callback_wm()
    {
        $state = $_GET['state'];
        $customerid = $_GET['customerid'];
        $sd51no = $_GET['sd51no'];
        $sdcustomno = $_GET['sdcustomno'];
        $ordermoney = $_GET['ordermoney'];
        $cardno = $_GET['cardno'];
        $mark = $_GET['mark'];
        $sign = $_GET['sign'];
        $resign = $_GET['resign'];
        $des = $_GET['des'];

        $this->error(__FUNCTION__ . ' pay callback debug', $_GET);

        if ($state != self::WM_PAY_SUCCESS || $customerid != self::WM_APPID) {
            echo '<result>0</result>';
            $this->error(__FUNCTION__ . ' state != 1 || customerid wrong || mark wrong');
            return;
        }

        if (strtoupper(md5('customerid=' . $customerid .'&sd51no=' . $sd51no . '&sdcustomno=' . $sdcustomno . '&mark=' . $mark . '&key=' . self::WM_APPSECRET)) != $sign || strtoupper(md5('sign=' . $sign . '&customerid=' . $customerid . '&ordermoney=' . $ordermoney . '&sd51no=' . $sd51no . '&state=' . $state . '&key=' . self::WM_APPSECRET)) != $resign) {
            echo '<result>0</result>';
            $this->error(__FUNCTION__ . ' sign/resign failed');
            return;
        }

        $recharge = Recharge::findOne('recharges', ' id = ? ', [ $sdcustomno ]);

        if (empty($recharge)) {
            $this->error('wm pay callback failed: recharge not exist', [$_GET]);
            echo '<result>0</result>';
            return;
        }
        if ($recharge->status == self::PAY_STATUS_YES) {
            echo '<result>1</result>';
            return;
        }
        if ($recharge->money != $ordermoney * 100) {
            $this->error('wm pay callback failed: wrong ordermoney', [$_GET, $recharge]);
            echo '<result>0</result>';
        } else {
            $user = User::findOne('users', ' id = ? ', [ $recharge->uid ]);
            if (empty($user)) {
                $this->error('wm pay callback failed: empty user', [$_GET, $recharge]);
            } else {
                // 事务处理
                Recharge::begin();
                try {
                    // 更新用户积分及积分纪录
                    if ($recharge->type == self::TYPE_SCORE) {
                        $user->total_scores += $recharge->amount;
                        $user->remaining_scores += $recharge->amount;

                        ScoreLog::recharge($user->id, $recharge->amount);

                        // 判断是否是首充, 赠送VIP
                        if ($recharge->pid == 0 && $recharge->attach > 0) {
                            $user->vip_deadline = getCurrentTime() + intval($recharge->attach * 30 * 24 * 60 * 60);
                        }
                    } elseif ($recharge->type == self::TYPE_VIP) {
                        // 充值VIP时赠送积分(需判断当月是否已经因VIP赠送过积分)
                        // 有两种情况送过积分, 根据积分变动记录统一判断即可
                        // 1: 当月充过一次VIP
                        // 2: 当月初由累计的充值VIP已赠送积分
                        $this_month_start_timestamp = strtotime(date("Ymd", strtotime("first day of this month")));
                        if (!ScoreLog::findOne('scorelogs', ' uid = ? AND type = ? AND created_at >= ? AND created_at <= ?', [ $user->id, self::TYPE_VIP_PRESENT, $this_month_start_timestamp, date('Y-m-d H:i:s', getCurrentTime()) ])) {
                            $present_scores = Config::getVipMonthPresentScores();
                            if (!empty($present_scores)) {
                                $user->total_scores += $present_scores;
                                $user->remaining_scores += $present_scores;

                                ScoreLog::vipPresent($user->id, $present_scores);
                            }
                        }

                        $add_vip_time = intval($recharge->amount * 30 * 24 * 60 * 60);
                        $user->vip_deadline = $user->vip_deadline > getCurrentTime() ? $user->vip_deadline + $add_vip_time : getCurrentTime() + $add_vip_time;
                    }
                    $user->updated_at = new \DateTime;
                    User::store($user);

                    // 充值状态
                    $recharge->status = self::PAY_STATUS_YES;
                    // 支付方式
                    $recharge->pay_type = $pay_type == self::YOU_PAY_PAYTYPE_WECHAT ? self::PAY_TYPE_WECHAT : ( $pay_type == self::YOU_PAY_PAYTYPE_ALIPAY ? self::PAY_TYPE_ALIPAY : self::PAY_TYPE_OTHER );
                    $recharge->updated_at = new \Datetime;

                    // 使用该字段区分两个优支付渠道
                    $recharge->bmob_order_id = self::WM_APPID;
                    Recharge::store($recharge);

                    Recharge::commit();

                    echo '<result>1</result>';
                } catch (Exception $e) {
                    $this->error('recharge callback failed rollbak', [$e->getMessage()]);
                    Recharge::rollback();

                    echo '<result>0</result>';
                }
            }
        }
    }

    /**
     * 微信支付回调
     */
    public function callback_wx()
    {
        $options = [
            'app_id' => 'wx86768e03c307a935',
            // payment
            'payment' => [
                'merchant_id'        => '1502090761',
                'key'                => 'ce19cbd650152f3fa0e43b3a8e6f4687',
                'notify_url'         => 'http://106.75.77.8/recharge/wx_callback',       // 你也可以在下单时单独设置来想覆盖它
            ],
        ];
        $app = new Application($options);

        $response = $app->payment->handleNotify(function ($notify, $successful) {
            $this->error('callback_wx pay callback debug', [(array)$notify]);
            $recharge = Recharge::findOne('recharges', ' id = ? ', [ $notify->out_trade_no ]);
            if (empty($recharge)) {
                $this->error(__FUNCTION__ . ' rechage not exists', [(array)$notify]);
                return 'Order not exist';
            }

            if ($recharge->status == self::PAY_STATUS_YES) {
                return true;
            }

            if (!$successful) {
                $this->error(__FUNCTION__ . ' pay failed', [(array)$notify]);
                return true;
            }

            $user = User::findOne('users', ' id = ? ', [ $recharge->uid ]);
            if (empty($user)) {
                $this->error(__FUNCTION__ . 'empty user', [(array)$notify, $recharge]);
                return true;
            }

            // 事务处理
            Recharge::begin();
            try {
                // 更新用户积分及积分纪录
                if ($recharge->type == self::TYPE_SCORE) {
                    $user->total_scores += $recharge->amount;
                    $user->remaining_scores += $recharge->amount;

                    ScoreLog::recharge($user->id, $recharge->amount);

                    // 判断是否是首充, 赠送VIP
                    if ($recharge->pid == 0 && $recharge->attach > 0) {
                        $user->vip_deadline = getCurrentTime() + intval($recharge->attach * 30 * 24 * 60 * 60);
                    }
                } elseif ($recharge->type == self::TYPE_VIP) {
                    // 充值VIP时赠送积分(需判断当月是否已经因VIP赠送过积分)
                    // 有两种情况送过积分, 根据积分变动记录统一判断即可
                    // 1: 当月充过一次VIP
                    // 2: 当月初由累计的充值VIP已赠送积分
                    $this_month_start_timestamp = strtotime(date("Ymd", strtotime("first day of this month")));
                    if (!ScoreLog::findOne('scorelogs', ' uid = ? AND type = ? AND created_at >= ? AND created_at <= ?', [ $user->id, self::TYPE_VIP_PRESENT, $this_month_start_timestamp, date('Y-m-d H:i:s', getCurrentTime()) ])) {
                        $present_scores = Config::getVipMonthPresentScores();
                        if (!empty($present_scores)) {
                            $user->total_scores += $present_scores;
                            $user->remaining_scores += $present_scores;

                            ScoreLog::vipPresent($user->id, $present_scores);
                        }
                    }

                    $add_vip_time = intval($recharge->amount * 30 * 24 * 60 * 60);
                    $user->vip_deadline = $user->vip_deadline > getCurrentTime() ? $user->vip_deadline + $add_vip_time : getCurrentTime() + $add_vip_time;
                }
                $user->updated_at = new \DateTime;
                User::store($user);

                // 充值状态
                $recharge->status = self::PAY_STATUS_YES;
                // 支付方式
                $recharge->pay_type = self::PAY_TYPE_WECHAT;
                $recharge->updated_at = new \Datetime;

                // 使用该字段区分两个优支付渠道
                $recharge->bmob_order_id = self::WM_APPID;
                Recharge::store($recharge);

                Recharge::commit();

                return true;
            } catch (Exception $e) {
                $this->error('recharge callback failed rollbak', [$e->getMessage()]);
                Recharge::rollback();

                return;
            }

        })->send();
    }


    /**
     * 先付支付回调
     */
    public function callback_xf_bak()
    {
        $order_id = $_GET['orderid'];
        $status = $_GET['result'];
        $money = $_GET['fee'];
        $pay_type = $_GET['paytype'];
        $time = $_GET['tradetime'];
        //        $cpparam = $_GET['cpparam'];
        $sign = $_GET['sign'];

        $this->error(__FUNCTION__ . ' pay callback debug', $_GET);

        if ($status != 1) {
            $this->error(__FUNCTION__ . ' status != 1');
            return;
        }

        if (md5($order_id . $status . $money . $time . self::XF_APPKEY) != $sign) {
            $this->error(__FUNCTION__ . ' sign failed');
            return;
        }

        $recharge = Recharge::findOne('recharges', ' id = ? ', [ $order_id ]);

        if (empty($recharge)) {
            $this->error('xf pay callback failed: recharge not exist', $_GET);
            echo 'failed';
            return;
        }
        if ($recharge->status == self::PAY_STATUS_YES) {
            echo 'success';
            return;
        }
        if ($recharge->money != $money) {
            $this->error('xf pay callback failed: wrong money', [$_GET, $recharge]);
            echo 'failed';
            return;
        } else {
            $user = User::findOne('users', ' id = ? ', [ $recharge->uid ]);
            if (empty($user)) {
                $this->error('xf pay callback failed: empty user', [$_GET, $recharge]);
                return;
            } else {
                // 事务处理
                Recharge::begin();
                try {
                    // 更新用户积分及积分纪录
                    if ($recharge->type == self::TYPE_SCORE) {
                        $user->total_scores += $recharge->amount;
                        $user->remaining_scores += $recharge->amount;

                        ScoreLog::recharge($user->id, $recharge->amount);

                        // 判断是否是首充, 赠送VIP
                        if ($recharge->pid == 0 && $recharge->attach > 0) {
                            $user->vip_deadline = getCurrentTime() + intval($recharge->attach * 30 * 24 * 60 * 60);
                        }
                    } elseif ($recharge->type == self::TYPE_VIP) {
                        // 充值VIP时赠送积分(需判断当月是否已经因VIP赠送过积分)
                        // 有两种情况送过积分, 根据积分变动记录统一判断即可
                        // 1: 当月充过一次VIP
                        // 2: 当月初由累计的充值VIP已赠送积分
                        $this_month_start_timestamp = strtotime(date("Ymd", strtotime("first day of this month")));
                        if (!ScoreLog::findOne('scorelogs', ' uid = ? AND type = ? AND created_at >= ? AND created_at <= ?', [ $user->id, self::TYPE_VIP_PRESENT, $this_month_start_timestamp, date('Y-m-d H:i:s', getCurrentTime()) ])) {
                            $present_scores = Config::getVipMonthPresentScores();
                            if (!empty($present_scores)) {
                                $user->total_scores += $present_scores;
                                $user->remaining_scores += $present_scores;

                                ScoreLog::vipPresent($user->id, $present_scores);
                            }
                        }

                        $add_vip_time = intval($recharge->amount * 30 * 24 * 60 * 60);
                        $user->vip_deadline = $user->vip_deadline > getCurrentTime() ? $user->vip_deadline + $add_vip_time : getCurrentTime() + $add_vip_time;
                    }
                    $user->updated_at = new \DateTime;
                    User::store($user);

                    // 充值状态
                    $recharge->status = self::PAY_STATUS_YES;
                    // 支付方式
                    $recharge->pay_type = $this->getXfPaytype($pay_type);
                    $recharge->updated_at = new \Datetime;

                    Recharge::store($recharge);

                    Recharge::commit();

                    echo 'OK';
                } catch (Exception $e) {
                    $this->error('recharge callback failed rollbak', [$e->getMessage()]);
                    Recharge::rollback();

                    echo 'failed';
                }
            }
        }
    }

    /**
     * 先付支付回调
     */
    public function callback_xf()
    {
        $order_id = $_GET['orderno'];
        $money = $_GET['fee'];
        $sign = $_GET['sign'];
//        $app_id = $_GET['app_id'];
        $pay_type = $_GET['attach'];
//        $child_para_id = $_GET['child_para_id'];
//        $wxno = $_GET['wxno'];

        $this->error(__FUNCTION__ . ' pay callback debug', $_GET);

        if (strtolower(md5($order_id . $money . self::XF_APPKEY2)) != $sign) {
            $this->error(__FUNCTION__ . ' sign failed');
            return;
        }

        $recharge = Recharge::findOne('recharges', ' id = ? ', [ $order_id ]);

        if (empty($recharge)) {
            $this->error('xf pay callback failed: recharge not exist', $_GET);
            echo 'failed';
            return;
        }
        if ($recharge->status == self::PAY_STATUS_YES) {
            echo 'success';
            return;
        }
        if ($recharge->money != $money) {
            $this->error('xf pay callback failed: wrong money', [$_GET, $recharge]);
            echo 'failed';
            return;
        } else {
            $user = User::findOne('users', ' id = ? ', [ $recharge->uid ]);
            if (empty($user)) {
                $this->error('xf pay callback failed: empty user', [$_GET, $recharge]);
                return;
            } else {
                // 事务处理
                Recharge::begin();
                try {
                    // 更新用户积分及积分纪录
                    if ($recharge->type == self::TYPE_SCORE) {
                        $user->total_scores += $recharge->amount;
                        $user->remaining_scores += $recharge->amount;

                        ScoreLog::recharge($user->id, $recharge->amount);

                        // 判断是否是首充, 赠送VIP
                        if ($recharge->pid == 0 && $recharge->attach > 0) {
                            $user->vip_deadline = getCurrentTime() + intval($recharge->attach * 30 * 24 * 60 * 60);
                        }
                    } elseif ($recharge->type == self::TYPE_VIP) {
                        // 充值VIP时赠送积分(需判断当月是否已经因VIP赠送过积分)
                        // 有两种情况送过积分, 根据积分变动记录统一判断即可
                        // 1: 当月充过一次VIP
                        // 2: 当月初由累计的充值VIP已赠送积分
                        $this_month_start_timestamp = strtotime(date("Ymd", strtotime("first day of this month")));
                        if (!ScoreLog::findOne('scorelogs', ' uid = ? AND type = ? AND created_at >= ? AND created_at <= ?', [ $user->id, self::TYPE_VIP_PRESENT, $this_month_start_timestamp, date('Y-m-d H:i:s', getCurrentTime()) ])) {
                            $present_scores = Config::getVipMonthPresentScores();
                            if (!empty($present_scores)) {
                                $user->total_scores += $present_scores;
                                $user->remaining_scores += $present_scores;

                                ScoreLog::vipPresent($user->id, $present_scores);
                            }
                        }

                        $add_vip_time = intval($recharge->amount * 30 * 24 * 60 * 60);
                        $user->vip_deadline = $user->vip_deadline > getCurrentTime() ? $user->vip_deadline + $add_vip_time : getCurrentTime() + $add_vip_time;
                    }
                    $user->updated_at = new \DateTime;
                    User::store($user);

                    // 充值状态
                    $recharge->status = self::PAY_STATUS_YES;
                    // 支付方式
                    $recharge->pay_type = $this->getXfPaytype($pay_type);
                    $recharge->updated_at = new \Datetime;

                    Recharge::store($recharge);

                    Recharge::commit();

                    echo 'ok';
                } catch (Exception $e) {
                    $this->error('recharge callback failed rollbak', [$e->getMessage()]);
                    Recharge::rollback();

                    echo 'fail';
                }
            }
        }
    }

    /**
     * 充值支付回调(钱进支付)--废弃
     */
    public function callback_qj()
    {
        $order_id = $_POST['order_id'];
        $orderNo = $_POST['orderNo'];
        $money = $_POST['money'];
        $mch = $_POST['mch'];
        $pay_type = $_POST['pay_type'];
        $transactionId = $_POST['transactionId'];
        $status = $_POST['status'];
        $sign = $_POST['sign'];
        $time = $_POST['time'];

        $this->error(__FUNCTION__ . ' pay callback debug', $_POST, $_GET);

        if ($status != 1 || $mch != self::QJ_APPID) {
            $this->error(__FUNCTION__ . ' status != 1 || mch wrong');
            return;
        }

        if (md5($order_id . $orderNo . $money . $mch . $pay_type . $time . md5(self::QJ_APPSECRET)) != $sign) {
            $this->error(__FUNCTION__ . ' sign failed');
            return;
        }

        $recharge = Recharge::findOne('recharges', ' id = ? ', [ $order_id ]);

        if (empty($recharge)) {
            $this->error('qj pay callback failed: recharge not exist', [$_POST]);
            echo 'failed';
            return;
        }
        if ($recharge->status == self::PAY_STATUS_YES) {
            echo 'success';
            return;
        }
        if ($recharge->money != $money) {
            $this->error('qj pay callback failed: wrong money', [$_POST, $recharge]);
            echo 'failed';
        } else {
            $user = User::findOne('users', ' id = ? ', [ $recharge->uid ]);
            if (empty($user)) {
                $this->error('qj pay callback failed: empty user', [$_POST, $recharge]);
            } else {
                // 事务处理
                Recharge::begin();
                try {
                    // 更新用户积分及积分纪录
                    if ($recharge->type == self::TYPE_SCORE) {
                        $user->total_scores += $recharge->amount;
                        $user->remaining_scores += $recharge->amount;

                        ScoreLog::recharge($user->id, $recharge->amount);

                        // 判断是否是首充, 赠送VIP
                        if ($recharge->pid == 0 && $recharge->attach > 0) {
                            $user->vip_deadline = getCurrentTime() + intval($recharge->attach * 30 * 24 * 60 * 60);
                        }
                    } elseif ($recharge->type == self::TYPE_VIP) {
                        // 充值VIP时赠送积分(需判断当月是否已经因VIP赠送过积分)
                        // 有两种情况送过积分, 根据积分变动记录统一判断即可
                        // 1: 当月充过一次VIP
                        // 2: 当月初由累计的充值VIP已赠送积分
                        $this_month_start_timestamp = strtotime(date("Ymd", strtotime("first day of this month")));
                        if (!ScoreLog::findOne('scorelogs', ' uid = ? AND type = ? AND created_at >= ? AND created_at <= ?', [ $user->id, self::TYPE_VIP_PRESENT, $this_month_start_timestamp, date('Y-m-d H:i:s', getCurrentTime()) ])) {
                            $present_scores = Config::getVipMonthPresentScores();
                            if (!empty($present_scores)) {
                                $user->total_scores += $present_scores;
                                $user->remaining_scores += $present_scores;

                                ScoreLog::vipPresent($user->id, $present_scores);
                            }
                        }

                        $add_vip_time = intval($recharge->amount * 30 * 24 * 60 * 60);
                        $user->vip_deadline = $user->vip_deadline > getCurrentTime() ? $user->vip_deadline + $add_vip_time : getCurrentTime() + $add_vip_time;
                    }
                    $user->updated_at = new \DateTime;
                    User::store($user);

                    // 充值状态
                    $recharge->status = self::PAY_STATUS_YES;
                    // 支付方式
                    $recharge->pay_type = $pay_type == self::QJ_WECHAT_H5_PAY ? self::PAY_TYPE_WECHAT : ( $pay_type == self::QJ_ALI_H5_PAY ? self::PAY_TYPE_ALIPAY : self::PAY_TYPE_OTHER );
                    $recharge->updated_at = new \Datetime;

                    // 使用该字段区分两个优支付渠道
                    $recharge->bmob_order_id = self::QJ_APPID;
                    Recharge::store($recharge);

                    Recharge::commit();

                    echo 'success';
                } catch (Exception $e) {
                    $this->error('recharge callback failed rollbak', [$e->getMessage()]);
                    Recharge::rollback();

                    echo 'failed';
                }
            }
        }
    }

    /**
     * 充值列表
     */
    public function index()
    {
        $user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);

        if (!empty($user)) {
            $status = isset($_GET['status']) ? intval($_GET['status']) : '';
            $month = isset($_GET['month']) ? $_GET['month'] : '';

            // 月份查询时不分页
            if (!empty($month)) {
                // month参数验证
                if ($month < 201702 || $month > date("Ym", strtotime("+1 month"))) {
                    $this->return_success([]);
                    exit;
                } else {
                    $start_time = substr($month, 0, 4) . '-' . substr($month, -2) . '-01 00:00:00';
                    $end_time = date('Y-m-d H:i:s', strtotime("+1 month", strtotime($month . '01')));
                    $recharges = $status === '' ? Recharge::findAll('recharges', ' uid = ? AND created_at > ? AND created_at < ? ', [ $user->id, $start_time, $end_time ]) : Recharge::findAll('recharges', ' uid = ? AND status = ? AND created_at > ? AND created_at < ? ', [ $user->id, $status, $start_time, $end_time ]);
                }
            } else {
                $paging_data = $this->getPageCount();
                $recharges = $status === '' ? Recharge::findAll('recharges', ' uid = ? ORDER BY ? DESC LIMIT ?, ? ', [ $user->id, 'created_at', $paging_data['start'], $paging_data['count'] ]) : Recharge::findAll('recharges', ' uid = ? AND status = ? ORDER BY ? DESC LIMIT ?, ? ', [ $user->id, $status, 'created_at', $paging_data['start'], $paging_data['count'] ]);
            }

            $ret = [];
            if (foreachAble($recharges)) {
                foreach ($recharges as $k => $recharge) {
                    $ret[$k]['id'] = $recharge->id;
                    $ret[$k]['pid'] = $recharge->pid;
                    $ret[$k]['content'] = $recharge->type == self::TYPE_SCORE ? $recharge->amount . '积分' : ($recharge->type == self::TYPE_VIP ? $recharge->amount . '个月VIP' : '');
                    $ret[$k]['status'] = $recharge->status;
                    $ret[$k]['created_at'] = $recharge->created_at;
                }

                $ret = array_values($ret);
            }

            $this->return_success($ret);
        } else {
            $this->return_error();
        }
    }

    /**
     * 订单详细状态
     */
    public function detail()
    {
        $id = $this->getRequestID();

        if (empty($id)) {
            $this->return_error(400, '请传入充值id');
        } else {
            $recharge = Recharge::findOne('recharges', ' id = ? AND uid = ? ', [ $id, $this->token->uid ]);

            if (empty($recharge)) {
                $this->return_error(401, '充值订单不存在');
            } else {
                $ret = [];

                $ret['id'] = $recharge->id;
                $ret['content'] = $recharge->type == self::TYPE_SCORE ? $recharge->amount . '积分' : ($recharge->type == self::TYPE_VIP ? $recharge->amount . '个月VIP' : '');
                $ret['pid'] = $recharge->pid;
                $ret['status'] = $recharge->status;
                $ret['created_at'] = $recharge->created_at;

                $this->return_success($ret);
            }
        }
    }

    /**
     * 首充大礼包
     */
    public function first()
    {
        $first_recharge_params = Config::getFirstRechargeParams();
        if (empty($first_recharge_params)) {
            $this->return_error(404, '无首充大礼包');
        } else {
            // 支付通道
            $pay_choice = Config::getPayChoice();
            if (empty($pay_choice)) {
                $this->return_error(500, '后台未配置支付通道');
                return;
            }

            $user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);
            if (empty($user)) {
                $this->return_error();
            } elseif (!empty(Recharge::count('recharges', ' uid = ? AND status = ? ', [ $user->id, self::PAY_STATUS_YES ]))) {
                $this->return_error(403, '已经充值过，没有首充资格');
            } else {
                $first_recharge_params = json_decode($first_recharge_params);

                // 充值
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $recharge = Recharge::dispense('recharges');
                    $recharge->uid = $user->id;
                    $recharge->money = $first_recharge_params->money;
                    $recharge->type = self::TYPE_SCORE;
                    $recharge->amount = $first_recharge_params->scores + $first_recharge_params->attach;
                    $recharge->attach = $first_recharge_params->vip;
                    $recharge->status = self::PAY_STATUS_NO;
                    $recharge->platform = strtolower($this->platform) == 'android' ? self::PLATFORM_ANDROID : self::PLATFORM_IOS;

                    if (Recharge::store($recharge)) {
                        $this->return_success(
                            [
                                'id' => $recharge->id,
                                'product_id' => $recharge->pid,
                                'content' => $recharge->type == self::TYPE_SCORE ? $recharge->amount . '积分' : ($recharge->type == self::TYPE_VIP ? $recharge->amount . '个月VIP' : ''),
                                'type' => $recharge->type,
                                'amount' => $recharge->amount,
                                'status' => $recharge->status,
                                'created_at' => $recharge->created_at ?: getCurrentTime(),
                                'vip_deadline' => $user->vip_deadline ?: '0',
                                'scores' => $user->remaining_scores ?: '0',
                                'pay' => [
                                    'type' => $pay_choice,									// 支付方式
                                    'callback_url' => self::PAY_CALLBACK_URL[$pay_choice],	// 支付回调地址
                                    'back_url' => 'http://www.dianzanyun.com',				// 官网
                                    'cpparam' => self::PAY_CPPARAM[$pay_choice],						// 优支付透传参数
                                ],
                                'channel' => [												// 话付通支付渠道
                                    'id' => array_keys(self::HFT_PAY_CHANNEL)[0],
                                    'key' => array_values(self::HFT_PAY_CHANNEL)[0],
                                ],
                            ]
                        );
                    } else {
                        $this->error('recharge failed', (array)$recharge);
                        $this->return_error();
                    }
                } else {
                    $this->return_success(
                        [
                            'money' => $first_recharge_params->money,
                            'scores' => $first_recharge_params->scores,
                            'attach' => $first_recharge_params->attach,
                            'vip' => $first_recharge_params->vip,
                            'people_amount' => $first_recharge_params->people + $first_recharge_params->incre_people * intval((getCurrentTime() - strtotime($first_recharge_params->start_day)) / 86400),
                            'extra' => '',
                            'product_id' => 0,
                        ]
                    );
                }
            }
        }
    }

    public function youPayNotify()
    {
        $appkey = '3c3db6985f32923d833f2dd5c79472e0';
        $orderNo = $_REQUEST['orderid'];//交易订单号
        $result = $_REQUEST['result'];//交易结果         1:成功 2:失败
        $fee = $_REQUEST['fee'];//用户实际支付的金额
        $paytype = $_REQUEST['paytype'];//对应请求时的支付参数
        $tradetime = $_REQUEST['tradetime'];//订单交易时间
        $cpparam = $_REQUEST['cpparam'];//透传参数原样传回
        $sign = $_REQUEST['sign'];//字符串MD5加密验证（小写）
        //sign= MD5(orderid + result + fee + tradetime + appkey)
        //加密串
        $s_sign = strtoupper(md5($orderNo.$result.$fee.$tradetime.$appkey));

        $paramJson = base64_decode($cpparam);
        $param = json_decode($paramJson,true);
        $order_str = $param['order_str'];
        //判断支付结果
        if ($result != 1) {
            echo "fail1\n";
            exit;
        }
        //判断加密
        if(strtoupper($sign) !== $s_sign){
            echo "fail2\n";
            echo $orderNo.$result.$fee.$tradetime.$appkey . "\n";
            echo $s_sign;
            exit;
        }
        $orders_model = M('orders');
        $orders_log_model = M('orders_log');
        $pay_amt = $fee/100;
        $orders_info = $this->getOrdersLogs($order_str, $pay_amt);
        if (empty($orders_info)) {
            echo "fail3\n";die;
        }
        //判断当前订单是否已经付过款
        if ($orders_info['status'] == 1 || $orders_info['status'] == 2) {
            echo "OK";die;
        }
        //订单数组
        $order_add = $this->setOrdersData($orders_info, $orderNo);
        $orders_model->startTrans();
        $sta = $orders_log_model->where(array('id'=>$orders_info['id']))->save(array('status'=>1,'updatetime'=>time(),'trade_no'=>$orderNo));
        if ($sta) {
            $issu = $this->paidSetData($orders_info);
            if ($issu) {
                $order_add['status'] = 1;
                $orderS = $orders_model->add($order_add);
                if ($orderS) {
                    $orders_model->commit();
                    //                     file_put_contents('notify.txt', '完成了~~~');
                    echo "OK";exit();	//请不要修改或删除
                } else {
                    $orders_model->rollback();
                    echo "fail4\n";
                    exit;
                }
            }else {
                $orders_model->rollback();
                echo "fail5\n";
                exit;
            }
        } else {
            $orders_model->rollback();
            echo "fail6\n";
            exit;
        }
    }

    /**
     * 获取先付支付类型
     * @param $paytype
     * @return int
     */
    private function getXfPaytype($paytype)
    {
        switch ($paytype) {
            case 11:
            case 21:
            case 28:
            case 31:
                $r = self::PAY_TYPE_WECHAT;
                break;
            case 12:
            case 22:
            case 32:
                $r = self::PAY_TYPE_ALIPAY;
                break;
            default:
                $r = self::PAY_TYPE_OTHER;
        }
        return $r;
    }
}


