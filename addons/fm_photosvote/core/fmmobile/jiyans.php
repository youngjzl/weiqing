<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
	require_once FM_CORE . 'plugin/jiyan/lib/class.geetestlib.php';
	//$rvote = pdo_fetch("SELECT codekey,codekeykey FROM ".tablename($this->table_reply_vote)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
	define("CAPTCHA_ID", $rvote['codekey']);
	define("PRIVATE_KEY", $rvote['codekeykey']);
	session_start();
	$GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);


	$data = array(
        "user_id" => $_SESSION['user_id'], # 网站用户id
        "client_type" => "h5", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
        "ip_address" => getip() # 请在此处传输用户请求验证时所携带的IP
    );

	if ($_SESSION['gtserver'] == 1) {   //服务器正常
	    $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
	//print_r($result);
	    if ($result) {
	        echo '{"status":"success"}';
	    } else{
	        echo '{"status":"fail"}';
	    }
	}else{  //服务器宕机,走failback模式
	    if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
	        echo '{"status":"success"}';
	    }else{
	        echo '{"status":"fail"}';
	    }
	}

	/**$user_id = $_SESSION['user_id'];

	if ($_SESSION['gtserver'] == 1) {
	    $result = $GtSdk->success_validate($_GPC['geetest_challenge'], $_GPC['geetest_validate'], $_GPC['geetest_seccode'], $user_id);
	    if ($result) {
	    	$fmdata = array(
				"success" => 1,
				"msg" => 'yesg',
			);
	       echo json_encode($fmdata);
			exit;
	    } else{
	    	$fmdata = array(
				"success" => -1,
				"msg" => 'nog',
			);
	       echo json_encode($fmdata);
			exit;
	    }
	}else{
	    if ($GtSdk->fail_validate($_GPC['geetest_challenge'],$_GPC['geetest_validate'],$_GPC['geetest_seccode'])) {

	    	$fmdata = array(
				"success" => 1,
				"msg" => 'yes',
			);
	       echo json_encode($fmdata);
			exit;
	    }else{

	    	$fmdata = array(
				"success" => -1,
				"msg" => 'no',
			);
	       echo json_encode($fmdata);
			exit;
	    }
	}**/
