<?php 
ini_set("display_errors","On"); //打开debug
ini_set("date.timezone","PRC");
include "OrderPush.class.php";
function send_not_enough_message($user_data,$open_data)
{
	$data = array(
				'first'=>array('value'=>$open_data['first_msg'],'color'=>"#000000"),
                'keyword1'=>array('value'=>'尾号'.substr($user_data['cardno'],strlen($user_data['cardno'])-4,4),'color'=>"#173177"),
				'keyword2'=>array('value'=>$user_data['lasttime'],'color'=>"#173177"),
				'keyword3'=>array('value'=>$user_data['lastmoney'].'元','color'=>"#173177"),
                'remark'=>array('value'=>$open_data['ex_msg']),
            );
	$ch = new OrderPush();	
	$result = $ch->doSend($user_data['openid'],$open_data['default_template'],$open_data['url'],$data);
	if($result['errcode'] == 0) return 0;
	elseif($result['errcode'] == '42001'){ //如果是超期，就再刷新一下token，再发一次
		$ch->resetToken();
		$result = $ch->doSend($user_data['openid'],$open_data['default_template'],$open_data['url'],$data);
		if($result['errcode'] == 0) return 0;
		else 
			return $result['errcode'];
	}
}

$user_data = array(
				'openid'=>'o3DOqtzq6QyvK8Q7XBvt6UqfC0a8',
				'cardno'=>'2150010100123456',
				'lasttime'=>'2015年2月26日',
				'lastmoney'=>'23.12'
);
// o3DOqt5JZsLitFAy5BQupof9U8L4 ，贝伟东
// o3DOqtyJGggLYk0gdFOXGkv7D87c ，徐振裕
// o3DOqtzq6QyvK8Q7XBvt6UqfC0a8 ，老婆
modifyConfig(0,0,0,0,0,"这是一个测试的行为!\r\n\r\n@欢迎光临@!");
$config = getConfig();
$result = send_not_enough_message($user_data,$config);
if($result == 0) echo "发送成功！";
?>