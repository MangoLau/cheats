注: 
> 1. 除了登录接口之外所有请求的header需传入'**access-token**:xxx', 暂时不对access-token加密
> 2. header不需传access-token的接口: ['/login', '/recharge/callback', '/hot-people', '/qqProducts', '/banners', '/broadcasts']
> 3. error_code > 0 都表示有错误返回，可根据error_code是否等于0判断是否出错

## 登录
* 地址: **/login**
* Method: **POST**
* 参数
	* `openid` *string* QQ授权登录获取到的用户openid
	* `nickname` *string* QQ用户昵称
	* `avatar` *string* QQ用户头像地址
* 返回值:

``` json
    {
        "error_code": 0,
        "error": "",
        "result" : 
        {
            "access-token": xxx,  // token
            "qq": xxx,  // 绑定的QQ，未绑定返回空
            "nickname": xxx,
            "avatar": xxx,
            "vip_deadline": xxx,  // VIP到期的时间戳
            "scores": xxx,        // 剩余总积分
        }
    }
```

``` json
    {
        "error_code": 100,
        "error": "失败原因",
        "result": null,
    }
```

## 用户信息
* 地址: **/userinfo**
* Method: **GET**
* 参数
    + 无
* 返回值:

``` json
    {
        "error_code": 0,
        "error": "",
        "result" : 
        {
            "qq": xxx,  // 绑定的QQ，未绑定返回空
            "nickname": xxx,
            "avatar": xxx,
            "vip_deadline": xxx,  // VIP到期的时间戳
            "scores": xxx,        // 剩余总积分
        }
    }
```

``` json
    {
        "error_code": 100,
        "error": "失败原因",
        "result": null,
    }
```

## refresh token
* 地址: **/refreshToken**
* Method: **POST**
* 参数:
    无
* 返回值:

``` json
   {
       "error_code": 0,
       "error": "",
       "result" : 
       {
       	"access-token": xxx
       }
   }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## (更换)绑定QQ
* 地址: **/bindQQ**
* Method: **POST**
* 参数:
    + `qq` *string* 绑定的QQ号码
* 返回值:

``` json
   {
       "error_code": 0,
       "error": "",
       "result" : "success"
   }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## 解绑QQ
* 地址: **/unbindQQ**
* Method: **POST**
* 参数:
    无
* 返回值:

``` json
   {
       "error_code": 0,
       "error": "",
       "result" : "success"
   }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## 充值产品列表
* 地址: **/products**
* Method: **GET**
* 参数:
    无
* 返回值:

```json
	 {
        "error_code": 0,
        "error": "",
        "result" : 
        [
            {
                "id": xxx,
                "money": xxx, // 单位分
                "type": xxx, // 1:积分， 2:VIP                       
                "amount": xxx,	// 数量, type是单位, 如2个月VIP, 5000积分
            },
            {
                ...
            },
            ...
        ]
   }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## QQ刷赞/转发等产品列表
* 地址: **/qqProducts**
* Method: **GET**
* 参数:
    无
* 返回值:

```json
   {
        "error_code": 0,
        "error": "",
        "result" : 
        [
            {
                "id": xxx,  // cheat_id
                "title": xxx, // 名称，如刷名片赞
                "icon": xxx,  // icon图片地址
                "need_vip": x, // 0:不需vip, 1:需要VIP
                "type": x,  // 1:说说类型，2:日志类型，3:其他类型
            },
            {
                ...
            },
            ...
        ]
   }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## QQ刷赞/转发等某个产品的产品列表
* 地址: **/qqProductList**
* Method: **GET**
* 参数:
    * `cid` *string* cheat_id 
* 返回值:

```json
   {
        "error_code": 0,
        "error": "",
        "result" : 
        [
            {
                "id": xxx,  // cpid
                "amount": xxx,  // 数量
                "scores": xxx, // 所需积分数
            },
            {
                ...
            },
            ...
        ]
   }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## 充值
* 地址: **/recharge**
* Method: **POST**
* 参数:
    * `product_id` *string* 商品编号
    * `bmob_order_id` *string* bmob支付订单号
    * `money` *string* 实际支付金额, 单位分
* 返回值:

