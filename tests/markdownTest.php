<?php

require __DIR__ . '/public.php';

global $php;
header("Content-type: text/html; charset=utf-8");
$parser = new \Zoco\Markdown();

$text = '## 支付宝
| 序号|功能 | 路径 | Wiki已完成|内网发布|正式数据|已发外网|开发者|
|---- |---- | ---- |  ---- | ---- | ---- | ---- | ---- |
|1 |获取签名|[/api/account/alisdk2/sign](9732)|√||||@zhang.qiao|

## 会员签到
| 序号|功能 | 路径 | Wiki已完成|内网发布|正式数据|已发外网|开发者|
|---- |---- | ---- |  ---- | ---- | ---- | ---- | ---- |
|1 |签到|[/v1/vip/sign/action](9792)|√||||@zhang.qiao|
|2 |会员中心|[/v1/vip/config/center](9795)|√||||@zhang.qiao|
|3 |会员中心-非会员|[/v1/vip/config/vipCenterNotVip](9811)|√||||@zhang.qiao|


## 聊天室围观
| 序号|功能 | 路径 | Wiki已完成|内网发布|正式数据|已发外网|开发者|
|---- |---- | ---- |  ---- | ---- | ---- | ---- | ---- |
|1 |加入或者围观|[/v1/chatroom/user/joinorvisit](9842)|√||||@zhang.qiao|
|1 |围观列表|[/v1/chatroom/visitor/lists](9858)|√||||@zhang.qiao|


## 礼物商城
| 序号|功能 | 路径 | Wiki已完成|内网发布|正式数据|已发外网|开发者|
|---- |---- | ---- |  ---- | ---- | ---- | ---- | ---- |
|1 |签到|[/v1/giftshop/like/userBirthdays](2653)|√||||@zhang.qiao|


## 陌陌现场
| 序号|功能 | 路径 | Wiki已完成|内网发布|正式数据|已发外网|开发者|
|---- |---- | ---- |  ---- | ---- | ---- | ---- | ---- |
|1 |艺人资料增加粉丝入口|[/v1/user/my/index](9672)|√||||@zhang.hongzhu_743|
|2 |未关注的艺人增加现场入口|[/v1/user/my/index](9675)|√||||@zhang.hongzhu_743|
|3 |附近的人陌陌现场样式优化|[/v1/nearby/index](9676)|√||||@zhang.hongzhu_743|
|4 |获取粉丝列表|[/v1/user/relation/getStarFollowers](9790)|√||||@zhang.hongzhu_743|

## 微信
| 序号|功能 | 路径 | Wiki已完成|内网发布|正式数据|已发外网|开发者|
|---- |---- | ---- |  ---- | ---- | ---- | ---- | ---- |
|1 |微信支付签名|[/v1/account/weixinpay/sign](9698)|√||||@wang.zhihao|
|2 |微信支付check|[/v1/account/weixinpay/check](9757)|√||||@wang.zhihao|

## 移动退订
| 序号|功能 | 路径 | Wiki已完成|内网发布|正式数据|已发外网|开发者|
|---- |---- | ---- |  ---- | ---- | ---- | ---- | ---- |
|1 |移动退订API|[/api/account/mmpay/unsubscribe](9844)|√||||@wang.zhihao|';
$html = $parser->makeHtml($text);
echo $html;
