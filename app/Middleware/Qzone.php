<?php
/**
 * 获取日志、说说列表等
 */

namespace App\Middleware;

use Curl\Curl;

class Qzone
{
	const QQ_TWITTEE_URL = 'http://taotao.qq.com/cgi-bin/emotion_cgi_homepage_msg';
	// const QQ_TWITTEE_URL = 'http://sh.taotao.qq.com/cgi-bin/emotion_cgi_feedlist_v6?hostUin=2892391933&ftype=0&sort=0&pos=0&num=10&replynum=0&g_tk=&callbackFun=_preloadCallback&code_version=1&format=jsonp&need_private_comment=1';
	const QQ_BLOG_URL = '';

	/**
	 * 获取说说列表
	 * @param  integer  $qq    QQ
	 * @param  integer $page  
	 * @param  integer $count
	 * @return mixed
	 */
	public static function getQQTwittees($qq, $page = 1, $count = 10)
	{
		$ret = [
			'code' => 1000,
			'result' => [],
		];

		// 整数判断
		if (ctype_digit(strval($qq))) {
			$params = [
				'owneruin' => $qq,
				'start' => ($page - 1) * $count,
				'count' => $count,
			];

			$url = self::QQ_TWITTEE_URL . '?' . http_build_query($params);
			$curl = new Curl;
			
			// 处理返回的jsonp格式的数据
			// _Callback({"result":{"code":0,"err":{"code":0},"msg":"","now":1497600398,"num":9,"posts":[{"checkflag":0,}], {..}]..,}})
			$data = substr($curl->get($url), 10, -2);
			$data = json_decode($data);
			if ($data->result->code > 0) {
				$ret['code'] = $data->result->code;
			} else {
				$ret['code'] = 0;
				$data =$data->result->posts;
				foreach ($data as $k => $v) {
					$ret['result'][] = [
						'tid' => $v->tid,
						'content' => $v->content,
						'created_at' => $v->create_time, 
					];
				}
			}
		}

		return $ret;
	}

	/**
	 * 获取空间日志列表
	 * @param  integer  $qq    QQ号码
	 * @param  integer $page
	 * @param  integer $count
	 * @return miexed         
	 */
	public static function getQQBlogs($qq, $page = 1, $count = 10)
	{

	}
}