```json
   {
       "error_code": 0,
       "error": "",
       "result" : 
       {
           "id": xxx, // 充值ID
           "product_id": xxx, // 商品编号
           "content": xxx,	// 描述，如：1000积分/3个月VIP
           "status": xxx,	// 0:未付款，1:已付款，2:已完成，3:失败
           "created_at": 1456382625,
           "vip_deadline": xxx,  // VIP到期的时间戳
           "scores": xxx,        // 剩余总积分
       }
   }
```

```json
   {
       "error_code": 100,
       "error": "创建失败原因",
       "result": null,
   }
```

## 充值列表
* 地址: **/recharges**
* Method: **GET**
* 参数:
    * `month`: xxx // 月份，如 201702
    * `status`: xxx	// 0:未支付，1:已支付
    * `page`: xxx
    * `count`: xxx
* 返回值:

```json
   {
       "error_code": 0,
       "error": "",
       "result" : 
       [
          {
              "id": "1",
              "pid": xxx, // 商品编号                            
              "content": xxx,	// 描述，如：1000积分/1个月VIP
              "status": xxx,	// 0:未支付，1:已支付
              "created_at": 1456382625
          },
          {
              ...
          },
          ...
       ]
   }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## 积分变动记录
* 地址: **/scores**
* Method: **GET**
* 参数:
    * `month`: xxx // 月份，如 201702
* 返回值:

```json
   {
       "error_code": 0,
       "error": "",
       "result" : 
       [
          {
              "type": x,                  // 类型，1:充值，2:签到赠送, 3:购买VIP赠送，4:消费
              "remark": "签到",            // 备注，type字段说明，充值、签到赠送、购买VIP赠送、（刷赞的类型）
              "scores": "+100"/"-100",              // 积分数(type=4时scores为负数,其他为带'+'的数值)
              "created_at": 1489044636    // 创建时间
          },
          {
              ...
          },
          ...
       ]
   }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## 下订单
* 地址: **/order**
* Method: **POST**
* 参数:
    * `cpid` *string* 商品编号 (必需)
    * `channel` *string* 渠道 (非必需)
    * `ssid`  *string* 说说id (对说说的操作需传入说说ID)
    * `ssnr`  *string* 说说内容 (非必需)
    * `rzid`  *string* 日志id (对日志的操作需传入日志ID)
    * `rznr`  *string* 日志内容 (非必需)
* 返回值:

```json
   {
       "error_code": 0,
       "error": "",
       "result" : 
       {
           "id": "5",                 // 订单id
           "qq": "1806559096",        // 当前的qq
           "type": "刷说说点赞",       // 订单类型
           "amount": "50",            // 个数
           "scores": "200",           // 消费积分
           "status": "1",             // 状态，1:进行中，2:已完成，3:失败
           "created_at": "1489463765" // 创建时间
       }
   }
```

```json
   {
       "error_code": 100,
       "error": "创建失败原因",
       "result": null,
   }
```

## 订单列表
* 地址: **/orders**
* Method: **GET**
* 参数:
    * `status`: xxx	// 0:未付款，1:已付款，2:已完成，3:失败
    * `page`: xxx
    * `count`: xxx
* 返回值:

```json
    {
        "error_code": 0,
        "error": "",
        "result" : 
        [
           {
               "id": "5",                 // 订单id
               "qq": "1806559096",        // 当前的qq
               "type": "刷说说点赞",       // 订单类型
               "amount": "50",            // 个数
               "real_amount": "40",       // 已处理的个数
               "scores": "200",           // 消费积分
               "status": "1",             // 状态，1:进行中，2:已完成，3:失败
               "created_at": "1489463765" // 创建时间
            },
            {
               ...
            },
            ...
        ]
   }
```

```json
   {
        "error_code": 100,
        "error": "失败原因",
        "result": null,
   }
```

## 签到
* 地址: **/attendance**
* Method: **POST**
* 参数:
    无
* 返回值:

```json
   {
       "error_code": 0,
       "error": "",
       "result" : "success"
   }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## 签到列表
* 地址: **/attendances**
* Method: **GET**
* 参数:
    * `page`: xxx
    * `count`: xxx
* 返回值:

```json
	  {
        "error_code": 0,
        "error": "",
        "result" : 
        [
            {
                "id": xxx,
                "day": xxx, // 签到日期,如 20170305
            },
            {
                ...
            },
            ...
        ]
    }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## 配置
* 地址: **/config**
* Method: **GET**
* 参数:
    * `k`: xxx  // key,选填，不传默认返回所有配置列表
* 返回值:

