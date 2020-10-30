<?php
/*珍贵资源 请勿转卖*/
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Index_EweiShopV2Page extends MobilePage
{
	protected function getWapSet()
	{
		global $_W;
		global $_GPC;
		$set = m('common')->getSysset(array('shop', 'wap'));
		$set['wap']['color'] = empty($set['wap']['color']) ? '#fff' : $set['wap']['color'];
		$params = array();

		if (!empty($_GPC['mid'])) {
			$params['mid'] = $_GPC['mid'];
		}

		if (!empty($_GPC['backurl'])) {
			$params['backurl'] = $_GPC['backurl'];
		}

		$set['wap']['loginurl'] = mobileUrl('account/login', $params);
		$set['wap']['regurl'] = mobileUrl('account/register', $params);
		$set['wap']['forgeturl'] = mobileUrl('account/forget', $params);
		return $set;
	}

	public function login()
	{
		global $_W;
		global $_GPC;
//		if (is_weixin() || !empty($_GPC['__ewei_shopv2_member_session_' . $_W['uniacid']])) {
//			header('location: ' . mobileUrl());
//		}

		if ($_W['ispost']) {
			$mobile = trim($_GPC['mobile']);
			$pwd = trim($_GPC['pwd']);
			$member = pdo_fetch('select id,openid,mobile,pwd,salt,isblack from ' . tablename('ewei_shop_member') . ' where mobile=:mobile and mobileverify=1 and uniacid=:uniacid limit 1', array(':mobile' => $mobile, ':uniacid' => $_W['uniacid']));

			if (empty($member)) {
				show_json(0, '用户不存在');
			}

			if ($member['isblack']==1) {
				show_json(0, '用户正在审核中');
			}

			if (md5($pwd . $member['salt']) !== $member['pwd']) {
				show_json(0, '用户或密码错误');
			}

			m('account')->setLogin($member);
			show_json(1, '登录成功');
		}

		$set = $this->getWapSet();
		$backurl = '';

		if (!empty($_GPC['backurl'])) {
			$backurl = $_W['siteroot'] . 'app/index.php?' . base64_decode(urldecode($_GPC['backurl']));
		}

		$wapset = $_W['shopset']['wap'];
		$sns = $wapset['sns'];
		include $this->template('login', NULL, true);
	}

	public function register()
	{
		$this->rf(0);
	}

	public function forget()
	{
		$this->rf(1);
	}

	protected function rf($type)
	{
		global $_W;
		global $_GPC;
//		if (is_weixin() || !empty($_GPC['__ewei_shopv2_member_session_' . $_W['uniacid']])) {
//			header('location: ' . mobileUrl());
//		}

		if ($_W['ispost']) {
			$mobile = trim($_GPC['mobile']);
			$verifycode = trim($_GPC['verifycode']);
			$pwd = trim($_GPC['pwd']);
			$email = trim($_GPC["email"]);
			$wechatname = trim($_GPC["wechatname"]);
			$province = trim($_GPC["province"]);
			$city = trim($_GPC["city"]);
			$area = trim($_GPC["area"]);
			$shopname = trim($_GPC["shopname"]);
			$parent = trim($_GPC["parent"]);
			$sub = trim($_GPC["sub"]);
			$child = trim($_GPC["child"]);
			$fans = trim($_GPC["fans"]);
			$address = trim($_GPC["address"]);

			if (empty($mobile)) {
				show_json(0, '请输入正确的手机号123');
			}

			if (empty($verifycode)) {
				show_json(0, '请输入验证码');
			}

			if (empty($pwd)) {
				show_json(0, '请输入密码');
			}
			if (empty($email)) {
				show_json(0, "请输入邮箱");
			}
			if (empty($wechatname)) {
				show_json(0, "请输入微信名");
			}
			if (empty($province)) {
				show_json(0, "请选择省份");
			}
			if (empty($city)) {
				show_json(0, "请选择市/区");
			}
			if (empty($area)) {
				show_json(0, "请选择区");
			}
			if (empty($shopname)) {
				show_json(0, "请选择店铺名字");
			}
			if (empty($parent)) {
				show_json(0, "请选择店铺类型");
			}
			if (empty($sub)) {
				show_json(0, "请选择店铺类型");
			}
			if (empty($child)) {
				show_json(0, "请选择店铺类型");
			}
			if ($parent == 2 && $sub == 6) {
				if (empty($fans)) {
					show_json(0, "请输入粉丝数量");
				}
			}
			if (empty($address) && $parent == 1 && $sub != 1) {
				show_json(0, "请输入店铺地址");
			}

			$path  = IA_ROOT."/addons/ewei_shopv2/static/images/qualification/";
			if(!is_dir($path)){
				load()->func('file');
				mkdirs($path,'0777');
			}
			$filename = $_FILES['file']['name'];
			$tmpname = $_FILES['file']['tmp_name'];
			if(empty($tmpname)){
				show_json(0, "请上传商家资质");
			}
			$ext =  strtolower( pathinfo($filename, PATHINFO_EXTENSION) );
			if($ext!='jpg' && $ext!='image/jpeg' && $ext!='png'){
				show_json(0, "请上传 jpg 或 jpeg  png 格式的图片文件!");
			}

			$file = time().$_W['uniacid'].".".$ext;
			$uploadfile = $path.$file;
			$result  = move_uploaded_file($tmpname,$uploadfile);
			if(!$result){
				show_json(0,'上传图片文件失败, 请重新上传!');
			}

			$key = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $mobile;
			if (!isset($_SESSION[$key]) || $_SESSION[$key] !== $verifycode || !isset($_SESSION['verifycodesendtime']) || $_SESSION['verifycodesendtime'] + 600 < time()) {
				show_json(0, '验证码错误或已过期!');
			}

			$member = pdo_fetch('select id,openid,mobile,pwd,salt from ' . tablename('ewei_shop_member') . ' where mobile=:mobile and mobileverify=1 and uniacid=:uniacid limit 1', array(':mobile' => $mobile, ':uniacid' => $_W['uniacid']));

			if (empty($type)) {
				if (!empty($member)) {
					show_json(0, '此手机号已注册, 请直接登录');
				}

				$salt = empty($member) ? '' : $member['salt'];

				if (empty($salt)) {
					$salt = m('account')->getSalt();
				}

				$openid = empty($member) ? '' : $member['openid'];
				$nickname = empty($member) ? '' : $member['nickname'];

				if (empty($openid)) {
					$openid = 'wap_user_' . $_W['uniacid'] . '_' . $mobile;
					$nickname = substr($mobile, 0, 3) . 'xxxx' . substr($mobile, 7, 4);
				}

				$data = array(
					'uniacid' => $_W['uniacid'],
					'mobile' => $mobile,
					'nickname' => $nickname,
					'openid' => $openid,
					'pwd' => md5($pwd . $salt),
					'salt' => $salt,
					'createtime' => time(),
					'mobileverify' => 1,
					'comefrom' => 'mobile',
					"email"=>$email,
					"weixin"=>$wechatname,
					"province"=>$province,
					"city"=>$city,
					"area"=>$area,
					"shopname"=>$shopname,
					"shoptype"=>$parent.','.$sub.','.$child,
					"fans"=>$fans,
					"shop_qualification"=>$file,
					"shop_address"=>$address,
				);
			}
			else {
				if (empty($member)) {
					show_json(0, '此手机号未注册');
				}

				$salt = m('account')->getSalt();
				$data = array('salt' => $salt, 'pwd' => md5($pwd . $salt));
			}

			if (empty($member)) {
				pdo_insert('ewei_shop_member', $data);

				if (method_exists(m('member'), 'memberRadisCountDelete')) {
					m('member')->memberRadisCountDelete();
				}
			}
			else {
				pdo_update('ewei_shop_member', $data, array('id' => $member['id']));
			}

			if (p('commission')) {
				p('commission')->checkAgent($openid);
			}

			unset($_SESSION[$key]);
			show_json(1, empty($type) ? '注册成功，请等待审核员审核！' : '密码重置成功');
		}

		$sendtime = $_SESSION['verifycodesendtime'];
		if (empty($sendtime) || $sendtime + 60 < time()) {
			$endtime = 0;
		}
		else {
			$endtime = 60 - (time() - $sendtime);
		}

		$set = $this->getWapSet();
		include $this->template('rf', NULL, true);
	}

	public function logout()
	{
		global $_W;
		global $_GPC;
		$key = '__ewei_shopv2_member_session_' . $_W['uniacid'];
		isetcookie($key, false, -100);
		header('location: ' . mobileUrl());
		exit();
	}

	public function sns()
	{
		global $_W;
		global $_GPC;
//		if (is_weixin() || !empty($_GPC['__ewei_shopv2_member_session_' . $_W['uniacid']])) {
//			header('location: ' . mobileUrl());
//		}

		$sns = trim($_GPC['sns']);
		if ($_W['ispost'] && !empty($sns) && !empty($_GPC['openid'])) {
			m('member')->checkMemberSNS($sns);
		}

		if ($_GET['openid']) {
			if ($sns == 'qq') {
				$_GET['openid'] = 'sns_qq_' . $_GET['openid'];
			}

			if ($sns == 'wx') {
				$_GET['openid'] = 'sns_wx_' . $_GET['openid'];
			}

			m('account')->setLogin($_GET['openid']);
		}

		$backurl = '';

		if (!empty($_GPC['backurl'])) {
			$backurl = $_W['siteroot'] . 'app/index.php?' . base64_decode(urldecode($_GPC['backurl']));
		}

		$backurl = empty($backurl) ? mobileUrl(NULL, NULL, true) : trim($backurl);
		header('location: ' . $backurl);
	}

	public function verifycode()
	{
		global $_W;
		global $_GPC;
		@session_start();
		$set = $this->getWapSet();
		$mobile = trim($_GPC['mobile']);
		$temp = trim($_GPC['temp']);
		$imgcode = trim($_GPC['imgcode']);

		if (empty($mobile)) {
			show_json(0, '请输入手机号');
		}

		if (empty($temp)) {
			show_json(0, '参数错误');
		}

		if (!empty($_SESSION['verifycodesendtime']) && time() < $_SESSION['verifycodesendtime'] + 60) {
			show_json(0, '请求频繁请稍后重试');
		}

		if (!empty($set['wap']['smsimgcode'])) {
			if (empty($imgcode)) {
				show_json(0, '请输入图形验证码');
			}

			$key = function_exists('complex_authkey') ? complex_authkey() : $_W['config']['setting']['authkey'];
			$imgcodehash = md5(strtolower($imgcode) . $key);

			if ($imgcodehash != trim($_GPC['__code'])) {
				show_json(-1, '请输入正确的图形验证码');
			}
		}

		$member = pdo_fetch('select id,openid,mobile,pwd,salt from ' . tablename('ewei_shop_member') . ' where mobile=:mobile and mobileverify=1 and uniacid=:uniacid limit 1', array(':mobile' => $mobile, ':uniacid' => $_W['uniacid']));
		if ($temp == 'sms_forget' && empty($member)) {
			show_json(0, '此手机号未注册');
		}

		if ($temp == 'sms_reg' && !empty($member)) {
			show_json(0, '此手机号已注册，请直接登录');
		}

		$sms_id = $set['wap'][$temp];

		if (empty($sms_id)) {
			show_json(0, '短信发送失败(NOSMSID)');
		}

		$key = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $mobile;
		@session_start();
		$code = random(5, true);
		$shopname = $_W['shopset']['shop']['name'];
		$ret = array('status' => 0, 'message' => '发送失败');

		if (com('sms')) {
			$ret = com('sms')->send($mobile, $sms_id, array('验证码' => $code, '商城名称' => !empty($shopname) ? $shopname : '商城名称'));
		}

		if ($ret['status']) {
			$_SESSION[$key] = $code;
			$_SESSION['verifycodesendtime'] = time();
			show_json(1, '短信发送成功');
		}

		show_json(0, $ret['message']);
	}
}

?>
