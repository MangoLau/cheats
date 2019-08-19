<?php

namespace App\Middleware;

use Curl\Curl;

class Cheat
{
	private $card;					// 卡密账号
	protected $login_url;			// 登录url
	protected $url;					// 业务url
	protected $progress_url;		// 进度url
	protected $cardinfo_url;		// 卡密详情url
	public $user_agent;				// ua
	public $content_type;			// content-type

    private $username = '13570208297';
    private $username_password = 'kudian888';

	public function __construct($card = '')
	{
		$this->card = $card;

		$this->user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36';
		$this->content_type = 'application/x-www-form-urlencoded; charset=UTF-8';
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getLoginUrl()
	{
		return $this->login_url;
	}

	public function getProgressUrl()
	{
		return $this->progress_url;
	}

	public function getCardinfoUrl()
	{
		return $this->cardinfo_url;
	}

	public function setUrl($url = '')
	{
		$this->url = $url;
	}

	public function setLoginUrl($login_url = '')
	{
		$this->login_url = $login_url;
	}

	public function setProgressUrl($progress_url = '')
	{
		$this->progress_url = $progress_url;
	}

	public function setCardinfoUrl($cardinfo_url = '')
	{
		$this->cardinfo_url = $cardinfo_url;
	}

	// 登录
	private function login($cookie = '')
	{
		$url = $this->login_url;
		$url_info = parse_url($url);
		parse_str($url_info['query'], $query);
		$host = $url_info['host'];
		$referer = $origin = $url_info['scheme'] . '://' . $host;

		$curl = new Curl;
		$curl->setTimeout(20);
		$curl->setOpt(CURLOPT_COOKIEJAR, $cookie);
		$curl->setOpt(CURLOPT_RETURNTRANSFER, true);
		$curl->setHeader('Content-Type', $this->content_type);
		$curl->setHeader('Referer', $referer);
		$curl->setHeader('Host', $host);
		$curl->setHeader('Origin', $origin);
		$curl->setHeader('User-Agent', $this->user_agent);
		$curl->setHeader('X-Requested-With', 'XMLHttpRequest');
		$curl->setHeader('Connection', 'keep-alive');

		// 服务器IP被封，此处需要用代理
		// $curl->setOpt(CURLOPT_PROXY, '113.65.161.205:9797');
		// $curl->setOpt(CURLOPT_PROXY, '110.73.55.77:8123');

		$curl->post($url, array(
		    'cardno' => $this->card,
		    'password' => '',
		    'username' => '',
		    'username_password' => '',
		    'sendpass_username' => '',
		    'reg_username' => '',
		    'reg_password' => '',
		    'reg_sex' => 0,
		    'reg_qq' => '',
		    'id' => $query['id'],
		    'goods_type' => $query['goods_type'],
		));

		// dd([$curl->error, $curl->errorMessage, $curl->curlErrorMessage, $curl->response, $curl->getInfo(), $query]);
		return $curl;
	}

    /**
     * 账号登录
     * @param string $cookie
     * @return Curl
     * @throws \ErrorException
     */
	private function accountLogin($cookie = '')
    {
        $url = $this->login_url;
        $url_info = parse_url($url);
        parse_str($url_info['query'], $query);
        $host = $url_info['host'];
        $referer = $origin = $url_info['scheme'] . '://' . $host;

        $curl = new Curl;
        $curl->setTimeout(20);
        $curl->setOpt(CURLOPT_COOKIEJAR, $cookie);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $curl->setHeader('Content-Type', $this->content_type);
        $curl->setHeader('Referer', $referer);
        $curl->setHeader('Host', $host);
        $curl->setHeader('Origin', $origin);
        $curl->setHeader('User-Agent', $this->user_agent);
        $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
        $curl->setHeader('Connection', 'keep-alive');

        // 服务器IP被封，此处需要用代理
        // $curl->setOpt(CURLOPT_PROXY, '113.65.161.205:9797');
        // $curl->setOpt(CURLOPT_PROXY, '110.73.55.77:8123');

        $curl->post($url, array(
            'cardno' => '',
            'password' => '',
            'username' => $this->username,
            'username_password' => $this->username_password,
            'sendpass_username' => '',
            'reg_username' => '',
            'reg_password' => '',
            'reg_sex' => 0,
            'reg_qq' => '',
            'id' => $query['id'],
            'goods_type' => $query['goods_type'],
        ));

        // dd([$curl->error, $curl->errorMessage, $curl->curlErrorMessage, $curl->response, $curl->getInfo(), $query]);
        return $curl;
    }

	// 各种点赞/转发处理
	public function handle($qq = '', $amount = 100, $extra = [])
	{
		$cookie = '../tmp/' . __FUNCTION__ . '-' . intval(microtime(true)*10000) . '-cookie.txt';
		parse_str(parse_url($this->url)['query'], $query);

		$curl = $this->login($cookie);

		$params = [
			'qq' => $qq,
		    'need_num_0' => $amount,
		    'goods_id' => $query['id'],
		    'goods_type' => $query['goods_type']
		];

		if (!empty($extra)) {
			$params += $extra;
		}

		$curl->post($this->url, $params);

		$curl->close();

		@unlink($cookie);
		// dd([$curl->error, $curl->response, $curl->getInfo(), $params, $this->url]);
		
		// if ($curl->response->status == 0) {
		// 	echo json_encode($params) . "\n";
		// 	error_log(json_encode([ $curl->response, $curl->getInfo(), $params ]));
		// }

		return $curl;
	}

    /**
     * 下单
     * @param string $qq
     * @param int $amount
     * @param array $extra
     * @return Curl
     * @throws \ErrorException
     */
	public function order($qq = '', $amount = 100, $extra = [])
    {
        $cookie = '../tmp/' . __FUNCTION__ . '-' . intval(microtime(true)*10000) . '-cookie.txt';
        parse_str(parse_url($this->url)['query'], $query);

        $url = $this->login_url;
        $url_info = parse_url($url);
        parse_str($url_info['query'], $query);
        $host = $url_info['host'];
        $referer = $origin = $url_info['scheme'] . '://' . $host;

        $curl = new Curl;
        $curl->setTimeout(20);
        $curl->setOpt(CURLOPT_COOKIEJAR, $cookie);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $curl->setHeader('Content-Type', $this->content_type);
        $curl->setHeader('Referer', $referer);
        $curl->setHeader('Host', $host);
        $curl->setHeader('Origin', $origin);
        $curl->setHeader('User-Agent', $this->user_agent);
        $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
        $curl->setHeader('Connection', 'keep-alive');
        $params = [
            'qq' => $qq,
            'need_num_0' => $amount,
            'goods_id' => $query['id'],
            'goods_type' => $query['goods_type'],
            'Api_UserName' => $this->username,
            'Api_UserMd5Pass' => strtolower(md5($this->username_password)),
            'pay_type' => 1,
        ];

        if (!empty($extra)) {
            $params += $extra;
        }

        $curl->post($this->url, $params);

        $curl->close();

        @unlink($cookie);
        return $curl;
    }

	// 卡密详情 (不需登录)
	public function cardDetail()
	{
		$url = $this->cardinfo_url;
		$url_info = parse_url($url);
		$host = $url_info['host'];
		$referer = $origin = $url_info['scheme'] . '://' . $host;

		$curl = new Curl;
		$curl->setOpt(CURLOPT_RETURNTRANSFER, true);
		$curl->setHeader('Content-Type', $this->content_type);
		$curl->setHeader('Referer', $referer);
		$curl->setHeader('User-Agent', $this->user_agent);
		$curl->setHeader('X-Requested-With', 'XMLHttpRequest');

		$curl->post($this->url, array(
		    'kmcz_cardno' => $this->card,
		));

		return $curl;
	}

	// 订单进度
	public function orderProgress($page = 1, $search_qq = '')
	{
		$cookie = 'tmp/' . __FUNCTION__ . '-' . intval(microtime(true)*10000) . '-cookie.txt';

		$curl = $this->login($cookie);
		$curl->setHeader('__REQUEST_TYPE', 'AJAX_REQUEST');

		$count = 10;

		$paging_data = [
			'pageSize' => $count,
			'startRecord' => ($page - 1) * $count,
			'nowPage' => $page,
			'recordCount' => -1,
			'parameters' => [
				'qq' => $search_qq
			],
			'fastQueryParameters' => new \stdClass,
			'advanceQueryConditions' => [],
			'advanceQuerySorts' => []
		];

		$params = [
			'dtGridPager' => json_encode($paging_data),
			// 'dtGridPager' => '{"isExport":false,"pageSize":10,"startRecord":10,"nowPage":2,"recordCount":11,"pageCount":2.1,"parameters":{"qq":"771935889"},"fastQueryParameters":{},"advanceQueryConditions":[],"advanceQuerySorts":[]}'
		];

		$curl->post($this->progress_url, $params);

		$curl_info = $curl->getInfo();
		$postfield = $curl->getOpt(CURLOPT_POSTFIELDS);
		$response_header = $curl->responseHeaders;

		$curl->close();

		@unlink($cookie);
		// dd([$curl->error, $curl->response, $curl_info, $params, $postfield, $response_header]);
		return $curl;
	}

	// 1gege 卡密详情 (不需登录)
	public function gegeCardDetail()
	{
		$url = 'http://www.1gege.cn/index.php?m=Home&c=Card&a=cardinfo_no&id=8&goods_type=5';		// 空间留言
		// $url = 'http://www.1gege.cn/index.php?m=Home&c=Card&a=cardinfo_no&id=116&goods_type=9';		// 名片赞

		$curl = new Curl;
		$curl->setOpt(CURLOPT_RETURNTRANSFER, true);
		$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		$curl->setHeader('Referer', 'http://www.1gege.cn');
		$curl->setHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36');
		$curl->setHeader('X-Requested-With', 'XMLHttpRequest');

		$curl->post($url, array(
		    'kmcz_cardno' => $this->card,
		));

		return $curl;
	}

	// 名片赞订单进度查询
	// notice: 接口暂时有问题
	public function profileLikeProgress()
	{
		$url = 'http://www.1gege.cn/index.php?m=home&c=order&a=orderlist_dtGrid&goods_id=116&goods_type=9';
		$cookie = realpath('../tmp') . '/' . __FUNCTION__ . '-' . intval(microtime(true)*10000) . '-cookie.txt';

		$curl = $this->login1Gege($cookie);
		$curl->setHeader('__REQUEST_TYPE', 'AJAX_REQUEST');
		$curl->setHeader('Referer', 'http://www.1gege.cn/index.php?m=Home&c=Goods&a=detail&id=116');

		$curl->post($url, array(
		    'dtGridPager' => '{"isExport":false,"pageSize":10,"startRecord":0,"nowPage":1,"recordCount":7,"pageCount":1,"parameters":{"qq":"2892391933"},"fastQueryParameters":{},"advanceQueryConditions":[],"advanceQuerySorts":[]}',
		));


		@unlink($cookie);
		return $curl;
	}

	// xdzk 卡密详情 (不需登录)
	public function xdzkCardDetail()
	{
		$url = 'http://www.xdzk.net/index.php?m=Home&c=Card&a=cardinfo_no&id=1962&goods_type=143';
		$curl = new Curl;
		$curl->setOpt(CURLOPT_RETURNTRANSFER, true);
		$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		$curl->setHeader('Referer', 'http://www.xdzk.net');
		$curl->setHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36');
		$curl->setHeader('X-Requested-With', 'XMLHttpRequest');

		$curl->post($url, array(
		    'kmcz_cardno' => $this->card,
		));

		return $curl;
	}

	// 获取发表的说说 (不需登录)
	// 需要登录 2018-02-04
	public function xdzkQQTwittess($qq = '', $page = 1)
	{
		// $url = 'http://www.xdzk.net/index.php?m=home&c=jiuwuxiaohun&a=qq_shuoshuo_lists';
		// $url = 'http://xiaochao.95jw.cn/index.php?m=home&c=jiuwuxiaohun&a=qq_shuoshuo_lists';
		// $url = 'http://www.1gege.cn/index.php?m=home&c=jiuwuxiaohun&a=qq_shuoshuo_lists';
		// $url = 'http://1gege.ssgnb.95jw.cn/index.php?m=home&c=jiuwuxiaohun&a=qq_shuoshuo_lists';
		$url = 'http://www.1gege.cn/index.php?m=home&c=jiuwuxiaohun&a=qq_shuoshuo_lists&goods_type=601';
		$this->setLoginUrl('http://www.1gege.cn/index.php?m=Home&c=User&a=login&id=29943&goods_type=601');

		$cookie = '../tmp/' . __FUNCTION__ . '-' . intval(microtime(true)*10000) . '-cookie.txt';
		$curl = $this->accountLogin($cookie);

		// $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
		// $curl->setHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		// $curl->setHeader('Referer', 'http://1gege.ssgnb.95jw.cn/index.php?m=Home&c=Goods&a=detail&id=1908');
		// $curl->setHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36');
		// $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
		$curl->post($url, array(
		    'uin' => $qq,
		    'page' => $page,
		));
		$curl->close();

		unset($cookie);
		// dd($curl, $curl->response);

		return $curl;
	}

	// 获取发表的日志 (不需登录)
	public static function xdzkQQArticles($qq = '', $page = 1)
	{
		// $url = 'http://www.xdzk.net/index.php?m=home&c=jiuwuxiaohun&a=qq_rizhi_lists';
		$url = 'http://www.1gege.cn/index.php?m=home&c=jiuwuxiaohun&a=qq_rizhi_lists';
		$curl = new Curl;
		$curl->setOpt(CURLOPT_RETURNTRANSFER, true);
		$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		$curl->setHeader('Referer', 'http://www.xdzk.net');
		$curl->setHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36');
		$curl->setHeader('X-Requested-With', 'XMLHttpRequest');

		$curl->post($url, array(
		    'uin' => $qq,
		    'page' => $page,
		));

		return $curl;
	}

    /**
     * 获取qq说说
     * @param string $qq
     * @param int $page
     * @return mixed
     * @throws \ErrorException
     */
	public static function getQQTwittes($qq = '', $page = 1)
    {
        $count = 20;
        $baseUrl = 'https://mobile.qzone.qq.com/list';
        $params = [
            'format' => 'json',
            'list_type' => 'shuoshuo',
            'action' => '0',
            'res_uin' => $qq,
            'count' => $count,
        ];

        $reqUrl = $baseUrl.'?'.http_build_query($params);
        $curl = new Curl;
        /* cookie的获取方法 */
        // 浏览器里按F12进入调试模式 登录空间(https://qzone.qq.com/)
        // Network下寻找任意XHR请求 查看其Request Headers下的cookie
        // 仅截取cookie中的p_uin=xxx; pt4_token=xxx; p_skey=xxx (注意：仅需要p_uin pt4_token p_skey三个参数，不能多，也不能少)
        $curl->setHeader('accept', 'application/json');
        $curl->setHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36');
        $curl->setHeader('cookie', 'p_uin=o2529397815; pt4_token=ToWlJMSoA31qEP*WIgA*jYtJowuzRe4xWOse0-FoPe4_; p_skey=GxfABkX3LFhl*ZITPvFtjzTXehnQFiJJA8h764VbbJA_;');
        $cookie = realpath('../tmp') . '/' . __FUNCTION__ . '-' . intval(microtime(true)*10000) . '-cookie.txt';
        $curl->setOpt(CURLOPT_COOKIEJAR, $cookie);
        $curl->get($reqUrl);
        $ret =  $curl->exec();
        $curl->close();
        if (file_exists($cookie)) {
            unlink($cookie);
        }
        return $ret;
    }
}