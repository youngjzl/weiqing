<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');

	require_once FM_CORE . 'plugin/jiyan/lib/class.geetestlib.php';
	define("CAPTCHA_ID", $rvote['codekey']);
	define("PRIVATE_KEY", $rvote['codekeykey']);
	$GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
	session_start();

	$data = array(
		"user_id" => $from_user, # 网站用户id
		"client_type" => "h5", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
		"ip_address" => getip() # 请在此处传输用户请求验证时所携带的IP
	);
	$status = $GtSdk->pre_process($data, 1);
	$_SESSION['gtserver'] = $status;
	$_SESSION['user_id'] = $data['user_id'];
	echo $GtSdk->get_response_str();