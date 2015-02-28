<?php
	$appid = 'wx22a79da618f80847';
	$secret = '290b8e0211607a2215445b7b91642409';
	$code = 'error';
	if(isset($_GET['code'])){

		$code = $_GET['code'];
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
		$o_code = file_get_contents($url);
		$o_code = json_decode($o_code,true);
		echo $o_code['openid'];
	}
	else
		echo $code;


?>