```json
    {
        "error_code": 0,
        "error": "",
        "result" : 
        [
            {
                "key": "vip_recharge_discount",         // key
                "value": "50",                          // value
                "remark": "VIP用户购买积分的折扣,百分比"    // 说明
            },
            {
                ...
            },
            ...
        ]
    }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## banner列表
* 地址: **/banners**
* Method: **GET**
* 参数:
    无
* 返回值:

```json
    {
        "error_code": 0,
        "error": "",
        "result" : 
        [
            {
                "id": xxx,
                "pic": xxx, // 图片
                "link": xxx, // 跳转地址
            },
            {
                ...
            },
            ...
        ]
    }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## 空间红人表
* 地址: **/hot-people**
* Method: **GET**
* 参数:
    无
* 返回值:

```json
    {
        "error_code": 0,
        "error": "",
        "result" : 
        [
            {
                "qq": xxx,  // QQ
                "avatar": xxx, // 头像
                "scores": xxx, // 积分
            },
            {
                ...
            },
            ...
        ]
    }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## 说说列表
* 地址: **/shuoshuo**
* Method: **GET**
* 参数:
    * `page`: xxx // 注：此处是调用的第三方接口，而此接口数据翻页有问题，暂未处理 todo.
* 返回值:

```json
    {
        "error_code": 0,
        "error": "",
        "result" : 
        [
            {
                "ssid": "91ce022e26a039530b6e0e00",   // 说说id
                "content": "呵呵",                    // 说说内容
                "created_time": "1396285478"          // 创建时间
            },
            {
                ...
            },
            ...
        ]
    }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## 日志列表
* 地址: **/qqBlogs**
* Method: **GET**
* 参数:
    * `page`: xxx // 注：此处是调用的第三方接口，count固定为15
* 返回值:

```json
    {
        "error_code": 0,
        "error": "",
        "result" : 
        [
            {
                "rzid": "1336842949",          // 日志id
                "content": "甲乙丙丁E",         // 日志内容
                "created_time": "1336842900"   // 创建时间 
            },
            {
                ...
            },
            ...
        ]
    }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## 充值广播假数据
* 地址: **/broadcasts**
* Method: **GET**
* 参数:
    * `count`: xxx // 默认返回15个
* 返回值:

```json
    {
        "error_code": 0,
        "error": "",
        "result" : 
        [
            "60589*****150.00元购买了6个月VIP",
            "45388*****32.41元购买了3425342积分",
            "93305*****150.00元购买了6个月VIP",
            "87730*****34.54元购买了44个月VIP",
            "60907*****80.00元购买了3个月VIP",
            "...,"
        ]
    }
```

```json
   {
       "error_code": 100,
       "error": "失败原因",
       "result": null,
   }
```

## 先付支付充值
* 地址: **/recharge/xianfu**
* Method: **POST**
* 参数:
    * `product_id` *string* 商品编号
    * `money` *string* 实际支付金额, 单位分
    * `paytype` *int* 支付类型
* 返回值:

```json
   {
       "error_code": 0,
       "error": "",
       "result" : 
       {
           "id": 1, // 充值ID
           "product_id": 1, // 商品编号
           "content": "xxx",	// 描述，如：1000积分/3个月VIP
           "status": 0,	// 0:未付款，1:已付款，2:已完成，3:失败
           "created_at": 1456382625,
           "vip_deadline": "xxx",  // VIP到期的时间戳
           "scores": "xxx",        // 剩余总积分
           "pay": {
                "payurl":"xxx",//生成的支付链接或者二维码图片或者跳转的页面
                "paytype":21 //支付类型
           }
       }
   }
```

```json
   {
       "error_code": 100,
       "error": "创建失败原因",
       "result": null,
   }
```

## 获取首充大礼包信息
* 地址: **/recharge/first**
* Method: **GET**
* 参数:
    无
* 返回值:

```json
    {
        "error_code": 0,
        "error": "",
        "result" : 
        {
            "money": 900,             // 金额，单位分
            "scores": 9000,          // 积分数
            "attach": 3000,          // 赠送积分数
            "vip": 1,                 // 赠送的VIP月数
            "people_amount": 29651,   // 已购买的人数
            "extra": "",               // 其他，暂时没用
            "product_id": 0         //产品id，占位符0
        }
    }
```

```json
   {
       "error_code": 100,
       "error": "创建失败原因",
       "result": null,
   }
```