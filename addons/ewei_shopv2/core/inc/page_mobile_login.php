<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class MobileLoginPage extends MobilePage
{
	public function __construct()
	{
		global $_W;
		global $_GPC;
		parent::__construct();
		$m = m("member")->getMember($_W["openid"], true);
		if ($m['isblack']==1||empty($m)){
			show_message('请您重新登录！',mobileUrl('account/login'));
		}
	}
}

?>
