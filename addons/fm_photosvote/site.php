<?php
/**
 * 本破解程序由资源邦提供
 * 资源邦www.wazyb.com
 * QQ:993424780  承接网站建设、公众号搭建、小程序建设、企业网站
 */
defined('IN_IA') or exit('Access Denied');
require IA_ROOT . '/addons/fm_photosvote/core/defines.php';
require FM_CORE . 'function/load.php';
class Fm_photosvoteModuleSite extends FmCoreC2 {
	public function __web($f_name) {
		global $_GPC, $_W;
		checklogin();

		$uniacid = $_W['uniacid'];
		$rid = intval($_GPC['rid']);
		if (!empty($rid)) {
			$r = pdo_fetch("SELECT uniacid, title, fmset,end_time  FROM " . tablename($this -> table_reply) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$v = pdo_fetch("SELECT uni_all_users FROM " . tablename($this -> table_reply_vote) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			if ($v['uni_all_users'] != 1) {
				if ($uniacid != $r['uniacid']) {
					$uni = " AND uniacid = " . $uniacid;
				}
			}

			if (!empty($r['fmset']) && $r['fmset'] != 'finish' && $_GPC['do'] != 'system' && $_GPC['do'] != 'start') {
				$this->message('请完成活动设置开启活动', $this->createWebUrl('system', array('op'=>$r['fmset'], 'rid' => $rid, 'set' => $this->getset($r['fmset']))), 'success');
			}
		}

		$total_regsh_all  = $this->getregsh();
		$total_message_all  = $this->getmessage();
		$time = time();
		//$this->getsyncusers();
		$total_list = $this->getlistsum();
		$total_list_all = $this->getlistsum('2');
		$total_tem_all = $this->getlistsum('3');
		$messages = pdo_fetchall('SELECT * FROM '.tablename($this->table_bbsreply).' WHERE uniacid = '.$uniacid.' AND status != 1 ORDER BY `createtime` DESC LIMIT 6');

		include_once 'core/fmweb/' . strtolower(substr($f_name, 5)) . '.php';
	}

	public function __mobile($f_name) {
		//关健词触发页面显示。
		global $_GPC, $_W;
		$uniacid = empty($_GPC['uniacid']) ? $_W['uniacid'] : $_GPC['uniacid'];
		$rid = $_GPC['rid'];
		$cfg = $this -> module['config'];

		//活动规则
		if (!empty($rid)) {

			$fromuser = !empty($_GPC['fromuser']) ? $_GPC["fromuser"] : $_COOKIE["user_fromuser_openid"];

			//分享人
			$usersinfo = $this -> GetOauth($_GPC['do'], $uniacid, $fromuser, $rid, $_GPC['gfrom_user'], $_GPC['duli']);
			$from_user = $usersinfo['from_user'];
			$follow = $usersinfo['follow'];
			$nickname = $usersinfo['nickname'];
			$avatar = $usersinfo['avatar'];
			$sex = $usersinfo['sex'];
			$unionid = $usersinfo['unionid'];
			if ($_GPC['tfrom_user'] == 'diytfrom') {
				$_GPC['tfrom_user'] = $from_user;
			}
			$tfrom_user = !empty($_GPC['tfrom_user']) ? $_GPC['tfrom_user'] : $_COOKIE["user_tfrom_user_openid"];

			//echo $from_user;
			$getreply = $this -> GetReply($rid, $uniacid);
			$rbasic = $getreply['rbasic'];
			$rshare = $getreply['rshare'];
			$rhuihua = $getreply['rhuihua'];
			$rdisplay = $getreply['rdisplay'];
			$rvote = $getreply['rvote'];
			$rbody = $getreply['rbody'];
			//$reply = $getreply['reply'];
			$qiniu = $getreply['qiniu'];
			$now = time();




			if (!empty($cfg['skipurl'])) {
				$this->skipurl($rid,$cfg);
			}
			if ($_GPC['do'] == 'photosvote' || $_GPC['do'] == 'tuser' || $_GPC['do'] == 'tuserphotos' || $_GPC['do'] == 'des' || $_GPC['do'] == 'reg' || $_GPC['do'] == 'paihang') {
				if ($now - $rdisplay['xuninum_time'] > $rdisplay['xuninumtime']) {
					pdo_update($this -> table_reply_display, array('xuninum_time' => $now, 'xuninum' => $rdisplay['xuninum'] + mt_rand($rdisplay['xuninuminitial'], $rdisplay['xuninumending'])), array('rid' => $rid));
				}
				$yuedu = $from_user . $rid . $uniacid;
				if (time() == mktime(0, 0, 0)) {
					setcookie("user_yuedua", -10000);
				}

				if ($_COOKIE["user_yuedua"] != $yuedu) {
					pdo_update($this -> table_reply_display, array('cyrs_total' => $rdisplay['cyrs_total'] + 1, 'hits' => $rdisplay['hits'] + 1), array('rid' => $rid));
					//增加总人气
					if (!empty($tfrom_user)) {
						$user = pdo_fetch("SELECT id, from_user, hits FROM " . tablename($this -> table_users) . " WHERE from_user = :from_user and rid = $rid", array(':from_user' => $tfrom_user));
						if ($user) {
							pdo_update($this -> table_users, array('hits' => $user['hits'] + 1, ), array('rid' => $rid, 'from_user' => $tfrom_user));

						}
					}
					setcookie("user_yuedua", $yuedu, time() + 3600 * 24);
				}
			}

			if ($_GPC['do'] == 'photosvote' || $_GPC['do'] == 'tuser' || $_GPC['do'] == 'tuserphotos' || $_GPC['do'] == 'reg') {
				if ($rbasic['status'] == 0) {// 活动暂停！请稍候再试！
					$stopurl = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('stop', array('status' => '0', 'rid' => $rid));
					header("location:$stopurl");
					exit ;
				}
			}
			if ($rvote['isipv'] == 1) {
				if ($_GPC['do'] == 'photosvote' || $_GPC['do'] == 'tuser' || $_GPC['do'] == 'tuserphotos' || $_GPC['do'] == 'reg') {
					$this -> stopip($rid, $uniacid, $from_user, getip(), $_GPC['do'], $rvote['ipturl'], $rvote['limitip']);
				}
			}
		}
		//echo 'core/fmmobile/' . strtolower(substr($f_name, 8)) . '.php';exit;
		include_once 'core/fmmobile/' . strtolower(substr($f_name, 8)) . '.php';
	}

	private function templatec($templatename, $filename) {
		global $_GPC, $_W;
		$tf = 'templates/' . $templatename . '/' . $filename;
		$toye = $this -> _stopllq($tf);
		//$toye = 'style1/photosvote';
		$tmfile = FMFILE . $tf . '.html';
		if (!file_exists($tmfile) || $templatename == 'default') {
			$tf = $filename;
		}
		return $tf;
	}

	private function _stopllq($turl) {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			//$turl = 'stopllq';
			return $turl;
		} else {

			return $turl; ;
		}

	}

	public function doMobilelisthome() {
		//这个操作被定义用来呈现 微站首页导航图标
		$this -> doMobilelistentry();
	}

	public function gettiles($keyword = '') {
		global $_GPC, $_W;
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		$urls = array();
		$list = pdo_fetchall("SELECT id FROM " . tablename('rule') . " WHERE uniacid = " . $uniacid . " and module = 'fm_photosvote'" . (!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$rbasic = pdo_fetch("SELECT title FROM " . tablename($this -> table_reply) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $row['id']));
				$urls[] = array('title' => $rbasic['title'], 'url' => $_W['siteroot'] . 'app/' . $this -> createMobileUrl('photosvote', array('rid' => $row['id'])));
			}
		}
		return $urls;
	}

