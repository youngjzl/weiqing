<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
define('IN_GW', true);

load()->model('user');
load()->model('message');
//load()->classs('oauth2/oauth2client');
load()->model('setting');
load()->func('global');

/*if (!empty($_W['uid']) && 'bind' != $_GPC['handle_type']) {
	message('请先退出再登录！');
}*/
if (checksubmit() || $_W['isajax']) {
	_login($_GPC['referer']);
}

/*$support_login_types = OAuth2Client::supportThirdLoginType();
if (in_array($_GPC['login_type'], $support_login_types)) {
	_login($_GPC['referer']);
}*/

$setting = $_W['setting'];
$_GPC['login_type'] = !empty($_GPC['login_type']) ? $_GPC['login_type'] : (!empty($_W['setting']['copyright']['mobile_status']) ? 'mobile' : 'system');

$login_urls = user_support_urls();
template('user/login');

function _login($forward = '') {
	global $_GPC, $_W;
	if (empty($_GPC['login_type'])) {
		$_GPC['login_type'] = 'system';
	}

	if (empty($_GPC['handle_type'])) {
		$_GPC['handle_type'] = 'login';
	}

/*	if ('login' == $_GPC['handle_type']) {
		$member = OAuth2Client::create($_GPC['login_type'], $_W['setting']['thirdlogin'][$_GPC['login_type']]['appid'], $_W['setting']['thirdlogin'][$_GPC['login_type']]['appsecret'])->login();
	} else {
		$member = OAuth2Client::create($_GPC['login_type'], $_W['setting']['thirdlogin'][$_GPC['login_type']]['appid'], $_W['setting']['thirdlogin'][$_GPC['login_type']]['appsecret'])->bind();
	}*/

	if (!empty($_W['user']) && '' != $_GPC['handle_type'] && 'bind' == $_GPC['handle_type']) {
		if (is_error($member)) {
			message($member['message'], url('user/profile/bind'), '');
		} else {
			message('绑定成功', url('user/profile/bind'), '');
		}
	}

	if (is_error($member)) {
		message($member['message'], url('user/login'), '');
	}

global $_GPC, $_W;
	$member = array();
	$username = trim($_GPC['username']);
	if (empty($username)) {
		message('请输入要登录的用户名');
	}
	$member['username'] = $username;
	$member['password'] = $_GPC['password'];
	if(empty($member['password'])) {
		message('请输入密码');
	}
	$record = user_single($member);
	//var_dump($record);die;
	$failed = pdo_get('users_failed_login', array('username' => trim($username)));
	if (!empty($record)) {
		if (USER_STATUS_CHECK == $record['status'] || USER_STATUS_BAN == $record['status']) {
			message('您的账号正在审核或是已经被系统禁止，请联系网站管理员解决?', url('user/login'), '');
		}
		$_W['uid'] = $record['uid'];
		$_W['isfounder'] = user_is_founder($record['uid']);
		$_W['user'] = $record;
	//	$_W['attachurl_remote']='https://img.b-ke.cn/';

/*		$support_login_bind_types = Oauth2CLient::supportThirdLoginBindType();
		if (in_array($_GPC['login_type'], $support_login_bind_types) && !empty($_W['setting']['copyright']['oauth_bind']) && !$record['is_bind'] && empty($_W['isfounder']) && (USER_REGISTER_TYPE_QQ == $record['register_type'] || USER_REGISTER_TYPE_WECHAT == $record['register_type'])) {
			message('您还没有注册账号，请前往注册', url('user/third-bind/bind_oauth', array('uid' => $record['uid'], 'openid' => $record['openid'], 'register_type' => $record['register_type'])));
			exit;
		}*/

		if (!empty($_W['siteclose']) && empty($_W['isfounder'])) {
			message('站点已关闭，关闭原因:' . $_W['setting']['copyright']['reason'], '', '');
		}
		$account = pdo_fetch("SELECT * FROM " . tablename("cjdc_account") . " WHERE status=2 AND uid=:uid ORDER BY id DESC LIMIT 1", array(':uid' => $record['uid']));
		if (!empty($account)) {
			$storeid = $account['storeid'];
			$_W['uniacid'] = $account['weid'];
		} else {
			message('您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！!!');
		}


		$cookie = array();
		$cookie['uid'] = $record['uid'];
		$cookie['lastvisit'] = $record['lastvisit'];
		$cookie['lastip'] = $record['lastip'];
		$cookie['hash'] = !empty($record['hash']) ? $record['hash'] : md5($record['password'] . $record['salt']);
		$cookie['rember'] = safe_gpc_int($_GPC['rember']);
		$session = authcode(json_encode($cookie), 'encode');
		$autosignout = (int)$_W['setting']['copyright']['autosignout'] > 0 ? (int)$_W['setting']['copyright']['autosignout'] * 60 : 0;
		isetcookie('__session', $session, !empty($_GPC['rember']) ? 7 * 86400 : $autosignout, true);
		$status = array();
		$status['uid'] = $record['uid'];
		$status['lastvisit'] = TIMESTAMP;
		$status['lastip'] = CLIENT_IP;
		user_update($status);
		//isetcookie('storeAccountName', $account['name']);
		//isetcookie('storeAccountId', $account['id']);
		//setcookie('storeAccountId', $account['id']);
		//$_SESSION["storeAccountId"] = $account['id'];
		//$_SESSION["storeAccountName"] = $account['name'];
		//$_W['storeAccountId']=$account['id'];
		$role = uni_permission($record['uid'], $_W['uniacid']);
		isetcookie('__uniacid', $_W['uniacid'], 7 * 86400);
		//isetcookie('__uid', $record['uid'], 7 * 86400);

		if (empty($forward)) {
			$forward = user_login_forward($_GPC['forward']);
		}
				$forward = safe_gpc_url($forward);

/*		if ($record['uid'] != $_GPC['__uid']) {
			isetcookie('__uniacid', '', -7 * 86400);
			isetcookie('__uid', '', -7 * 86400);
		}*/
		if (!empty($failed)) {
			pdo_delete('users_failed_login', array('id' => $failed['id']));
		}
		$user_endtime = $_W['user']['endtime'];
		if (!empty($user_endtime) && !in_array($user_endtime, array(USER_ENDTIME_GROUP_EMPTY_TYPE, USER_ENDTIME_GROUP_UNLIMIT_TYPE)) && $user_endtime < TIMESTAMP) {
			$user_is_expired = true;
		}

		if ((empty($_W['isfounder']) || user_is_vice_founder()) && $user_is_expired) {
			$user_expire = setting_load('user_expire');
			$user_expire = !empty($user_expire['user_expire']) ? $user_expire['user_expire'] : array();
			$notice = !empty($user_expire['notice']) ? $user_expire['notice'] : '您的账号已到期，请前往商城购买续费';
			$redirect = !empty($user_expire['status_store_redirect']) && 1 == $user_expire['status_store_redirect'] ? url('home/welcome/ext', array('m' => 'store')) : '';
			$extend_buttons = array();
			if (!empty($user_expire['status_store_button']) && 1 == $user_expire['status_store_button']) {
				$extend_buttons['status_store_button'] = array(
					'url' => url('home/welcome/ext', array('m' => 'store')),
					'class' => 'btn btn-primary',
					'title' => '去商城续费',
				);
			}
			$extend_buttons['cancel'] = array(
				'url' => url('user/profile'),
				'class' => 'btn btn-default',
				'title' => '取消',
			);
			message($notice, $redirect, 'expired', '', $extend_buttons);
		}
		//cache_build_frame_menu();
		//	message("欢迎回来，{$record['username']}", $forward, 'success');
		//echo url('site/entry/stores', array('m' => 'yb_cy', 'storeId' => $storeId, 'uid' => $record['uid'], 'do' => 'storeindex'));die;
		//echo $_W['uid'];die;
		//pdo_update("cjdc_account", $data, array('id' => $record['id']));
		message("欢迎回来，{$record['username']}！", url('site/entry/stores', array('m' => 'zh_cjdianc', 'id' => $storeid,'uid'=>$record['uid'], 'do' => 'start')), 'success');
	} else {
		if (empty($failed)) {
			pdo_insert('users_failed_login', array('ip' => CLIENT_IP, 'username' => trim($_GPC['username']), 'count' => '1', 'lastupdate' => TIMESTAMP));
		} else {
			pdo_update('users_failed_login', array('count' => $failed['count'] + 1, 'lastupdate' => TIMESTAMP), array('id' => $failed['id']));
		}
		message('登录失败，请检查您输入的账号和密码', '', '');
	}
}
