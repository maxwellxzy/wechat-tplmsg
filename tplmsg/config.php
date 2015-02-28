<?php
/*
经常使用的函数：
1、初始化数据库：
setDefaultConfig();一般情况下不用使用；更换数据库以后使用；
modifyConfig();修改参数，除了token以外的所有参数均可以修改；
getConfig();获取所有参数；
restoreToken();获取token，存储token；
updated:feb 27,2015,by maxwell
https://github.com/maxwellxzy/wechat-tplmsg
*/
ini_set("date.timezone","PRC");
ini_set("display_errors","On"); //打开debug
//如果需要修改默认值，请在这里修改，其他地方就不要动了！！
define('SQL_HOST','localhost');
define('SQL_USER','root');
define('SQL_PASSWORD','123456');
define('SQL_DB','abc');
define('KEY','051235s7hJyfzVz6JLDGON35sNI7hT5nUllCL2-5pxDUd90kzoBspu9ckZOGK35sNI7hOIclPXnAPxWB1V0VT82E3raRoQ35sNI7hwU-QjL1JCXhkSlLwK4Z2GIsq93AHig2150');
define('APPID','xxxxxx');
define('APPSECRECT','xxxxx');
define('DEFAULT_TPL','jc3nNPhxrhkRbvYGWYQocRmmcjYmRW4VeeyRgAefytY');
define('FIRST_MSG',"尊敬的客户，您好！\r\n\r\n您绑定的市民卡余额不足（测试）：");
define('URL',"http://www.szsmk.com/");
define('EX_MSG',"请及时充值！\r\n\r\n@点击进入优惠活动！@");
define('ACCESS_TOKEN','ARPGI-zbLWoJWTgpambLcjz13TkaocEuStaZ-ijWr5yafguNf5zJc-qZfM1_b8OXGLzP4e0N1MOWWbgXpgBH2xiteXQugkUoJwb0Wgde8tE');
define("EXP_TIME",7200);
global $con;
//以上默认值可以修改，下面的就不要动了！！

//连接数据库
function connectSql($host=SQL_HOST,$user=SQL_USER,$password=SQL_PASSWORD,$db=SQL_DB)
{
	global $con;
	$con = mysql_connect($host,$user,$password);
	if (!$con)
	  {
	  die('Could not connect: ' . mysql_error());
	  return false;
	  }
		if(!mysql_select_db($db, $con))	return false;
	mysql_query("set character set 'utf8'");//读库 
	mysql_query("set names 'utf8'");//写库 
	return true;
}

//设置默认参数，初始化数据库
function setDefaultConfig()
{
	global $con;
	if(!$con){if(!connectSql()) return false;}
	$sql = "select * from wx_config where 1";
	if(mysql_num_rows(mysql_query($sql))<1 ){
		$sql = "INSERT INTO `wx_config`(`appid`, `appsecrect`, `default_template`, `first_msg`, `url`, `ex_msg`, `access_token`, `createtime`) VALUES ('".APPID."','".APPSECRECT."','".DEFAULT_TPL."','".FIRST_MSG."','".URL."','".EX_MSG."','".ACCESS_TOKEN."',10000)";
	if(!mysql_query($sql)) return false;
	}
	return true;
}

//修改参数，除了token
//其他都设置为false时，默认不变；
function modifyConfig($appid,$appsecrect,$template,$url,$first_msg,$ex_msg)
{
	$config = getConfig();
	if(false == $appid)	$appid = $config['appid'];
	if(false == $appsecrect)	$appsecrect = $config['appsecrect'];
	if(false == $template)	$template = $config['default_template'];
	if(false == $first_msg)	$first_msg = $config['first_msg'];
	if(false == $url)	$url = $config['url'];
	if(false == $ex_msg)	$ex_msg = $config['ex_msg'];
	
	if(!setDefaultConfig()) return false;
	$sql = "UPDATE `wx_config` SET `appid`='{$appid}',`appsecrect`='{$appsecrect}',`default_template`='{$template}',`first_msg`='{$first_msg}',`url`='{$url}',`ex_msg`='$ex_msg' WHERE 1";
	if(!mysql_query($sql)) return false;
	return true;
}

//获取参数，读取
//返回关联数组
//包括id,appid，appsecrect，default_template，first_msg，url，ex_msg，access_token,createtime
function getConfig()
{
	if(!setDefaultConfig()) return false;
	$sql = "select * from wx_config where 1";
	$result = mysql_query($sql);
	return mysql_fetch_array($result,MYSQL_ASSOC);
}

//存储accesstoken
//return:false-数据库出错，3:appid出错；2：accesstoken过期;accesstoken：正确
//$access_token 为false或默认时，默认读取数据库token并返回；
//              为具体token时，存储到数据库；
function restoreToken($appid,$access_token = false)
{
	$createtime=mktime();
	if(!setDefaultConfig()) return false;
	if($access_token != false){
		$sql = "UPDATE `wx_config` SET `access_token`='$access_token',createtime='$createtime' WHERE appid='$appid'";
		$result = mysql_query($sql);
		if(!$result) return false;
		if(mysql_num_rows($result)<1) return 3;//appid有没有写错？
	}else{
		$sql = "select * from wx_config where appid='$appid'";
		$result = mysql_query($sql);
		if(!$result) return false;
		if(mysql_num_rows($result)<1) return 3;//appid有没有写错？
		$token = mysql_fetch_array($result,MYSQL_ASSOC);
		$createtime = $token['createtime'];
		$access_token = $token['access_token'];
		if(($token['createtime'] - mktime() + EXP_TIME)<100) return 2;
	}
	return array(
			'access_token'=>$access_token,
			'createtime'=>$createtime
	);
}