	//入口列表
	public function doMobilelistentry() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileStop() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileStopip() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileStopllq() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobilePhotosvote() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobilePhdatabase() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobilePhdata() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobilePhdatab() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileTuser() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileSubscribe() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileSubscribeshare() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileTvote() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileTvotestart() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileTbbs() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileTbbsreply() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileTuserphotos() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobilereg() {
		$this -> __mobile(__FUNCTION__);
	}

    public function doMobilediyqun() {
        $this -> __mobile(__FUNCTION__);
    }

	public function doMobileSaverecord() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileTreg() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobilereguser() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobilePaihang() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileDes() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileshareuserview() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileshareuserdata() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileMiaoxian() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileComment() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileCommentdata() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileCmzan() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobilePreview() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileGetuserinfo() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileTags() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileLocker() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileJiyan() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileJiyans() {
		//include_once 'core/fmmobile/jiyans.php';
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileLogin_lock() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileMember() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileTvotelist() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileJifen() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileJifenlist() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileXiaofeilist() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileGiftlist() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileAutosave() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileCharge() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileGiftvote() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileGiftsong() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileChargeend() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobileCash() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileAnswer() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileIndex() {
		$this -> __mobile(__FUNCTION__);
	}


	/**
	 *	功能 二维码创建函数；
	 * @param string $value 内容（可以是：链接、文字等）
	 * @param string $filename 文件名字
	 * @param string $pathname 路径名字
	 * @param string $errorCorrectionLevel 容错率 L M Q H
	 * @return $fileurllogo 中间带logo的二维码；
	 * @Author Fmoons
	 * @Time 2015.06.04 01:27
	 **/

	public function fm_qrcode($value = 'http://fmoons.com', $filename = '', $pathname = '', $logo = '', $scqrcode = array('errorCorrectionLevel' => 'H', 'matrixPointSize' => '4', 'margin' => '5')) {
		global $_W;
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		require_once '../framework/library/qrcode/phpqrcode.php';
		load() -> func('file');

		$filename = empty($filename) ? date("YmdHis") . '' . random(10) : date("YmdHis") . '' . random(istrlen($filename));

		if (!empty($pathname)) {
			$dfileurl = 'attachment/images/' . $uniacid . '/qrcode/cache/' . date("Ymd") . '/' . $pathname;
			$fileurl = '../' . $dfileurl;
		} else {
			$dfileurl = 'attachment/images/' . $uniacid . '/qrcode/cache/' . date("Ymd");
			$fileurl = '../' . $dfileurl;
		}
		mkdirs($fileurl);

		$fileurl = empty($pathname) ? $fileurl . '/' . $filename . '.png' : $fileurl . '/' . $filename . '.png';

		QRcode::png($value, $fileurl, $scqrcode['errorCorrectionLevel'], $scqrcode['matrixPointSize'], $scqrcode['margin']);
		if (!empty($logo)) {
			$dlogo = $_W['attachurl'] . 'headimg_' . $uniacid . '.jpg?uniacid=' . $uniacid;
			if (!$logo) {
				$logo = toimage($dlogo);
			}
			$QR = $_W['siteroot'] . $dfileurl . '/' . $filename . '.png';
			if ($logo !== FALSE) {
				$QR = imagecreatefromstring(file_get_contents($QR));
				$logo = imagecreatefromstring(file_get_contents($logo));
				$QR_width = imagesx($QR);
				$QR_height = imagesy($QR);
				$logo_width = imagesx($logo);
				$logo_height = imagesy($logo);
				$logo_qr_width = $QR_width / 5;
				$scale = $logo_width / $logo_qr_width;
				$logo_qr_height = $logo_height / $scale;
				$from_width = ($QR_width - $logo_qr_width) / 2;
				imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
			}
			if (!empty($pathname)) {
				$dfileurllogo = 'attachment/images/' . $uniacid . '/qrcode/fm_qrcode/' . date("Ymd") . '/' . $pathname;
				$fileurllogo = '../' . $dfileurllogo;
			} else {
				$dfileurllogo = 'attachment/images/' . $uniacid . '/qrcode/fm_qrcode';
				$fileurllogo = '../' . $dfileurllogo;
			}
			mkdirs($fileurllogo);
			$fileurllogo = empty($pathname) ? $fileurllogo . '/' . $filename . '_logo.png' : $fileurllogo . '/' . $filename . '_logo.png'; ;

			imagepng($QR, $fileurllogo);
			return $fileurllogo;
		}
		return $fileurl;
	}

	function downloadImage($mediaid, $filename) {
		//下载图片
		global $_W;
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		load() -> func('file');
		//$access_token = WeAccount::token();
		$account_api = WeAccount::create();
		$access_token = $account_api->getAccessToken();
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$mediaid";
		$fileInfo = $this -> downloadWeixinFile($url);
		$updir = '../attachment/images/' . $uniacid . '/' . date("Y") . '/' . date("m") . '/';
		if (!is_dir($updir)) {
			mkdirs($updir);
		}
		$filename = $updir . $filename . ".jpg";
		$this -> saveWeixinFile($filename, $fileInfo["body"]);
		return $filename;
	}

	function downloadVoice($mediaid, $filename, $savetype = 0) {
		//下载语音
		global $_W;
		load() -> func('file');
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];

		//$access_token = WeAccount::token();
		$account_api = WeAccount::create();
		$access_token = $account_api->getAccessToken();
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$mediaid";
		$fileInfo = $this -> downloadWeixinFile($url);

		$updir = '../attachment/audios/' . $uniacid . '/' . date("Y") . '/' . date("m") . '/';
		if (!is_dir($updir)) {
			mkdirs($updir);
		}
		//$key = $filename.".mp3";
		$filename = $updir . $filename . ".amr";

		$this -> saveWeixinFile($filename, $fileInfo["body"]);
		//$localfilename = $_W['siteroot'].'attachment/audios/'.$uniacid.'/'.date("Y").'/'.date("m").'/'.$key;
		//$qimedia = $this->qiniusaveWeixinFile($key , $localfilename, $fileInfo["body"], $rid);
		if ($savetype == 1) {
			return $qimedia;
		} else {
			return $filename;
		}

	}

