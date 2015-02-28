<?php
//namespace Org\Weixin;
/**
 * Created by PhpStorm.
 * User: StandOpen
 * Date: 15-1-7
 * Time: 9:41
 2015-02-26:增加了处理accesstoken过期的方法；
			增加了本地存储token的方法；并加密token
 2015-02-27:增加远程获取用户openid和信息并更新和存储到数据库
 */
include 'config.php';
class OrderPush
{
    protected $appid;
    protected $secrect;
    protected $accessToken;

    function  __construct()
    {
		$config = getConfig();
        $this->appid = $config['appid'];
        $this->secrect = $config['appsecrect'];
        $this->accessToken = $this->getToken($config['appid'], $config['appsecrect']);
//		$this->accessToken = 'ARPGI-zbLWoJWTgpambLcjz13TkaocEuStaZ-ijWr5yafguNf5zJc-qZfM1_b8OXGLzP4e0N1MOWWbgXpgBH2xiteXQugkUoJwb0Wgde8tE';
    }

    /**
     * 发送post请求
     * @param string $url
     * @param string $param
     * @return bool|mixed
     */
    function request_post($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch); //运行curl
        curl_close($ch);
        return $data;
    }


    /**
     * 发送get请求
     * @param string $url
     * @return bool|mixed
     */
    function request_get($url = '')
    {
        if (empty($url)) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * @param $appid
     * @param $appsecret
     * @return mixed
     * 获取token
     */
    protected function getToken($appid, $appsecret,$reset=false)
    {
	//此处需要判断上一个access token 有没有过期，如果没过期，就用之前的；如果过期了，就往下走
		$token=restoreToken($appid);
		if (($token !=2) && ($reset==false)) {
            $access_token = $token['access_token'];
		}else{
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
			$token = $this->request_get($url);
			$token = json_decode(stripslashes($token));
			$arr = json_decode(json_encode($token), true);
			$access_token = $arr['access_token'];
			
			restoreToken($appid,$access_token);//此处需要记录access token的时间
		}
        return $access_token;
    }
    /**
     * 存储token ，并且返回token
	 * 如果token=0，则返回0，否则，存储token到数据库
	 * 存储关系是，appid，token，createtime(unixtime)，exptime
	 * 加密accesstoken，本地存储为文本文件
    
    protected function restoreToken($appid,$access_token=false,$exptime=7200)
    {
		$key = "051235s7hJyfzVz6JLDGON35sNI7hT5nUllCL2-5pxDUd90kzoBspu9ckZOGK35sNI7hOIclPXnAPxWB1V0VT82E3raRoQ35sNI7hwU-QjL1JCXhkSlLwK4Z2GIsq93AHig2150";//静态密钥；
		if($access_token){//存储
			$access_token = $key^$access_token;//加密存储
			$time = mktime();
			$text = $appid."\r\n".$access_token."\r\n".$time;
			if ($fp = fopen('accesstoken.txt', "w")) {
                if (@fwrite($fp, $text)) {
                    fclose($fp);
				}
			}
		}else{//读取
			if(false == ($text = @file_get_contents('http://wx.szsmk.com/wechat/tplmsg/accesstoken.txt')))
			return false;
			if(strlen($text)<100) return false; //太短，就抛弃掉吧
			$text = explode("\r\n", $text); //格式化成数组
			if(count($text)!= 3) return false; //三个
			if($appid != $text[0]) return false; //appid 是否和设置的一样？
			$exptime = $text[2] - mktime() + $exptime;
			if($exptime <100) return false; //有没有超过accesstoken的超期时间
			$access_token = array('appid'=>$text[0],
								'access_token'=>$text[1]^$key,
								'exptime'=>$exptime);
		}
		
		return $access_token;
    }
*/
	    /**
     * 重置access token
     */
    public function resetToken()
    {
		
		$this->accessToken = $this->getToken($this->appid,$this->secrect,true);
		return $this->accessToken;
    }
    /**
     * 发送自定义的模板消息
     * @param $touser
     * @param $template_id
     * @param $url
     * @param $data
     * @param string $topcolor
     * @return bool
     */
    public function doSend($touser, $template_id, $url, $data, $topcolor = '#000000')
    {

        /*
         * data=>array(
                'first'=>array('value'=>urlencode("您好,您已购买成功"),'color'=>"#743A3A"),
                'name'=>array('value'=>urlencode("商品信息:微时代电影票"),'color'=>'#EEEEEE'),
                'remark'=>array('value'=>urlencode('永久有效!密码为:1231313'),'color'=>'#FFFFFF'),
            )
         */
        $template = array(
            'touser' => $touser,
            'template_id' => $template_id,
            'url' => $url,
            'topcolor' => $topcolor,
            'data' => $data
        );
        $json_template = json_encode($template);
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->accessToken;
        $dataRes = $this->request_post($url, urldecode($json_template));
		return json_decode($dataRes,true);
    }

	
	
	/*
	*根据openid获取用户信息；
	*返回包括微信定义的数据，详细见这里：http://mp.weixin.qq.com/wiki/0/d0e07720fc711c02a3eab6ec33054804.html
	*主要使用的是：subscribe，nickname，sex，headimgurl，subscribe_time，unionid，openid，city，province，
	*/
	public function getRemoteUser($weixin_openid){
		$weixin_robot_access_token = $this->accessToken;
		
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$weixin_robot_access_token.'&openid='.$weixin_openid;

		$response = $this->request_get($url);
		//卧槽！还可能有非法字符干扰json？？
		$response = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.'|[\x00-\x7F][\x80-\xBF]+'. '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'. '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S','?', $response );
		$weixin_user = json_decode($response,true);
		if(isset($weixin_user['errcode'])){
			return false;
		}
		$weixin_user['last_update'] = mktime();
		if(null == $weixin_user) return false;
		return $weixin_user;
	}
	
	/**
	*	拉取所有用户的openid
	*	返回一个数组，里面只有openid
	*	update:直接写到库里
	*/
	public function getUserList(){
		$weixin_robot_access_token = $this->accessToken;	
		$url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$weixin_robot_access_token;
		$response = $this->request_get($url);
		$weixin_user_list = json_decode($response,true);	
		if(isset($weixin_user_list['errcode'])){
			return $weixin_user_list['errcode'];
		}
		//判断有多少人？
		if($weixin_user_list['total']<=10000){
//			return $weixin_user_list['data']['openid'];
			$i = 1;
			$openid[0] = $weixin_user_list['data']['openid'];
		}else{
			$openid[0] = $weixin_user_list['data']['openid'];
			$k = floor($weixin_user_list['total'] / 10000)+1;
			for($i=1;$i<$k;$i++){
				$next_openid = $weixin_user_list['next_openid'];
				$url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$weixin_robot_access_token.'&next_openid='.$next_openid;
				$response = $this->request_get($url);
				$weixin_user_list = json_decode($response,true);
				if(isset($weixin_user_list['errcode'])){return false;}
				$openid[$i] = array_merge($openid[$i-1],$weixin_user_list['data']['openid']);
			}
//				return $openid[$i-1];

		}
						//索性先写入数据库吧！
				foreach($openid[$i-1] as $s_openid){
					$sql = "select * from wx_users where openid='$s_openid'";
					if(mysql_num_rows(mysql_query($sql))<1){
					$sql = "INSERT INTO `wx_users`(`openid`, `subscribe`) VALUES ('$s_openid',1)";
					if(!mysql_query($sql)) return false;
					}
				}
				return true;
	}
	
	/*
	批量更新数据库，如果是订阅客户，且库内有记录，则update该行所有信息；20000秒内不更新
	返回更新到的id，一次更新500个
	*/
	public function updateUserList($start=0,$numbers = 450){
		$sql = "select * from wx_users where 1 order by id desc limit 0,1";
		$count = mysql_fetch_array(mysql_query($sql),MYSQL_ASSOC)['id'];
		$timesup = 0;
		$idstop = 0;
		for($i=$start;$i<=$count;$i++)
		{
			$sql = "select * from wx_users where id=".$i;
			$result = mysql_query($sql);
			if(mysql_num_rows($result)>0){
				$sql_info = mysql_fetch_array($result,MYSQL_ASSOC);
				if(mktime() - $sql_info['last_update'] >2592000){
					//更新代码
					$openid = $sql_info['openid'];
					$user_info = $this->getRemoteUser($openid);
					$timesup++;
					if($user_info != false){
						if($user_info['subscribe'] == 1){//订阅客户，不管3721，update和插入了再说；
							$nickname = $user_info['nickname'];
							$sex = $user_info['sex'];
							$language = $user_info['language'];
							$city = $user_info['city'];
							$province = $user_info['province'];
							$country = $user_info['country'];
							$headimgurl = $user_info['headimgurl'];
							$subscribe_time = $user_info['subscribe_time'];
							$unionid = $user_info['unionid'];
							$last_update = $user_info['last_update'];
							$sql = "UPDATE `wx_users` SET `nickname`='$nickname',`subscribe_time`='$subscribe_time',`sex`='$sex',`city`='$city',`country`='$country',`province`='$province',`language`='$language',`headimgurl`='$headimgurl',`unionid`='$unionid',`last_update`='$last_update' WHERE openid= '$openid'";
						}else{	//非订阅客户，那就只能先插入openid再说；
							$sql = "update wx_users set subscribe=0, last_update = '$last_update' where openid='$openid'";
						}
//						if(!mysql_query($sql)) echo "chucuole";//return false;
						mysql_query($sql);
					}else{
						var_dump($user_info);
						echo '<br>'.$openid;
					}
				}
			}
			$idstop = $i;
			if($timesup>$numbers) {
				$i = $count;
			}
		}
		return array('count'=>$idstop,
					 'timesup'=>$timesup);
	}
}