	function downloadThumb($mediaid, $filename) {
		//下载缩略图
		global $_W;
		load() -> func('file');
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		//$access_token = WeAccount::token();
		$account_api = WeAccount::create();
		$access_token = $account_api->getAccessToken();
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$mediaid";
		$fileInfo = $this -> downloadWeixinFile($url);
		$updir = '../attachment/images/' . $uniacid . '/' . date("Y") . '/' . date("m") . '/';
		if (!is_dir($updir)) {
			mkdirs($updir);
		}
		$filename = $updir . $filename . ".jpg";
		$this -> saveWeixinFile($filename, $fileInfo["body"]);
		return $filename;
	}

	function downloadWeixinFile($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		//只取body头
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$package = curl_exec($ch);
		$httpinfo = curl_getinfo($ch);
		curl_close($ch);
		$imageAll = array_merge(array('header' => $httpinfo), array('body' => $package));
		return $imageAll;
	}

	function saveWeixinFile($filename, $filecontent) {
		$local_file = fopen($filename, 'w');
		if (false !== $local_file) {
			if (false !== fwrite($local_file, $filecontent)) {
				fclose($local_file);
			}
		}
	}

	public function doWebsendMobileQfMsg() {
		global $_GPC, $_W;
		$groupid = $_GPC['gid'];
		$id = $_GPC['id'];
		$rid = $_GPC['rid'];
		$url = urldecode($_GPC['url']);
		$uniacid = $_W['uniacid'];
		if (!empty($groupid) || $groupid <> 0) {
			$w = " AND id = '{$groupid}'";
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		if ($id != 'end') {
			$a = $item = pdo_fetch("SELECT * FROM " . tablename('site_article') . " WHERE id = :id", array(':id' => $id));
		}
		if ($groupid == -1) {

			$userinfo = pdo_fetchall("SELECT openid FROM " . tablename('mc_mapping_fans') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY updatetime DESC, fanid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('mc_mapping_fans') . " WHERE uniacid = '{$_W['uniacid']}'");
		} elseif ($groupid == -2) {

			$userinfo = pdo_fetchall("SELECT * FROM " . tablename($this -> table_users) . " WHERE uniacid = '{$_W['uniacid']}' AND rid = '{$rid}' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this -> table_users) . " WHERE uniacid = '{$_W['uniacid']}' AND rid = '{$rid}' ");
		} elseif ($groupid == -3) {

			$userinfo = pdo_fetchall("SELECT distinct(from_user) FROM " . tablename('fm_photosvote_votelog') . " WHERE uniacid = '{$_W['uniacid']}' AND rid = '{$rid}'  ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('fm_photosvote_votelog') . " WHERE uniacid = '{$_W['uniacid']}' AND rid = '{$rid}' ");
		} else {
			$userinfo = pdo_fetchall("SELECT openid FROM " . tablename('mc_mapping_fans') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY updatetime DESC, fanid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('mc_mapping_fans') . " WHERE uniacid = '{$_W['uniacid']}'");
		}
		$pager = pagination($total, $pindex, $psize);
		$fmqftemplate = pdo_fetch("SELECT fmqftemplate, endtemplate FROM " . tablename($this -> table_reply_huihua) . " WHERE rid = :rid LIMIT 1", array(':rid' => $rid));


		foreach ($userinfo as $mid => $u) {
			$from_user = $u['from_user'];
			$row = pdo_fetch("SELECT id FROM " . tablename($this -> table_qunfa) . " WHERE rid = :rid AND from_user = :from_user AND status = 1 LIMIT 1", array(':rid' => $rid,':from_user' => $from_user));

			if (!empty($row)) {
				continue;
			}

			include 'core/mtemplate/fmqf.php';

			//print_r($template_id);exit;
			if (!empty($template_id)) {
				$qfdata = array(
					'uniacid' => $uniacid,
					'rid' => $rid,
					'from_user' => $from_user,
					'type' => $groupid,
					'status' => 1,
					'createtime' => time(),
					'lasttime' => time(),
				);
				pdo_insert($this->table_qunfa, $qfdata);
				$this -> sendtempmsg($template_id, $url, $data, '#FF0000', $from_user);
			}
			if (($psize - 1) == $mid) {
				$mq = round((($pindex - 1) * $psize / $total) * 100);
				$msg = '正在发送，目前：<strong style="color:#5cb85c">' . $mq . ' %</strong>';

				$page = $pindex + 1;
				$to = $this -> createWebUrl('sendMobileQfMsg', array('gid' => $groupid, 'rid' => $rid, 'id' => $id, 'url' => $url, 'page' => $page));
				$this->message($msg, $to);
			}
		}

		$this->message('发送成功！', $this -> createWebUrl('fmqf', array('rid' => $rid)));
	}

	private function sendMobileRegMsg($from_user, $rid, $uniacid) {
		global $_GPC, $_W;
		$reply = pdo_fetch("SELECT regmessagetemplate FROM " . tablename($this -> table_reply_huihua) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));

		$userinfo = pdo_fetch("SELECT * FROM " . tablename($this -> table_users) . " WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user, ':rid' => $rid));
		include 'core/mtemplate/regvote.php';
		$url = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('tuser', array('rid' => $rid, 'from_user' => $from_user, 'tfrom_user' => $from_user));
		if (!empty($template_id)) {
			$this -> sendtempmsg($template_id, $url, $data, '#FF0000', $from_user);
		}
	}

	private function sendMobileVoteMsg($tuservote, $tousers, $template_id = '') {
		global $_GPC, $_W;
		$uniacid = $_W['uniacid'];
		$rid = $tuservote['rid'];
		$reply = pdo_fetch("SELECT title, start_time,templates FROM " . tablename($this -> table_reply) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));

		$u = pdo_fetch("SELECT uid,realname, nickname, from_user, photosnum, xnphotosnum FROM " . tablename($this -> table_users) . " WHERE from_user = :from_user AND rid = :rid", array(':from_user' => $tuservote['tfrom_user'], ':rid' => $rid));
		include 'core/mtemplate/vote.php';
		if ($reply['templates'] == 'stylebase') {
			$tdo = 'photosvote';
		} else {
			$tdo = 'tuser';
		}
		$url = $_W['siteroot'] . 'app/' . $this -> createMobileUrl($tdo, array('rid' => $rid, 'from_user' => $tousers, 'tfrom_user' => $tuservote['tfrom_user']));

		if (!empty($template_id)) {
			$this -> sendtempmsg($template_id, $url, $data, '#FF0000', $tousers);
		}

		include 'core/mtemplate/tvote.php';
		$turl = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('paihang', array('rid' => $rid, 'votelog' => '1', 'tfrom_user' => $tuservote['tfrom_user']));
		if (!empty($template_id)) {
			$this -> sendtempmsg($template_id, $turl, $tdata, '#FF0000', $tuservote['tfrom_user']);
		}
	}

	private function sendMobileHsMsg($from_user, $rid, $uniacid) {
		global $_GPC, $_W;
		$reply = pdo_fetch("SELECT title FROM " . tablename($this -> table_reply) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$replyhh = pdo_fetch("SELECT shmessagetemplate FROM " . tablename($this -> table_reply_huihua) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$userinfo = pdo_fetch("SELECT * FROM " . tablename($this -> table_users) . " WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user, ':rid' => $rid));
		include 'core/mtemplate/shenhe.php';
		$url = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('tuser', array('rid' => $rid, 'from_user' => $from_user, 'tfrom_user' => $from_user));
		if (!empty($template_id)) {
			$this -> sendtempmsg($template_id, $url, $data, '#FF0000', $from_user);
		}
	}

	private function sendMobileMsgtx($from_user, $rid, $uniacid) {
		global $_GPC, $_W;
		$msgtemplate = pdo_fetch("SELECT msgtemplate FROM " . tablename($this -> table_reply_huihua) . " WHERE rid = :rid LIMIT 1", array(':rid' => $rid));
		$msgs = pdo_fetch('SELECT from_user,tfrom_user, content,createtime FROM ' . tablename($this -> table_bbsreply) . ' WHERE rid = :rid AND from_user = :from_user  LIMIT 1', array(':rid' => $rid, ':from_user' => $from_user));

		include 'core/mtemplate/msg.php';
		//if ($reply['templates'] == 'default') {
		//	$url =  $_W['siteroot'] .'app/'.$this->createMobileUrl('tuser', array('rid' => $rid,'tfrom_user' => $msgs['tfrom_user']));
		//}else {
		$url = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('comment', array('rid' => $rid, 'tfrom_user' => $msgs['tfrom_user']));
		//}

		if (!empty($template_id)) {
			$this -> sendtempmsg($template_id, $url, $data, '#FF0000', $msgs['tfrom_user']);
		}
	}

	public function sendtempmsg($template_id, $url, $data, $topcolor, $tousers = '') {
		//load()->func('communication');
		//load()->classs('weixin.account');
		//$access_token = WeAccount::token();
		$account_api = WeAccount::create();
		$access_token = $account_api->getAccessToken();
		if (empty($access_token)) {
			return;
		}
		$postarr = '{"touser":"' . $tousers . '","template_id":"' . $template_id . '","url":"' . $url . '","topcolor":"' . $topcolor . '","data":' . $data . '}';
		$res = ihttp_post('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token, $postarr);
		return true;
	}

	public function doWebSystem() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebIndex() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebDeleteAll() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebDelete() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebMembers() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebVotemembers() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebDeletefans() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebDeletemsg() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebDeletevote() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebProvevote() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebupaudios() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebupimages() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebAddProvevote() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebVotelog() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebMessage() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebFmqf() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebAnnounce() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebAddMessage() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebIplist() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebdeletealllog() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebdeleteallmessage() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebRankinglist() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebstatus() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebBanner() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebAdv() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebGetunionid() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebTemplates() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebTags() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebUpgrade() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebFmcount() {
		$this -> __web(__FUNCTION__);
	}

	public function doWebAnswer() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebSource() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebSchool() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebCopyactivity() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebStart() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebHome() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebStatistics() {
		$this -> __web(__FUNCTION__);
	}


	####################################

	public function webmessage($error, $url = '', $errno = -1) {
		$data = array();
		$data['errno'] = $errno;
		if (!empty($url)) {
			$data['url'] = $url;
		}
		$data['error'] = $error;
		echo json_encode($data);
		exit ;
	}

}
