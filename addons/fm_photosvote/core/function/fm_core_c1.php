<?php
/**
 * 女神来了模块定义
 * 核心功能文件
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */

defined('IN_IA') or exit('Access Denied');
class FmCoreC1 extends Core {
	public function doWebgetqrcode() {
		global $_W,$_GPC;
		load()->func('communication');
		$rid = intval($_GPC['rid']);
		$url_send = $_W['siteroot'] . $this -> createMobileUrl('photosvote', array('rid' => $rid));
		//$url_jieguo = $this -> wxdwz($url_send);
		//http://suo.im/api.php?url=
		$url = "http://suo.im/api.php?url=".urlencode($url_send);
		$response = ihttp_get($url);
		//$result = @json_decode($response['content'], true);
		//print_r($response['content']);
		//exit;
		$surl = $response['content'];
		$qrurl = $this->fm_qrcode($surl);
		//print_r($a);
		return tomedia($qrurl);
	}
	public function wxdwz($longurl) {
		load()->func('communication');
		$token = $this->gettoken();

		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/shorturl?access_token={$token}";
		$send = array();
		$send['action'] = 'long2short';
		$send['long_url'] = $longurl;
		$response = ihttp_request($url, json_encode($send));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}");
		}
		return $result;
	}
	public function qrcodecreate($barcode) {
		$barcode = iunserializer(base64_decode($barcode));

		$barcode['expire_seconds'] = empty($barcode['expire_seconds']) ? 2592000 : $barcode['expire_seconds'];
		if (empty($barcode['action_info']['scene']['scene_id']) || empty($barcode['action_name'])) {
			return error('1', 'Invalid params');
		}

		$token = $this->gettoken();
		$response = ihttp_request("https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$token, json_encode($barcode));
		//print_r($response);
		if (is_error($response)) {
			return $response;
		}
		$content = @json_decode($response['content'], true);

		if(empty($content)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		}
		if (!empty($content['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$content['errcode']}, 错误信息: {$content['errmsg']}");
		}
		return $content;
	}

	public function getData($page) {
		global $_W, $_GPC;
		$rid = intval($_GPC['rid']);
		$tfrom_user = $_GPC['tfrom_user'];
		if (!empty($page['datas'])) {
			$data = htmlspecialchars_decode($page['datas']);
			$d = json_decode($data, true);
			$usersids = array();


			foreach ($d as $k1 => &$dd) {
				if ($dd['temp'] == 'photosvote') {
					foreach ($dd['data'] as $k2 => $ddd) {
						$usersids[] = array('id' => $ddd['usersid'], 'k1' => $k1, 'k2' => $k2);
					}
				} elseif ($dd['temp'] == 'tongji') {
					$tj = pdo_fetch("SELECT csrs_total,ljtp_total,xunips,cyrs_total,xuninum FROM ".tablename($this->table_reply_display)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
					$dd['params']['tongjicszp'] =  $tj['csrs_total'] ;
					$dd['params']['tongjiljtp'] =  $tj['ljtp_total']+$tj['xunips'];
					$dd['params']['tongjicyrs'] =  $tj['cyrs_total'] + $tj['xuninum'];
				} elseif ($dd['temp'] == 'usertongji') {
					$user = pdo_fetch("SELECT uid,photosnum,xnphotosnum,hits,xnhits FROM ".tablename($this->table_users)." WHERE rid = :rid AND from_user = :from_user ORDER BY `id` DESC", array(':rid' => $rid,':from_user' => $tfrom_user));
					$dd['params']['uid'] =  $user['uid'] ;
					$dd['params']['votes'] =  $user['photosnum'] + $user['xnphotosnum'];
					$dd['params']['hits'] =  $user['hits'] + $user['xnhits'];
					$dd['params']['giftnum'] =  $this -> getgiftnum($rid, $tfrom_user, $uni);
					//print_r($dd);
				} elseif ($dd['temp'] == 'topuser') {
					$dd['params']['username'] =  $this->getname($rid, $tfrom_user,'8');
					$dd['params']['avatar'] =  $this->getname($rid, $tfrom_user,'8', 'avatar');
					if ($dd['params']['bg'] == 1) {
						$dd['params']['bgimg'] = $this->getuserfm($rid,$tfrom_user);
					}
				} elseif ($dd['temp'] == 'userpicture') {
					$p =  pdo_fetchall("SELECT id,imgpath FROM ".tablename($this->table_users_picarr)." WHERE from_user = :from_user AND rid = :rid", array(':from_user' => $tfrom_user,':rid' => $rid));
					$dd['data'] = array();
					foreach ($p as $key => $row) {
						$dd['data'][$key]['id'] = $row['id'];
						$dd['data'][$key]['imgurl'] = $row['imgpath'];
					}
				} elseif ($dd['temp'] == 'richtext') {
					$dd['content'] = $this -> unescape($dd['content']);
				}
				if (empty($dd['params']['rid'])) {
					$dd['params']['rid'] = $rid;
				}
			}

			unset($dd);
			$arr = array();
			foreach($usersids as $a) {
				$arr[] = $a['id'];
			}
			if (count($arr) > 0) {
				$usersinfo = pdo_fetchall("SELECT id,rid,from_user,nickname,realname,uid,avatar,photosnum,hits,xnphotosnum,xnhits,sharenum FROM " . tablename($this->table_users) . " WHERE id in ( " . implode(',', $arr) . ") AND uniacid= :uniacid AND status=:status AND rid =:rid ORDER BY uid ASC", array(':uniacid' => $_W['uniacid'], ':status' => '1', ':rid' => $rid), 'id');

				$usersinfo = $this->set_medias($usersinfo, 'avatar');
				foreach ($d as $k1 => &$dd) {
					if ($dd['temp'] == 'pusers') {
						foreach ($dd['data'] as $k2 => &$ddd) {

							$cdata = $usersinfo[$ddd['usersid']];
							$ddd['name'] = $this->getname($rid, $cdata['from_user'],'8');
							$ddd['uid'] = $cdata['uid'];
							$ddd['from_user'] = $cdata['from_user'];
							$ddd['piaoshu'] = $cdata['photosnum'] + $cdata['xnphotosnum'];
							$ddd['img'] =  $this->getuserfm($rid,$tfrom_user);
							$ddd['renqi'] = $cdata['hits'] + $cdata['xnhits'];
							$ddd['sharenum'] = $cdata['sharenum'];
						}
						unset($ddd);
					}
				}
				unset($dd);
			}
			$data = json_encode($d);
			$data = rtrim($data, "]");
			$data = ltrim($data, "[");
		}
			$pageinfo = htmlspecialchars_decode($page['pageinfo']);
			$p = json_decode($pageinfo, true);
			$page_title = empty($p[0]['params']['title'])?"未设置页面标题":$p[0]['params']['title'];
			$page_desc = empty($p[0]['params']['desc'])?"未设置页面简介":$p[0]['params']['desc'];
			$page_img = empty($p[0]['params']['img'])?"":tomedia($p[0]['params']['img']);
			$page_keyword = empty($p[0]['params']['kw'])?"":$p[0]['params']['kw'];
			//print_r($p);
			$vote_anname = empty($p[0]['params']['voteanname'])? "投Ta一票" :$p[0]['params']['voteanname'];
			$vote_title = empty($p[0]['params']['votetitle'])?"未设置投票区标题":$p[0]['params']['votetitle'];
			$vote_cansaizhe = empty($p[0]['params']['cansaizhe'])?"":$p[0]['params']['cansaizhe'];
			$shopset = array('name'=>'女神来了','logo'=>'11');
			$users = $this->getMember($from_user);
			$system =  array('photosvote' => array('sharephoto'=>$rshare['sharephoto'],'shareurl'=>$rshare['shareurl'],'sharetitle'=>$rshare['sharetitle']));
			$system = json_encode($system);
			$vote = array('voteanname' => $vote_anname,'votetitle' => $vote_title,'votecansaizhe' => $vote_cansaizhe);
			$vote = json_encode($vote);
			$pageinfo = rtrim($pageinfo, "]");
			$pageinfo = ltrim($pageinfo, "[");
			$ret = array('page' => $page, 'pageinfo' => $pageinfo, 'data' => $data, 'share' => array('title' => $page_title, 'desc' => $page_desc, 'imgUrl' => $page_img), 'vote_set' => array('voteanname' => $vote_anname,'votetitle' => $vote_title,'votecansaizhe' => $vote_cansaizhe), 'footertype' => intval($p[0]['params']['footer']), 'footermenu' => intval($p[0]['params']['footermenu']), 'system' => $system, 'vote' => $vote);
			if ($p[0]['params']['footer'] == 2) {
				$menuid = intval($p[0]['params']['footermenu']);
				$menu = pdo_fetch('select * from ' . tablename('fm_photosvote_templates_designer_menu') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $menuid, ':uniacid' => $_W['uniacid']));
				if (!empty($menu)) {
					$ret['menus'] = json_decode($menu['menus'], true);
					$ret['params'] = json_decode($menu['params'], true);
				}
			}
			return $ret;

		}
	public function escape($str){
			preg_match_all("/[\xc2-\xdf][\x80-\xbf]+|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}|[\x01-\x7f]+/e", $str, $r);
			$str = $r [0];
			$l = count($str);
			for ($i = 0; $i < $l; $i++) {
				$value = ord($str [$i] [0]);
				if ($value < 223) {
					$str [$i] = rawurlencode(utf8_decode($str [$i]));
				} else {
					$str [$i] = "%u" . strtoupper(bin2hex(iconv("UTF-8", "UCS-2", $str [$i])));
				}
			}
			return join("", $str);
	}
	public function unescape($str){
			$ret = '';
			$len = strlen($str);
			for ($i = 0; $i < $len; $i++) {
				if ($str[$i] == '%' && $str[$i + 1] == 'u') {
					$val = hexdec(substr($str, $i + 2, 4));
					if ($val < 0x7f) $ret .= chr($val); else if ($val < 0x800) $ret .= chr(0xc0 | ($val >> 6)) . chr(0x80 | ($val & 0x3f)); else $ret .= chr(0xe0 | ($val >> 12)) . chr(0x80 | (($val >> 6) & 0x3f)) . chr(0x80 | ($val & 0x3f));
					$i += 5;
				} else if ($str[$i] == '%') {
					$ret .= urldecode(substr($str, $i, 3));
					$i += 2;
				} else $ret .= $str[$i];
			}
			return $ret;
	}
	public function is_array2($array) {
			if (is_array($array)) {
				foreach ($array as $k => $v) {
					return is_array($v);
				}
				return false;
			}
			return false;
	}
	public function set_medias($list = array(), $fields = null) {
			if (empty($fields)) {
				foreach ($list as &$row) {
					$row = tomedia($row);
				}
				return $list;
			}
			if (!is_array($fields)) {
				$fields = explode(',', $fields);
			}
			if ($this->is_array2($list)) {
				foreach ($list as $key => &$value) {
					foreach ($fields as $field) {
						if (is_array($value) && isset($value[$field])) {
							$value[$field] = tomedia($value[$field]);
						}
					}
				}
				return $list;
			} else {
				foreach ($fields as $field) {
					if (isset($list[$field])) {
						$list[$field] = tomedia($list[$field]);
					}
				}
				return $list;
			}
	}
	public function save_media($url) {

		return $url;
	}
	public function getGuide($system, $pageinfo){
		global $_W, $_GPC;
		if (!empty($_GPC['preview'])) {
			$guide['followed'] = '0';
		} else {
			$guide['openid2'] = $from_user;
			$guide['followed'] = $follow;
		}
		if ($guide['followed'] != '1') {
			$system = json_decode($system, true);
			$system['photosvote'] = $this->set_medias($system['photosvote'], 'sharephoto');
			$pageinfo = json_decode($pageinfo, true);
			if (!empty($_GPC['mid'])) {
				$guide['member1'] = pdo_fetch("SELECT uid,nickname,from_user,avatar FROM " . tablename($this->table_users) . " WHERE from_user=:from_user and rid= :rid limit 1 ", array(':rid' => $rid, ':from_user' => $fromuser));
			}
			$guide['shareurl'] = $system['photosvote']['shareurl'];
			if (empty($guide['member1'])) {
				$guide['title1'] = $pageinfo['params']['guidetitle1'];
				$guide['title2'] = $pageinfo['params']['guidetitle2'];
				$guide['logo'] = $system['photosvote']['sharephoto'];
			} else {
				$pageinfo['params']['guidetitle1s'] = str_replace("[邀请人]", $guide['member1']['nickname'], $pageinfo['params']['guidetitle1s']);
				$pageinfo['params']['guidetitle2s'] = str_replace("[邀请人]", $guide['member1']['nickname'], $pageinfo['params']['guidetitle2s']);
				$pageinfo['params']['guidetitle1s'] = str_replace("[访问者]", $nickname, $pageinfo['params']['guidetitle1s']);
				$pageinfo['params']['guidetitle2s'] = str_replace("[访问者]", $nickname, $pageinfo['params']['guidetitle2s']);
				$guide['title1'] = $pageinfo['params']['guidetitle1s'];
				$guide['title2'] = $pageinfo['params']['guidetitle2s'];
				$guide['logo'] = $guide['member1']['avatar'];
			}
		}
		return $guide;
	}
	public function GetFooter($id) {
		global $_W;
		$footer = array();
		$menu = pdo_fetch('select * from ' . tablename($this->table_designer_menu) . ' where id =:id LIMIT 1', array(':id' =>$id));
		$footer['menus'] = $menu['menus'];
		$footer['params'] = $menu['params'];
		return $footer;
	}
	public function GetMenuname($id) {
		$menu = pdo_fetch('select menuname from ' . tablename($this->table_designer_menu) . ' where id=:id', array(':id' => $id));
		return $menu['menuname'];
	}

	function get_share($uniacid,$rid,$from_user,$title) {

		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT xuninum,hits FROM ".tablename($this->table_reply_display)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));

			$csrs = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_users)." WHERE rid= ".$rid."");



			$listtotal = $csrs + $reply['hits'] + pdo_fetchcolumn("SELECT sum(hits) FROM ".tablename($this->table_users)." WHERE rid= ".$rid."") + pdo_fetchcolumn("SELECT sum(xnhits) FROM ".tablename($this->table_users)." WHERE rid= ".$rid."") + $reply['xuninum'];//总参与人数


			$ljtp = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_log)." WHERE rid= ".$rid."") + pdo_fetchcolumn("SELECT sum(xnphotosnum) FROM ".tablename($this->table_users)." WHERE rid= ".$rid."");//累计投票

        }
		if (!empty($from_user)) {
		    $userinfo = pdo_fetch("SELECT uid, nickname,realname FROM ".tablename($this->table_users)." WHERE rid= :rid AND from_user= :from_user", array(':rid' => $rid,':from_user' => $from_user));
			$nickname = empty($userinfo['realname']) ? $userinfo['nickname'] : $userinfo['realname'];
			$userid = $userinfo['uid'];
		}
		$str = array('#编号#'=>$userid,'#参赛人数#'=>$csrs,'#参与人数#'=>$listtotal,'#参与人名#'=>$nickname,'#累计票数#'=>$ljtp);
		$result = strtr($title,$str);
        return $result;
    }
	public function gettagname($tagid,$tagpid,$tagtid, $rid) {

		$tags = '';
		if ($tagid && $tagpid && $tagtid) {
			$tagt = pdo_fetch("SELECT title,parentid FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid", array(':id' => $tagtid, ':rid' => $rid));
			$tag = pdo_fetch("SELECT title,parentid FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid", array(':id' => $tagid, ':rid' => $rid));
			$tagf = pdo_fetch("SELECT title FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid", array(':id' => $tag['parentid'], ':rid' => $rid));
			$tags = $tagf['title'].' / '.$tag['title'].' / '.$tagt['title'];
			//return $tagf['title'].' / '.$tag['title'].' / '.$tagt['title'];
		}elseif ($tagid && $tagpid && !$tagtid) {
			$tag = pdo_fetch("SELECT title,parentid FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid", array(':id' => $tagid, ':rid' => $rid));
			$tagf = pdo_fetch("SELECT title FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid", array(':id' => $tag['parentid'], ':rid' => $rid));
			$tags =  $tagf['title'].' / '.$tag['title'];
		}elseif ( $tagpid && !$tagid && !$tagtid) {
			$tagf = pdo_fetch("SELECT title FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid", array(':id' => $tagpid, ':rid' => $rid));
			$tags =  $tagf['title'];
		}elseif (empty($tagf)) {
			$tags = '默认分组';
		}
		return $tags;


	}

	public function gettagname_tag($pid, $rid,$type='') {

		$tagname = '';

		if ($type == 'tag') {
			$tagb = pdo_fetch("SELECT title,parentid FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid", array(':id' => $pid, ':rid' => $rid));

			if (empty($tagb)) {
				$tagname .= '';
			}else{
				if (!empty($tagb['parentid'])) {
					$taga = pdo_fetch("SELECT title,parentid FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid", array(':id' => $tagb['parentid'], ':rid' => $rid));
					if (empty($taga)) {
						$tagname .= '';
					}else{
						$tagname .= $taga['title'] . ' / ';
					}
				}
				$tagname .= $tagb['title'];
			}
		}else{
			$tagc = pdo_fetch("SELECT title,parentid FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid", array(':id' => $pid, ':rid' => $rid));
			if (empty($tagc)) {
				$tagname = '默认分组';
			}else{
				if (!empty($tagc['parentid'])) {
					$tagb = pdo_fetch("SELECT title,parentid FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid", array(':id' => $pid, ':rid' => $rid));
					if (empty($tagb)) {
						$tagname = '默认分组';
					}else{
						if (!empty($tagb['parentid'])) {
							$taga = pdo_fetch("SELECT title,parentid FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid", array(':id' => $tagb['parentid'], ':rid' => $rid));
							if (empty($taga)) {
								$tagname = '默认分组';
							}else{
								$tagname .= $taga['title'] . ' / ';
							}
						}
						$tagname .= $tagb['title'] . ' / ';
					}
				}
				$tagname .= $tagc['title'];
			}
		}
		return $tagname;


	}
	public function GetPaihangcha($rid, $tfrom_user,$indexpx='') {
		global $_W;
		$date = array();
		if ($indexpx == '2') {
			$order = " ORDER BY `hits` + `xnhits` DESC, `photosnum` + `xnphotosnum` DESC";
		}else{
			$order = " ORDER BY `photosnum` + `xnphotosnum` DESC , `hits` + `xnhits` DESC ";
		}
		$ranks = pdo_fetchall('SELECT photosnum, xnphotosnum,from_user,hits,xnhits  FROM '.tablename($this->table_users).' WHERE status =1 AND rid = '.$rid.$order.'');
		//print_r($ranks);
		foreach ($ranks as $key => $value) {
			//print_r($value);
			if ($value['from_user'] == $tfrom_user) {
				$rank = $key + 1;//当前排名
				if ($indexpx == '2') {
					$piaoshu = $value['hits'] + $value['xnhits'];//当前票数
				}else{
					$piaoshu = $value['photosnum'] + $value['xnphotosnum'];//当前票数
				}
				$qkey = $key - 1;//前一名序号
				$akey = $key + 1;//后一名序号
				break;
			}
		}
		$date['rank'] = $rank;
		if ($indexpx == '2') {
			if ($rank == 1) {
				$apiaoshu  = $ranks[$akey]['hits'] + $ranks[$akey]['xnhits'];
			}else{
				$qpiaoshu  = $ranks[$qkey]['hits'] + $ranks[$qkey]['xnhits'];
				$apiaoshu  = $ranks[$akey]['hits'] + $ranks[$akey]['xnhits'];
				$date['qps'] = $qpiaoshu - $piaoshu;
			}
			$date['aps'] = $piaoshu - $apiaoshu;
		}else{
			if ($rank == 1) {
				$apiaoshu  = $ranks[$akey]['photosnum'] + $ranks[$akey]['xnphotosnum'];
			}else{
				$qpiaoshu  = $ranks[$qkey]['photosnum'] + $ranks[$qkey]['xnphotosnum'];
				$apiaoshu  = $ranks[$akey]['photosnum'] + $ranks[$akey]['xnphotosnum'];
				$date['qps'] = $qpiaoshu - $piaoshu;
			}
			$date['aps'] = $piaoshu - $apiaoshu;
		}


		return $date;
	}/**
	public function GetPaihangchaall($from_user) {
		global $_W;
		$uniacid = $_W['uniacid'];
		$date = array();
		$order = " ORDER BY `photosnum` + `xnphotosnum` DESC , `hits` + `xnhits` DESC ";

		$ranks = pdo_fetchall('SELECT photosnum, xnphotosnum,from_user,hits,xnhits  FROM '.tablename($this->table_users).' WHERE status =1 AND uniacid = '.$uniacid.$order.'');
		//print_r($ranks);
		foreach ($ranks as $key => $value) {
			//print_r($value);
			if ($value['from_user'] == $tfrom_user) {
				$rank = $key + 1;//当前排名
				if ($indexpx == '2') {
					$piaoshu = $value['hits'] + $value['xnhits'];//当前票数
				}else{
					$piaoshu = $value['photosnum'] + $value['xnphotosnum'];//当前票数
				}
				$qkey = $key - 1;//前一名序号
				$akey = $key + 1;//后一名序号
				break;
			}
		}
		$date['rank'] = $rank;
		if ($rank == 1) {
			$apiaoshu  = $ranks[$akey]['photosnum'] + $ranks[$akey]['xnphotosnum'];
		}else{
			$qpiaoshu  = $ranks[$qkey]['photosnum'] + $ranks[$qkey]['xnphotosnum'];
			$apiaoshu  = $ranks[$akey]['photosnum'] + $ranks[$akey]['xnphotosnum'];
			$date['qps'] = $qpiaoshu - $piaoshu;
		}
		$date['aps'] = $piaoshu - $apiaoshu;



		return $date;
	}**/
	public function _getip($rid, $ip, $uniacid = '') {
		global $_GPC, $_W;

		$iparrs = pdo_fetch("SELECT iparr FROM ".tablename($this->table_log)." WHERE rid = :rid and ip = :ip ", array(':rid' => $rid, ':ip' => $ip));
		$iparr = iunserializer($iparrs['iparr']);
		return $iparr;
	}
	public function isvoteok($ordersn, $rid) {
		$loguser = pdo_fetch("SELECT from_user FROM ".tablename($this->table_log)." WHERE rid = :rid and ordersn = :ordersn ORDER BY id DESC LIMIT 1", array(':rid' => $rid, ':ordersn' => $ordersn));
		return $loguser;
	}
	public function _getloguser($rid, $from_user, $tfrom_user ='', $type = '') {
		if ($type == 'all') {
			$loguser = pdo_fetchall("SELECT tfrom_user, createtime,vote_times,is_del FROM ".tablename($this->table_log)." WHERE rid = :rid and from_user = :from_user and tfrom_user = :tfrom_user ORDER BY createtime DESC", array(':rid' => $rid, ':from_user' => $from_user, ':tfrom_user' => $tfrom_user));
			foreach ($loguser as $key => $value) {
				$loguser[$key]['createtime'] = date('Y-m-d h:i:s', $value['createtime']);
			}
		}else{
			$loguser = pdo_fetch("SELECT nickname, avatar FROM ".tablename($this->table_log)." WHERE rid = :rid and from_user = :from_user ORDER BY id DESC LIMIT 1", array(':rid' => $rid, ':from_user' => $from_user));
		}
		return $loguser;
	}
	public function gettvotes($rid, $from_user, $indexpx){
		$r = array();
		$votes = pdo_fetchall("SELECT tfrom_user FROM " . tablename($this -> table_log) . " WHERE from_user = :from_user AND rid = :rid GROUP BY tfrom_user ORDER BY createtime DESC", array(':from_user' => $from_user, ':rid' => $rid));
		foreach ($votes as $key => $value) {
			$r[$key]['tfrom_user'] = $value['tfrom_user'];
			$r[$key]['tuser'] = $this->_getuser($rid, $value['tfrom_user']);
			$r[$key]['username'] = $this->getusernames($r[$key]['tuser']['realname'], $r[$key]['tuser']['nickname'], '10');
			$r[$key]['tfmimage'] = $this -> getpicarr($uniacid, $rid, $value['tfrom_user'], 1);
			$r[$key]['avatar'] = $this->getphotos($r[$key]['tfmimage']['photos'], $r[$key]['tuser']['avatar'], 'addons/fm_photosvote/static/mobile/public/images/no-avatar.png');
			$r[$key]['votenum'] = $this->getvotes($rid,$from_user, $value['tfrom_user'], 'from_user_tfrom_user');
			$r[$key]['paihang'] = $this->GetPaihangcha($rid, $value['tfrom_user'],$rvote['indexpx']);
		}
		return $r;
	}
	public function _getuser($rid, $tfrom_user, $uniacid = '') {
		global $_GPC, $_W;
		return pdo_fetch("SELECT uid, avatar, nickname, realname, sex, mobile FROM ".tablename($this->table_users)." WHERE rid = :rid and from_user = :tfrom_user ", array(':rid' => $rid, ':tfrom_user' => $tfrom_user));
	}
	public function getMember($from_user) {
		global $_GPC, $_W;
		return pdo_fetch("SELECT * FROM ".tablename($this->table_users)." WHERE from_user = :from_user ORDER BY id DESC LIMIT 1", array(':from_user' => $from_user));
	}
	public function _auser($rid, $afrom_user, $uniacid = '') {
		global $_GPC, $_W;
		load()->model('mc');

		$auser = pdo_fetch("SELECT avatar, nickname FROM ".tablename($this->table_users)." WHERE rid = :rid and from_user = :afrom_user ", array(':rid' => $rid, ':afrom_user' => $afrom_user));
		if (empty($auser)) {
			$auser = pdo_fetch("SELECT avatar, nickname FROM ".tablename($this->table_data)." WHERE rid = :rid and from_user = :afrom_user ", array(':rid' => $rid, ':afrom_user' => $afrom_user));
			if (empty($auser)) {
				$auser = mc_fansinfo($row['afrom_user']);
			}
		}
		return $auser;
	}
	public function getsharenum($uniacid, $tfrom_user, $rid, $sharenum) {
		global $_W, $_GPC;
		return pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_data)." WHERE tfrom_user = :tfrom_user and rid = :rid", array(':tfrom_user' => $tfrom_user,':rid' => $rid)) + pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_data)." WHERE fromuser = :fromuser and rid = :rid", array(':fromuser' => $tfrom_user,':rid' => $rid)) + $sharenum;
	}
	public function gettagtitle($tagid, $rid) {
		$tags = pdo_fetch("SELECT title FROM ".tablename($this->table_tags)." WHERE rid = :rid AND id = :id ORDER BY id DESC" , array(':rid' => $rid,':id' => $tagid));
		return $tags['title'];
	}
	public function getpicarr($uniacid,$rid, $from_user,$isfm = 0) {
		if ($isfm == 1) {
			$photo = pdo_fetch("SELECT photos,photoname,imgpath FROM ".tablename($this->table_users_picarr)." WHERE from_user = :from_user AND rid = :rid AND isfm = :isfm LIMIT 1", array(':from_user' => $from_user,':rid' => $rid,':isfm' => $isfm));
		}else {
			$photo = pdo_fetch("SELECT photos,photoname,imgpath FROM ".tablename($this->table_users_picarr)." WHERE from_user = :from_user AND rid = :rid ORDER BY createtime DESC LIMIT 1", array(':from_user' => $from_user,':rid' => $rid));
		}
		return $photo;

	}
	public function getuserfm($rid, $from_user) {
		$row = pdo_fetch("SELECT photos,photoname,imgpath FROM ".tablename($this->table_users_picarr)." WHERE from_user = :from_user AND rid = :rid AND isfm = 1 LIMIT 1", array(':from_user' => $from_user,':rid' => $rid));
		$fm = tomedia($row['imgpath']);
		if (empty($fm)) {
			$fm = tomedia($this->getname($rid, $from_user,'','avatar'));
			if (empty($fm)) {
				$reply = pdo_fetch("SELECT picture FROM ".tablename($this->table_reply)." WHERE rid = :rid LIMIT 1", array(':rid' => $rid));
				$fm = tomedia($reply['picture']);
			}
		}
		return $fm;

	}

	public function getphotos($photo, $avatar, $picture, $is = '') {
		if ($is) {
			if (!empty($avatar)) {
				$photos = tomedia($avatar);
			}elseif (!empty($photo)) {
				$photos = tomedia($photo);
			}else{
				$photos = tomedia($picture);
			}
		}else {
			if (!empty($photo)) {
				$photos = tomedia($photo);
			}elseif (!empty($avatar)) {
				$photos = tomedia($avatar);
			}else{
				$photos = tomedia($picture);
			}
		}
		return $photos;
	}
	public function getusernames($realname, $nickname,  $limit = '6', $from_user = '') {
		if (!empty($realname)) {
			$name = cutstr($realname, $limit);
		}elseif (!empty($nickname)) {
			$name = cutstr($nickname, $limit);
		}elseif (!empty($from_user)) {
			$name = cutstr($from_user, $limit);
		}else{
			$name = '网友';
		}
		return $name;
	}
	public function getname($rid, $from_user, $limit = '20' , $type = 'name') {
		load()->model('mc');
		if ($type == 'avatar') {
			$username = $this->_getuser($rid, $from_user);
			$avatar = tomedia($username['avatar']);
			if (empty($avatar)) {

				$username = $this->gettpinfo($rid, $from_user);
				$avatar = tomedia($username['avatar']);
				if (empty($avatar)) {
					$username = mc_fansinfo($from_user);
					$avatar = tomedia($username['avatar']);
					if (empty($avatar)) {
						$avatar = tomedia('./addons/fm_photosvote/icon.jpg');
					}
				}
			}
			return $avatar;
		}else{

			$username = $this->_getuser($rid, $from_user);
			if (!empty($username['realname'])) {
				$name = cutstr($username['realname'], $limit);
			}else{
				$name = cutstr($username['nickname'], $limit);
			}
			if (empty($name)) {

				$username = $this->gettpinfo($rid, $from_user);
				if (!empty($username['realname'])) {
					$name = cutstr($username['realname'], $limit);
				}else{
					$name = cutstr($username['nickname'], $limit);
				}
				if (empty($name)) {
					$username = mc_fansinfo($from_user);
					$name = cutstr($username['nickname'], $limit);
					if (empty($name)) {
						$name = cutstr($from_user, $limit);
						if (empty($name)) {
							$name = '网友';
						}
					}
				}
			}
			return $name;
		}
	}
	public function isgetname($rid, $from_user) {
		load()->model('mc');

			$username = $this->_getuser($rid, $from_user);
			$avatar = tomedia($username['avatar']);
			if (empty($avatar)) {

				$username = $this->gettpinfo($rid, $from_user);
				$avatar = tomedia($username['avatar']);
				if (empty($avatar)) {
					$username = mc_fansinfo($from_user);
					$avatar = tomedia($username['avatar']);
					if (empty($avatar)) {
						$avatar = '';
					}
				}
			}

			if (!empty($username['realname'])) {
				$name = cutstr($username['realname'], $limit);
			}else{
				$name = cutstr($username['nickname'], $limit);
			}
			if (empty($name)) {

				$username = $this->gettpinfo($rid, $from_user);
				if (!empty($username['realname'])) {
					$name = cutstr($username['realname'], $limit);
				}else{
					$name = cutstr($username['nickname'], $limit);
				}
				if (empty($name)) {
					$username = mc_fansinfo($from_user);
					$name = cutstr($username['nickname'], $limit);
					if (empty($name)) {
						$name = '';
					}
				}
			}
		if (empty($avatar) && empty($name)) {
			return 'error';
		}

	}


	public function getmobile($rid, $from_user) {
		$userinfo = $this->_getuser($rid, $from_user);
		$mobile = $userinfo['mobile'];
		if (empty($mobile)) {
			$userinfo = $this->gettpinfo($rid, $from_user);
			$mobile = $userinfo['mobile'];
			if (empty($mobile)) {
				load()->model('mc');
				$userinfo = mc_fansinfo($from_user);
				$mobile = $userinfo['mobile'];
				if (empty($mobile)) {
					$mobile = '';
				}
			}
		}
		return $mobile;
	}
	public function getcommentnum($rid, $uniacid,  $from_user, $type = '0') {
		if ($type == 1) {
			$num = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_bbsreply)." WHERE rid= ".$rid."  AND ( from_user= '".$from_user."' OR tfrom_user= '".$from_user."') AND status = 1 ");//评论数
		}else{
			$num = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_bbsreply)." WHERE rid= ".$rid." AND tfrom_user= '".$from_user."' AND status = 1 ");//评论数
		}
		if (empty($num)) {
			$num = 0;
		}
		return $num;
	}
	public function getphotosnum($rid, $uniacid,  $tfrom_user) {
		$num = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_users_picarr)." WHERE rid= ".$rid." AND from_user= '".$tfrom_user."' ");//图片数
		return $num;
	}
	public function gettpinfo($rid, $from_user) {
		$tpinfo = pdo_fetch('SELECT realname, mobile,nickname,avatar FROM '.tablename($this->table_voteer).' WHERE rid= :rid AND from_user = :from_user ', array(':rid' => $rid,':from_user' => $from_user));
		return $tpinfo;
	}
	public function getuidusers($rid, $uid) {
		$tpinfo = pdo_fetch('SELECT from_user,realname, mobile,avatar FROM '.tablename($this->table_users).' WHERE rid= :rid AND uid = :uid ', array(':rid' => $rid,':uid' => $uid));
		return $tpinfo;
	}
	public function getvotes($rid, $from_user, $tfrom_user = '', $type = '') {
		if ($type == 'from_user_tfrom_user') {
			$num = pdo_fetchcolumn("SELECT SUM(vote_times) FROM ".tablename($this->table_log)." WHERE rid= ".$rid." AND from_user= '".$from_user."' AND tfrom_user= '".$tfrom_user."' ");//投票数
		}else{
			$num = pdo_fetchcolumn("SELECT SUM(vote_times) FROM ".tablename($this->table_log)." WHERE rid= ".$rid." AND from_user= '".$from_user."' ");//投票数
		}
		if (empty($num)) {
			$num = 0;
		}

		return $num;
	}

	public function getaccount($uniacid) {
		$acid = pdo_fetchcolumn("SELECT default_acid FROM ".tablename('uni_account')." WHERE uniacid = :uniacid", array(':uniacid' => $uniacid));
		$account = account_fetch($acid);
		return $account;
	}
	public function getmoneys($rid, $from_user) {
		$num = pdo_fetchcolumn("SELECT SUM(price) FROM ".tablename($this->table_order)." WHERE rid= ".$rid." AND status = 1 AND from_user= '".$from_user."' ");//投票数
		if (empty($num)) {
			$num = '0.00';
		}else{
			$num = sprintf('%.2f', $num);;
		}
		return $num;
	}
	public function addorderlog($rid, $ordersn, $from_user, $jifen, $title, $type = '0', $remark) {
		global $_W;
		$item = pdo_fetch("SELECT * FROM " . tablename($this -> table_order) . " WHERE ordersn='{$ordersn}' limit 1");
		pdo_update($this->table_order, array('ispayvote' => $type), array('id' => $item['id']));
		$orderlog = pdo_fetch('SELECT * FROM '.tablename($this->table_orderlog).' WHERE rid= :rid AND ordersn = :ordersn', array(':rid' => $rid,':ordersn' => $ordersn));
		if ($type == 6) {
			$status = 1;
		}
		if (empty($orderlog)) {
			$data = array(
				'uniacid' => $_W['uniacid'],
				'rid' => $rid,
				'ordersn' => $ordersn,
				'from_user' => $from_user,
				'num' => $jifen,
				'title' => $title,
				'type' => $type,
				'status' => $status,
				'createtime' => TIMESTAMP,
				'remark' => $remark,
			);
			pdo_insert($this->table_orderlog, $data);
		}else{

			pdo_update($this->table_orderlog, array('status' => $status,'type' => $type,'lasttime' => TIMESTAMP), array('id' => $orderlog['id']));
		}


	}
	public function addmsg($rid,$from_user, $tfrom_user, $title, $content, $type = '1') {
		global $_W;
		$date = array(
			'uniacid' => $_W['uniacid'],
			'rid' => $rid,
			'status' => '0',
			'type' => $type,
			'from_user' => $from_user,
			'tfrom_user' => $tfrom_user,
			'title' => $title,
			'content' => $content,
			'createtime' => time()
		);
		pdo_insert($this->table_msg, $date);
	}
	public function getmsg($rid,$from_user,$type) {
		global $_W;
		$uniacid = $_W['uniacid'];
		$rmsg = array();
		if ($type == 'all') {
			$rmsg['msg'] = pdo_fetchall('SELECT rid,from_user,title,content,type,createtime FROM '.tablename($this->table_msg).' WHERE uniacid= :uniacid ORDER BY createtime DESC LIMIT 200', array(':uniacid' => $uniacid));
			$rmsg['total'] = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_msg)." WHERE uniacid= ".$uniacid." ");//投票数
		}else{
			$rmsg['msg'] = pdo_fetchall('SELECT * FROM '.tablename($this->table_msg).' WHERE rid= :rid AND from_user = :from_user ORDER BY createtime DESC LIMIT 200', array(':rid' => $rid,':from_user' => $from_user));
			$rmsg['total'] = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_msg)." WHERE rid= ".$rid." AND from_user= '".$from_user."' ");//投票数
		}

		return $rmsg;
	}
	public function getmsgtype($type) {

		if ($type == '1') {
			$v = 'am-icon-clone';
			$c = '#a589b1';
		}elseif ($type == '2') {
			$v = 'am-icon-plus';
			$c = '#8E44AD';
		}elseif ($type == '3') {
			$v = 'am-icon-user-plus';
			$c = '#33c6d2';
		}elseif ($type == '4') {
			$v = 'am-icon-money';
			$c = '#e8505a';
		}elseif ($type == '5') {
			$v = 'am-icon-gift';
			$c = '#f37a1e';
		}elseif ($type == '6') {
			$v = 'am-icon-exchange';
			$c = '#f37a1e';
		}elseif ($type == '7') {
			$v = 'am-icon-bell-o';
			$c = '#3598dc';
		}
		return array('color' => $c, 'icon' => $v);
	}
	public function getgiftlist($rid,$from_user, $pindex, $psize = '6',$jishu = '6', &$list ='') {
		$gift = pdo_fetchall('SELECT * FROM '.tablename($this->table_jifen_gift).' WHERE rid= :rid ORDER BY id DESC LIMIT ' . ($pindex - 1) * $jishu . ',' . $psize, array(':rid' => $rid));
		$total = pdo_fetchcolumn('SELECT COUNT(1) FROM '.tablename($this->table_jifen_gift).' WHERE rid= :rid ORDER BY id DESC LIMIT ' . ($pindex - 1) * $jishu . ',' . $psize, array(':rid' => $rid));
		$list .= '<div class="mui-slider-item"><ul class="mui-table-view mui-grid-view mui-grid-9">';
		foreach ($gift as $key => $value) {
			$mygift = pdo_fetch("SELECT giftnum FROM ".tablename($this->table_user_gift)." WHERE rid = ".$rid." AND from_user = :from_user AND status = 1 AND giftid = ".$value['id']."  ORDER BY giftid ASC", array(':from_user' => $from_user));
			if ($value['piaoshu'] > 0) {
				$fuhao = '+';
			}
			$list .= '<li id="rall" class="mui-table-view-cell mui-media mui-col-xs-4 mui-col-sm-4" onclick="choose('.$value['id'].')">
					<span id="addps_'.$value['id'].'" class="piaoshu" >'.$fuhao.$value['piaoshu'].' 票数</span>
						<a href="#" id="gift_'.$value['id'].'">
							<span class="mui-icon"><img src="'.tomedia($value['images']).'" width="100%" height="64"/>';
			if (!empty($mygift) && $mygift['giftnum'] > 0) {
				$list .= '<div class="maskBar text-c">拥有'.$mygift['giftnum'].'个</div>';
			}
			$list .= '</span>
							<div class="" style="line-height: 15px;">
								<p class="list-group-item-text" style="color:#f0ad4e;">'.$value['gifttitle'].'</p>
								<p class="list-group-item-text" style="color:#f0ad4e;font-size: 12px;"><span class="mui-icon Hui-iconfont Hui-iconfont-jifen"></span>'.$value['jifen'].'</p>
							</div>
						</a>
					</li>';
		}
		$list .= '</ul></div>';
		if (!empty($gift) && count($gift) >= $jishu) {
			$pindex =  $pindex+1;
			$psize = $pindex*$jishu;
			//print_r($psize);
			 $a = $this->getgiftlist($rid,$from_user,$pindex,$psize,$jishu, $list);
		}
		return $list;
	}
	public function getgift($rid) {
		$gift = array();
		$gift['gifts'] = pdo_fetchall('SELECT * FROM '.tablename($this->table_jifen_gift).' WHERE rid= :rid ORDER BY createtime DESC', array(':rid' => $rid));
		$gift['total'] = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_jifen_gift)." WHERE rid= ".$rid."  ");//礼物
		if (empty($gift['total'])) {
			$gift['total'] = '0';
		}
		return $gift;
	}
	public function getmygift($rid,$from_user, $type = '1') {
		$gift = array();
		$con = '';
		if ($type == 2) {
			$con .= ' AND status = 2 ';
		}else{
			$con .= ' AND status = 1 ';
		}
		$gift['gifts'] = pdo_fetchall("SELECT * FROM ".tablename($this->table_user_gift)." WHERE rid = ".$rid.$con."  AND from_user = :from_user  ORDER BY giftid ASC", array(':from_user' => $from_user));
		$gift['total'] = pdo_fetchcolumn("SELECT SUM(giftnum) FROM ".tablename($this->table_user_gift)." WHERE rid= ".$rid.$con."  AND from_user = :from_user ", array(':from_user' => $from_user));//礼物
		if (empty($gift['total'])) {
			$gift['total'] = '0';
		}
		$gift['zstotal'] = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_user_zsgift)." WHERE rid= ".$rid."  AND from_user = :from_user ", array(':from_user' => $from_user));//礼物
		if (empty($gift['zstotal'])) {
			$gift['zstotal'] = '0';
		}
		return $gift;
	}
	public function addjifen($rid, $from_user, $tfrom_user,$info = array(),$vote = array(),$type='vote') {
		if ($type != 'reg') {
			pdo_update($this->table_users, array('photosnum'=> $vote['3']+$vote['1'],'hits'=> $vote['4']+$vote['1']), array('rid' => $rid, 'from_user' => $tfrom_user));
			pdo_update($this->table_reply_display, array('ljtp_total' => $vote['2']+$vote['1'],'cyrs_total' => $vote['5']+$vote['1']), array('rid' => $rid));//增加总投票 总人气
		}
		$rjifen = pdo_fetch("SELECT is_open_jifen,is_open_jifen_sync,jifen_vote,jifen_vote_reg,jifen_reg FROM ".tablename($this->table_jifen)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		if ($rjifen['is_open_jifen']) {
			if ($type == 'reg') {
				$jifen = $rjifen['jifen_reg'];
				$msg = '报名参赛 <span class="label label-warning">增加</span> '.$jifen.'积分';
				$voteer = pdo_fetch("SELECT jifen FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
			}else{
				$user = pdo_fetch("SELECT id FROM ".tablename($this->table_users)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $tfrom_user,':rid' => $rid));
				if (!empty($user)) {
					$tjifen = $rjifen['jifen_vote_reg']*$vote['1'];
					$tmsg = '被投票 <span class="label label-warning">增加</span> '.$tjifen.'积分';
				}
				$jifen = $rjifen['jifen_vote']*$vote['1'];
				$msg = '投票 <span class="label label-warning">增加</span> '.$jifen.' 积分';
				$voteer = pdo_fetch("SELECT jifen FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
				$tvoteer = pdo_fetch("SELECT jifen FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $tfrom_user,':rid' => $rid));
			}


			if ($rjifen['is_open_jifen_sync']) {
				load()->model('mc');
				$uid = mc_openid2uid($from_user);
				if (empty($uid)) {
					$uid = $_W['fans']['uid'];
				}
				if (!empty($uid)) {
					mc_credit_update($uid, 'credit1', $jifen, array(0, $msg,'fm_photosvote'));
					$result = mc_fetch($uid, array('credit1'));
					$lastjifen = $result['credit1'];
				}else{
					$lastjifen = $voteer['jifen']+$jifen;
				}
				$tuid = mc_openid2uid($tfrom_user);
				if (!empty($tuid)) {
					mc_credit_update($tuid, 'credit1', $tjifen, array(0, $tmsg,'fm_photosvote'));
					$tresult = mc_fetch($tuid, array('credit1'));
					$tlastjifen = $tresult['credit1'];
				}else{
					$lastjifen = $voteer['jifen']+$jifen;
					$tlastjifen = $tvoteer['jifen']+$tjifen;
				}
			}else{
				$lastjifen = $voteer['jifen']+$jifen;
				$tlastjifen = $tvoteer['jifen']+$tjifen;
			}

			pdo_update($this->table_voteer, array('jifen' => $lastjifen), array('rid' => $rid, 'from_user'=>$from_user));//增加积分

			if ($type != 'reg') {
				pdo_update($this->table_voteer, array('jifen' => $tlastjifen), array('rid' => $rid, 'from_user'=>$tfrom_user));//增加积分
			}
		}
		if ($type == 'reg') {
			$this->addmsg($rid,$from_user,'','报名消息',$msg,'3');
		}else{
			//$tpinfo = $this->gettpinfo($rid,$from_user);
			$nickname = $this->getname($rid, $from_user);
			$tcontent = '恭喜您，' . $nickname . '为您投了'.$vote['1'].'票<br />' . $tmsg;
			$this->addmsg($rid,$from_user,$tfrom_user,'投票消息',$info['3'],'1');
			$this->addmsg($rid,$tfrom_user,'','被投票消息',$tcontent,'2');
		}
		return true;
	}
	public function jsjifen($rid, $from_user, $jifen, $gifttitle, $type = 'gift') {
		global $_W;
		$userjf = $this->cxjifen($rid, $from_user);
		$lastjifen = $userjf - $jifen;
		$voteer = pdo_fetch("SELECT jifen FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
		$rjifen = pdo_fetchcolumn("SELECT is_open_jifen_sync FROM ".tablename($this->table_jifen)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		if ($rjifen) {
			load()->model('mc');
			$uid = mc_openid2uid($from_user);
			if (empty($uid)) {
				$uid = $_W['fans']['uid'];
			}
			if (!empty($uid)) {
				if ($type == 'cj') {
					$msg = '抽奖，<span class="label label-warning">减少</span>'.$jifen.'积分';
				}elseif ($type == 'zs') {
					$msg = '赠送礼物: '.$gifttitle.'，<span class="label label-warning">减少</span>'.$jifen.'积分';
				}else{
					$msg = '兑换礼物: '.$gifttitle.'，<span class="label label-warning">减少</span>'.$jifen.'积分';
				}

				fm_mc_credit_update($uid, 'credit1', $jifen, 'js', array(0, $msg,'fm_photosvote'));
			}
		}
		pdo_update($this->table_voteer, array('jifen' => $lastjifen), array('rid' => $rid, 'from_user'=>$from_user));//积分
		if ($type == 'cj') {
			$msg = '抽奖 ，<span class="label label-warning">消费</span>'.$jifen.'积分';
		}elseif ($type == 'zs') {
			$msg = '赠送 '.$gifttitle.' 礼物，<span class="label label-warning">消费</span>'.$jifen.'积分';
		}else{
			$msg = '兑换 '.$gifttitle.' 礼物，<span class="label label-warning">消费</span>'.$jifen.'积分';
		}
		$this->addmsg($rid,$from_user,'','积分消费',$msg,'6');//
		return true;
	}
	public function addjifencharge($rid, $from_user, $jifen, $ordersn) {
		global $_W;
		$remark = '微信充值，<span class="label label-warning">增加</span>'.$_GPC['jifen'].'积分';
		$userjf = $this->cxjifen($rid, $from_user);
		$lastjifen = $userjf + $jifen;
		$voteer = pdo_fetch("SELECT jifen FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
		$this->addorderlog($rid, $ordersn, $from_user, $_GPC['jifen'], '积分充值', $type = '4', $remark);
		$rjifen = pdo_fetchcolumn("SELECT is_open_jifen_sync FROM ".tablename($this->table_jifen)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		if ($rjifen) {
			load()->model('mc');
			$uid = mc_openid2uid($from_user);
			if (empty($uid)) {
				$uid = $_W['fans']['uid'];
			}
			$this->addorderlog($rid, $ordersn, $from_user, $_GPC['jifen'], '积分充值', $type = '5', $remark);
			if (!empty($uid)) {
				$msg = '微信充值，<span class="label label-warning">增加</span>'.$jifen.'积分';
				fm_mc_credit_update($uid, 'credit1', $jifen, 'add', array(0, $msg,'fm_photosvote'));
				$this->addorderlog($rid, $ordersn, $from_user, $_GPC['jifen'], '积分充值', $type = '6', $remark);
			}
		}else{
			$this->addorderlog($rid, $ordersn, $from_user, $_GPC['jifen'], '积分充值', $type = '6', $remark);
		}
		pdo_update($this->table_voteer, array('jifen' => $lastjifen), array('rid' => $rid, 'from_user'=>$from_user));//增加积分

		$msg = '微信充值，<span class="label label-warning">增加</span>'.$jifen.'积分';
		$this->addmsg($rid,$from_user,'','积分充值',$msg,'4');//充值
		return true;
	}
	public function cxjifen($rid,$from_user) {
		$rjifen = pdo_fetch("SELECT is_open_jifen_sync FROM ".tablename($this->table_jifen)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$orders = pdo_fetchall("SELECT * FROM ".tablename($this->table_order)." WHERE rid = :rid AND from_user = :from_user AND status = 1 AND paytype = 6 AND ispayvote > 3 AND ispayvote < 6 AND (transid != '' OR transid != '0')", array(':rid' => $rid,':from_user' => $from_user));
		if (!empty($orders)) {
			foreach ($orders as $key => $value) {
				$voteer = pdo_fetch("SELECT jifen FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
				$lastjifen = $voteer['jifen'] + $value['jifen'];
				pdo_update($this->table_voteer, array('jifen' => $lastjifen), array('rid' => $rid, 'from_user'=>$from_user));//增加积分
				$remark = '微信充值，<span class="label label-warning">增加</span>'.$_GPC['jifen'].'积分';
				$this->addorderlog($rid, $value['ordersn'], $from_user, $value['jifen'], '积分充值', $type = '6', $remark);
			}
		}
		$voteer = pdo_fetch("SELECT jifen FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
		if ($rjifen['is_open_jifen_sync']) {
			load()->model('mc');
			$uid = mc_openid2uid($from_user);
			if (empty($uid)) {
				$uid = $_W['fans']['uid'];
			}
			if (!empty($uid)) {
				$result = mc_fetch($uid, array('credit1'));
				$lastjifen = $result['credit1'];
				if (empty($voteer['jifen'])) {
					if (!empty($lastjifen)) {
						return $lastjifen;
					}else{
						return '0';
					}
				}else{
					if ($lastjifen > 0) {
						if ($lastjifen != $voteer['jifen'] && $voteer['jifen'] <= 0) {
							$lastjifen = $lastjifen + $voteer['jifen'];
							pdo_update($this->table_voteer, array('jifen' => $lastjifen), array('rid' => $rid, 'from_user'=>$from_user));//增加积分
							$msg = '同步积分，<span class="label label-warning">同步</span>'.$lastjifen.'积分';
							fm_mc_credit_update($uid, 'credit1', $lastjifen, false, array(0, $msg,'fm_photosvote'));
						}
						$voteer = pdo_fetch("SELECT jifen FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
						$lastjifen = $voteer['jifen'];

						return $lastjifen;
					}else{
						if (!empty($uid)) {
							$msg = '同步积分中，<span class="label label-warning">同步</span>'.$jifen.'积分';
							fm_mc_credit_update($uid, 'credit1', $voteer['jifen'], false, array(0, $msg,'fm_photosvote'));
							$lastjifen = $voteer['jifen'];
						}else{
							$lastjifen = $voteer['jifen'];
						}
					}
					return $lastjifen;
				}
			}else{
				if (!empty($voteer['jifen'])) {
					return $voteer['jifen'];
				}else{
					return '0';
				}
			}

		}else{
			if (!empty($voteer['jifen'])) {
				return $voteer['jifen'];
			}else{
				return '0';
			}
		}
	}
	public function editjifen($rid, $from_user, $jifen,$nickname,$avatar,$realname,$mobile,$sex) {
		global $_W;
		$rjifen = pdo_fetchcolumn("SELECT is_open_jifen_sync FROM ".tablename($this->table_jifen)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$voteer = pdo_fetch("SELECT * FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
		if ($rjifen) {
			load()->model('mc');
			$uid = mc_openid2uid($from_user);
			if (empty($uid)) {
				$uid = $_W['fans']['uid'];
			}
			if (!empty($uid)) {
				$msg = '后台手动修改，<span class="label label-warning">变更为</span>'.$jifen.'积分';
				fm_mc_credit_update($uid, 'credit1', $jifen, false, array(0, $msg,'fm_photosvote'));
			}
		}
		$voteer_data = array(
			'uniacid' => $_W['uniacid'],
			'weid' => $_W['uniacid'],
			'rid' => $rid,
			'from_user' => $from_user,
			'nickname' => $nickname,
			'avatar' => $avatar,
			'sex' => $sex,
			'realname' => $realname,
			'mobile' => $mobile,
			'status' => '1',
			'jifen' => $jifen,
			//'ip' => getip(),
			'createtime' => time(),
		);

		if (empty($voteer)) {
			pdo_insert($this->table_voteer, $voteer_data);
		}else{
			pdo_update($this->table_voteer, array('jifen' => $jifen), array('rid' => $rid, 'from_user'=>$from_user));//增加积分
		}
		$msg = '管理员后台手动修改积分，<span class="label label-warning">变更为</span>'.$jifen.'积分';
		$this->addmsg($rid,$from_user,'','积分变动',$msg,'6');
		return true;
	}
	public function createvoteer($rid,$uniacid, $from_user,$nickname,$avatar,$sex) {
		if (!empty($from_user)) {
			$voteer = pdo_fetch("SELECT * FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user,':rid' => $rid));
			$rvote = pdo_fetch("SELECT open_smart,answer_times,isanswer FROM ".tablename($this->table_reply_vote)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$time = time();

			if (empty($voteer)) {
				if ($rvote['open_smart']) {
					$voteernickname = pdo_fetch("SELECT id FROM ".tablename($this->table_voteer)." WHERE nickname = :nickname and rid = :rid", array(':nickname' => $nickname,':rid' => $rid));
					$voteeravatar = pdo_fetch("SELECT id FROM ".tablename($this->table_voteer)." WHERE avatar = :avatar and rid = :rid", array(':avatar' => $avatar,':rid' => $rid));
					if (!empty($voteernickname) && !empty($voteeravatar) ) {
						$splog = array(
							'uniacid' => $uniacid,
							'weid' => $uniacid,
							'rid' => $rid,
							'from_user' => $from_user,
							'nickname' => $nickname,
							'avatar' => $avatar,
							'sex' => $sex,
							'status' => '1',
							'ip' => getip(),
							'createtime' => $time,
						);
						$splog['iparr'] = getiparr($splog['ip']);
						pdo_insert($this->table_shuapiaolog, $splog);
						$stopllq = $_W['siteroot'] .'app/'.$this->createMobileUrl('stopllq', array('rid' => $rid, 'info' => '系统检测到您存在异常，请勿刷票，否则将拉入黑名单'));
					    header("location:$stopllq");
						exit;
					}
				}
				$voteer_data = array(
					'uniacid' => $uniacid,
					'weid' => $uniacid,
					'rid' => $rid,
					'from_user' => $from_user,
					'nickname' => $nickname,
					'avatar' => $avatar,
					'sex' => $sex,
					'realname' => '',
					'mobile' => '',
					'status' => '1',
					'ip' => getip(),
					'lasttime' => $time,
					'createtime' => $time,
				);
				if ($rvote['isanswer']) {
					$voteer_data['is_user_chance'] = $rvote['answer_times'];
				}
				$voteer_data['iparr'] = getiparr($voteer_data['ip']);
				pdo_insert($this->table_voteer, $voteer_data);
				$msg = '恭喜您成功开通个人中心';
				$this->addmsg($rid,$from_user,'','开通信息',$msg,'7');
			}else{
				if ($rvote['isanswer']) {
					if ($voteer['lasttime'] < mktime(0,0,0)) {
						pdo_update($this->table_voteer, array('is_user_chance' => $rvote['answer_times'], 'lasttime' => time()), array('rid' => $rid, 'from_user'=>$from_user));//写入答题
					}
				}
				//print_r($rvote);
		//exit;
				pdo_update($this->table_voteer, array('lasttime' => $time, 'ip' => getip()),array('from_user'=>$from_user,'rid'=>$rid));
				//$this->recoveryorder($rid, $from_user);

			}

			return true;
		}else{
			return false;
		}

	}
	public function updatevoteer($rid, $from_user,$realname,$mobile) {
		$voteer = pdo_fetch("SELECT realname,mobile FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user,':rid' => $rid));

		if (empty($realname)) {
			$msg = '您的真实姓名没有填写，请填写！';
			$status = -1;
			return $msg;
		}

		if(!preg_match(REGULAR_MOBILE, $mobile)) {
			$msg = '必须输入手机号，格式为 11 位数字。';
			$status = -1;
			return $msg;
		}

		if ($voteer['realname']) {
			if ($voteer['realname'] == $realname) {
			}else {
				$realname = pdo_fetch("SELECT * FROM ".tablename($this->table_voteer)." WHERE realname = :realname and rid = :rid", array(':realname' => $realname,':rid' => $rid));
				if (!empty($realname)) {
					$msg = '已经存在该姓名';
					$status = -1;
					return $msg;
				}
			}
		}
		if ($voteer['mobile']) {
			if ($voteer['mobile'] == $mobile) {
			}else {
				$ymobile = pdo_fetch("SELECT * FROM ".tablename($this->table_voteer)." WHERE mobile = :mobile and rid = :rid", array(':mobile' => $mobile,':rid' => $rid));
				if(!empty($ymobile)) {
					$r = array(
						'msg' => '非常抱歉，此手机号码已经被注册，你需要更换注册手机号！',
						'status' => -1
					);
					$msg = '非常抱歉，此手机号码已经被注册，你需要更换注册手机号！';
					return $msg;
				}
			}
		}

		pdo_update($this->table_voteer, array('realname' => $realname,'mobile' => $mobile),array('from_user'=>$from_user,'rid'=>$rid));

	}
	public function updatelp($rid) {
		$where = "";
		$where .= " AND (transid != '' OR transid <> '0') AND (paytime != '' OR paytime != '0') ";
		$where .= " AND ispayvote > 1";
		$where .= " AND paytype < 6";
		$votelogs = pdo_fetchall('SELECT * FROM '.tablename($this->table_order).' WHERE `rid` = '.$rid.' '.$where.' ');
		foreach ($votelogs as $key => $value) {
			$tfrom_user = $value['tfrom_user'];
			$vote_times = $value['vote_times'];
			$user = $this->_getloguser($rid, $value['from_user']);
			$votedate = array(
				'uniacid' => $uniacid,
				'rid' => $rid,
				'tptype' => '3',
				'vote_times' => $vote_times,
				'avatar' => $user['avatar'],
				'nickname' => $user['nickname'],
				'from_user' => $value['from_user'],
				'afrom_user' => $value['fromuser'],
				'tfrom_user' => $tfrom_user,
				'ordersn' => $value['ordersn'],
				'islp' => '1',
				'ip' => $value['ip'],
				'iparr' => $value['iparr'],
				'createtime' => $value['paytime']
			);
			pdo_insert($this->table_log, $votedate);
			pdo_update($this->table_order, array('ispayvote' => '1'), array('ordersn' => $value['ordersn']));
			$user = pdo_fetch("SELECT hits,photosnum FROM ".tablename($this->table_users)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $tfrom_user,':rid' => $rid));
			pdo_update($this->table_users, array('photosnum'=>$user['photosnum'] + $vote_times,'hits'=> $user['hits']+$vote_times), array('rid' => $rid, 'from_user' => $tfrom_user));
			$rdisplay = pdo_fetch("SELECT ljtp_total,cyrs_total  FROM ".tablename($this->table_reply_display)." WHERE rid = :rid", array(':rid' => $rid));
			pdo_update($this->table_reply_display, array('ljtp_total' =>$rdisplay['ljtp_total'] + $vote_times,'cyrs_total' =>$rdisplay['cyrs_total'] +  $vote_times), array('rid' => $rid));//增加总投票 总人气

		}
		setcookie("user_lptb_time", time(), time()+1800);
	}
	public function counter($rid,$from_user,$tfrom_user, $types, $unimoshi='') {
		global $_W;
		$where = "";
		$starttime = mktime(0,0,0);//当天：00：00：00
		$endtime = $starttime + 86399;//当天：23：59：59
		$where .= ' AND createtime >=' .$starttime;
		$where .= ' AND createtime <=' .$endtime;
		if ($types == 'tp') {
			if ($unimoshi == 1) {
				$num = 8;
			}else{
				$num = 4;
			}
			for ($type = 1; $type <= $num; $type++) {
				$date = array(
					'uniacid' => $_W['uniacid'],
					'rid' => $rid,
					'from_user' => $from_user
				);
				switch ($type) {
					case '1':
						$counter = pdo_fetch("SELECT * FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user AND type = :type", array(':rid' => $rid,':from_user' => $from_user,':type' => $type));
						$date['tp_times'] = $counter['tp_times'] + 1;
						$date['type'] = $type;
						break;
					case '2':
						$counter = pdo_fetch("SELECT * FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user AND type = :type $where", array(':rid' => $rid,':from_user' => $from_user,':type' => $type));
						$date['tp_times'] = $counter['tp_times'] + 1;
						$date['type'] = $type;
						break;
					case '3':
						$counter = pdo_fetch("SELECT * FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user AND tfrom_user = :tfrom_user AND type = :type", array(':rid' => $rid,':from_user' => $from_user,':tfrom_user' => $tfrom_user,':type' => $type));
						$date['tfrom_user'] = $tfrom_user;
						$date['tp_times'] = $counter['tp_times'] + 1;
						$date['type'] = $type;
						break;
					case '4':
						$counter = pdo_fetch("SELECT * FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user  AND tfrom_user = :tfrom_user AND type = :type $where", array(':rid' => $rid,':from_user' => $from_user,':tfrom_user' => $tfrom_user,':type' => $type));
						$date['tfrom_user'] = $tfrom_user;
						$date['tp_times'] = $counter['tp_times'] + 1;
						$date['type'] = $type;
						break;
					case '5':
						$counter = pdo_fetch("SELECT * FROM ".tablename($this->table_counter)." WHERE uniacid = :uniacid AND rid = :rid AND from_user = :from_user AND type = :type", array(':uniacid' => $_W['uniacid'],':rid' => $rid,':from_user' => $from_user,':type' => $type));
						$date['tp_times'] = $counter['tp_times'] + 1;
						$date['type'] = $type;
						break;
					case '6':
						$counter = pdo_fetch("SELECT * FROM ".tablename($this->table_counter)." WHERE uniacid = :uniacid AND rid = :rid AND from_user = :from_user AND type = :type $where", array(':uniacid' => $_W['uniacid'],':rid' => $rid,':from_user' => $from_user,':type' => $type));
						$date['tp_times'] = $counter['tp_times'] + 1;
						$date['type'] = $type;
						break;
					case '7':
						$counter = pdo_fetch("SELECT * FROM ".tablename($this->table_counter)." WHERE uniacid = :uniacid AND rid = :rid AND from_user = :from_user AND tfrom_user = :tfrom_user AND type = :type", array(':uniacid' => $_W['uniacid'],':rid' => $rid,':from_user' => $from_user,':tfrom_user' => $tfrom_user,':type' => $type));
						$date['tfrom_user'] = $tfrom_user;
						$date['tp_times'] = $counter['tp_times'] + 1;
						$date['type'] = $type;
						break;
					case '8':
						$starttime = mktime(0,0,0);//当天：00：00：00
						$endtime = $starttime + 86399;//当天：23：59：59
						$where .= ' AND createtime >=' .$starttime;
						$where .= ' AND createtime <=' .$endtime;
						$counter = pdo_fetch("SELECT * FROM ".tablename($this->table_counter)." WHERE uniacid = :uniacid AND rid = :rid AND from_user = :from_user  AND tfrom_user = :tfrom_user AND type = :type $where", array(':uniacid' => $_W['uniacid'],':rid' => $rid,':from_user' => $from_user,':tfrom_user' => $tfrom_user,':type' => $type));
						$date['tfrom_user'] = $tfrom_user;
						$date['tp_times'] = $counter['tp_times'] + 1;
						$date['type'] = $type;
						break;
					default:
						break;
				}

				$id = $counter['id'];
				if (empty($id)) {
					$date['createtime'] = TIMESTAMP;
					pdo_insert($this->table_counter, $date);
				}else{
					pdo_update($this->table_counter, $date, array('id' => $id));
				}
			}
		}elseif ($types == 'gift') {
			$date = array(
				'uniacid' => $_W['uniacid'],
				'rid' => $rid,
				'from_user' => $from_user,
				'type' => 9,
				'createtime' => TIMESTAMP,
			);
			$where = "";
			$starttime = mktime(0,0,0);//当天：00：00：00
			$endtime = $starttime + 86399;//当天：23：59：59
			$where .= ' AND createtime >=' .$starttime;
			$where .= ' AND createtime <=' .$endtime;
			$counter = pdo_fetch("SELECT * FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user AND type = :type $where" , array(':rid' => $rid,':from_user' => $from_user,':type' => 9));
			$date['gift_times'] = $counter['gift_times'] + 1;
			$id = $counter['id'];
			//print_r($counter);
			if (empty($id)) {
				//$date['createtime'] = TIMESTAMP;
				pdo_insert($this->table_counter, $date);
			}else{
				pdo_update($this->table_counter, $date, array('id' => $id));
			}
		}
	}
	public function gettpnum($rid, $from_user, $tfrom_user = '', $type = '') {
		global $_W;
		$where = "";
		$starttime = mktime(0,0,0);//当天：00：00：00
		$endtime = $starttime + 86399;//当天：23：59：59
		$where .= ' AND createtime >=' .$starttime;
		$where .= ' AND createtime <=' .$endtime;
		switch ($type) {
			case '1':
				$counter = pdo_fetchcolumn("SELECT tp_times FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user AND type = :type", array(':rid' => $rid,':from_user' => $from_user,':type' => $type));
				break;
			case '2':
				$counter = pdo_fetchcolumn("SELECT tp_times FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user AND type = :type $where", array(':rid' => $rid,':from_user' => $from_user,':type' => $type));
				break;
			case '3':
				$counter = pdo_fetchcolumn("SELECT tp_times FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user AND tfrom_user = :tfrom_user AND type = :type", array(':rid' => $rid,':from_user' => $from_user,':tfrom_user' => $tfrom_user,':type' => $type));

				break;
			case '4':
				$counter = pdo_fetchcolumn("SELECT tp_times FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user  AND tfrom_user = :tfrom_user AND type = :type $where", array(':rid' => $rid,':from_user' => $from_user,':tfrom_user' => $tfrom_user,':type' => $type));

				break;
			case '5':
				$counter = pdo_fetchcolumn("SELECT tp_times FROM ".tablename($this->table_counter)." WHERE uniacid = :uniacid AND rid = :rid AND from_user = :from_user AND type = :type", array(':uniacid' => $_W['uniacid'],':rid' => $rid,':from_user' => $from_user,':type' => $type));
				break;
			case '6':
				$counter = pdo_fetchcolumn("SELECT tp_times FROM ".tablename($this->table_counter)." WHERE uniacid = :uniacid AND rid = :rid AND from_user = :from_user AND type = :type $where", array(':uniacid' => $_W['uniacid'],':rid' => $rid,':from_user' => $from_user,':type' => $type));

				break;
			case '7':
				$counter = pdo_fetchcolumn("SELECT tp_times FROM ".tablename($this->table_counter)." WHERE uniacid = :uniacid AND rid = :rid AND from_user = :from_user AND tfrom_user = :tfrom_user AND type = :type", array(':uniacid' => $_W['uniacid'],':rid' => $rid,':from_user' => $from_user,':tfrom_user' => $tfrom_user,':type' => $type));

				break;
			case '8':
				$counter = pdo_fetchcolumn("SELECT tp_times FROM ".tablename($this->table_counter)." WHERE uniacid = :uniacid AND rid = :rid AND from_user = :from_user  AND tfrom_user = :tfrom_user AND type = :type $where", array(':uniacid' => $_W['uniacid'],':rid' => $rid,':from_user' => $from_user,':tfrom_user' => $tfrom_user,':type' => $type));
				break;
			case '9':
				$counter = pdo_fetchcolumn("SELECT gift_times FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user AND type = :type $where", array(':rid' => $rid,':from_user' => $from_user,':type' => $type));
				break;

			default:
				$counter = pdo_fetchcolumn("SELECT tp_times FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user AND type = :type", array(':rid' => $rid,':from_user' => $from_user,':type' => $type));
				break;
		}
		if (empty($counter)) {
			$counter = 0;
		}
		return $counter;
	}
	public function gettpxz_status($rid, $from_user, $tfrom_user = '', $type = '1', $tpxz) {
		$counter = $this->gettpnum($rid, $from_user, $tfrom_user, $type);//活动期间一共可以投多少次票限制（全部人）
		if ($counter >= $tpxz) {
			return false;
		}else{
			return true;
		}
	}
	public function votecode($rid, $from_user, $tfrom_user) {
		global $_W;
		//load()->func('communication');
		$setting = setting_load('site');
		$id = isset($setting['site']['key']) ? $setting['site']['key'] : '0';
		$onlyoauth = pdo_fetch("SELECT fmauthtoken FROM ".tablename('fm_photosvote_onlyoauth')." WHERE 1 ORDER BY id DESC LIMIT 1");
		//$d = base64_decode("aHR0cDovL2FwaS5mbW9vbnMuY29tL2luZGV4LnBocD8md2VidXJsPQ==").$_SERVER ['HTTP_HOST']."&visitorsip=" . $_W['clientip']."&modules=".$_GPC['m']."&type=cx";
		//$dc = ihttp_get($d);
		//$t = @json_decode($dc['content'], true);
		if (!empty($onlyoauth['fmauthtoken'])) {
			$text = $_W['config']['setting']['authkey'].$onlyoauth['fmauthtoken'].$from_user.$tfrom_user.$rid;
			//$key = cutstr($_W['token'], '8');
			$votecode = base64_encode($text);
			return $votecode;
		}else{
			return false;
		}
	}
	public function fmvipleavel($rid, $uniacid,  $tfrom_user) {

		$user = pdo_fetch("SELECT photosnum,xnphotosnum,hits,xnhits,yaoqingnum,zans FROM ".tablename($this->table_users)." WHERE rid= ".$rid." AND from_user= '".$tfrom_user."' ");
		$jifen = $this->cxjifen($rid, $from_user);
		if (!empty($user)) {
			$userps = $user['photosnum'] + $user['xnphotosnum'] + $user['hits'] + $user['xnhits'] + $user['yaoqingnum'] + $user['zans'] + $jifen;
		}else {
			$userps = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_log)." WHERE rid= ".$rid." AND from_user= '".$tfrom_user."' ") + $jifen;
		}

			$userps = intval($userps);

		if ($userps >0 && $userps <= 1) {
			$level = 1;
		}elseif ($userps >1 && $userps <= 5) {
			$level = 2;
		}elseif ($userps >5 && $userps <= 15) {
			$level = 3;
		}elseif ($userps >15 && $userps <= 30) {
			$level = 4;
		}elseif ($userps >30 && $userps <= 50) {
			$level = 5;
		}elseif ($userps >50 && $userps <= 100) {
			$level = 6;
		}elseif ($userps >100 && $userps <= 200) {
			$level = 7;
		}elseif ($userps >200 && $userps <= 400) {
			$level = 8;
		}elseif ($userps >400 && $userps <= 800) {
			$level = 9;
		}elseif ($userps >800 && $userps <= 2000) {
			$level = 10;
		}elseif ($userps >2000 && $userps <= 3000) {
			$level = 11;
		}elseif ($userps >3000 && $userps <= 5000) {
			$level = 12;
		}elseif ($userps >5000 && $userps <= 8000) {
			$level = 13;
		}elseif ($userps >8000 && $userps <= 15000) {
			$level = 14;
		}elseif ($userps >15000 && $userps <= 30000) {
			$level = 15;
		}elseif ($userps >30000 && $userps <= 60000) {
			$level = 16;
		}elseif ($userps >60000 && $userps <= 100000) {
			$level = 17;
		}elseif ($userps >100000 && $userps <= 500000) {
			$level = 18;
		}elseif ($userps >500000 && $userps <= 1500000) {
			$level = 19;
		}elseif ($userps >1500000 && $userps <= 3500000) {
			$level = 20;
		}

		return $level;
	}
	public function emotion($text) {
			$smile_popo =  '<span class="smile_popo" style="background-position-y: ';
			$smile_popoe = 'px;display: inline-block;  width: 30px"></span>';
			$str = array(
				 '(#呵呵)'=> $smile_popo.'-0'.$smile_popoe,
				 '(#哈哈)' =>  $smile_popo.'-30'.$smile_popoe,
				 '(#吐舌)' =>  $smile_popo.'-60'.$smile_popoe,
				 '(#啊)' =>  $smile_popo.'-90'.$smile_popoe,
				 '(#酷)' =>  $smile_popo.'-120'.$smile_popoe,
				 '(#怒)' =>  $smile_popo.'-150'.$smile_popoe,
				 '(#开心)' =>  $smile_popo.'-180'.$smile_popoe,
				 '(#汗)' =>  $smile_popo.'-210'.$smile_popoe,
				 '(#泪)' =>  $smile_popo.'-240'.$smile_popoe,
				 '(#黑线)' =>  $smile_popo.'-270'.$smile_popoe,
				 '(#鄙视)' =>  $smile_popo.'-300'.$smile_popoe,
				 '(#不高兴)' =>  $smile_popo.'-330'.$smile_popoe,
				 '(#真棒)' =>  $smile_popo.'-360'.$smile_popoe,
				 '(#钱)' =>  $smile_popo.'-390'.$smile_popoe,
				 '(#疑问)' =>  $smile_popo.'-420'.$smile_popoe,
				 '(#阴险)' =>  $smile_popo.'-450'.$smile_popoe,
				 '(#吐)' =>  $smile_popo.'-480'.$smile_popoe,
				 '(#咦)' =>  $smile_popo.'-510'.$smile_popoe,
				 '(#委屈)' =>  $smile_popo.'-540'.$smile_popoe,
				 '(#花心)' =>  $smile_popo.'-570'.$smile_popoe,
				 '(#呼~)' =>  $smile_popo.'-600'.$smile_popoe,
				 '(#笑眼)' =>  $smile_popo.'-630'.$smile_popoe,
				 '(#冷)' =>  $smile_popo.'-660'.$smile_popoe,
				 '(#太开心)' =>  $smile_popo.'-690'.$smile_popoe,
				 '(#滑稽)' =>  $smile_popo.'-720'.$smile_popoe,
				 '(#勉强)' =>  $smile_popo.'-750'.$smile_popoe,
				 '(#狂汗)' =>  $smile_popo.'-780'.$smile_popoe,
				 '(#乖)' =>  $smile_popo.'-810'.$smile_popoe,
				 '(#睡觉)' =>  $smile_popo.'-840'.$smile_popoe,
				 '(#惊哭)' =>  $smile_popo.'-870'.$smile_popoe,
				 '(#升起)' =>  $smile_popo.'-900'.$smile_popoe,
				 '(#惊讶)' =>  $smile_popo.'-930'.$smile_popoe,
				 '(#喷)' =>  $smile_popo.'-960'.$smile_popoe,
				 '(#爱心)' =>  $smile_popo.'-990'.$smile_popoe,
				 '(#心碎)' =>  $smile_popo.'-1020'.$smile_popoe,
				 '(#玫瑰)' =>  $smile_popo.'-1050'.$smile_popoe,
				 '(#礼物)' =>  $smile_popo.'-1080'.$smile_popoe,
				 '(#彩虹)' =>  $smile_popo.'-1110'.$smile_popoe,
				 '(#星星月亮)' =>  $smile_popo.'-1140'.$smile_popoe,
				 '(#太阳)' =>  $smile_popo.'-1170'.$smile_popoe,
				 '(#钱币)' =>  $smile_popo.'-1200'.$smile_popoe,
				 '(#灯泡)' =>  $smile_popo.'-1230'.$smile_popoe,
				 '(#茶杯)' =>  $smile_popo.'-1260'.$smile_popoe,
				 '(#蛋糕)' =>  $smile_popo.'-1290'.$smile_popoe,
				 '(#音乐)' =>  $smile_popo.'-1320'.$smile_popoe,
				 '(#haha)' =>  $smile_popo.'-1350'.$smile_popoe,
				 '(#胜利)' =>  $smile_popo.'-1380'.$smile_popoe,
				 '(#大拇指)' =>  $smile_popo.'-1410'.$smile_popoe,
				 '(#弱)' =>  $smile_popo.'-1440'.$smile_popoe,
				 '(#OK)' =>  $smile_popo.'-1470'.$smile_popoe,
			);
			$content = strtr($text,$str);
			return $content;
		}
	public function uniarr($uniarr,$uniacid) {
		foreach ($uniarr as $key => $value) {
			if ($value == $uniacid) {
				return true;
			}
		}
		return false;
	}
	public function limitSpeed($rid, $limitsd, $from_user, $type = '') {
		$zf = date('H',time()) * 60 + date('i',time());
		$timeduan = intval($zf / $limitsd);//总时间段 288 当前时间段
		$cstime = $timeduan*$limitsd * 60+mktime(0,0,0);//初始限制时间
		$jstime = ($timeduan+1)*$limitsd * 60+mktime(0,0,0);//结束限制时间

		$where = '';
		if ($type == 'voter') {
			$where .= ' AND from_user = "' . $from_user.'"';
		}else{
			$where .= ' AND tfrom_user = "' . $from_user.'"';
		}
		$where .= ' AND createtime >=' .$cstime;
		$where .= ' AND createtime <=' .$jstime;

		$limitsdvote = pdo_fetchcolumn('SELECT COUNT(1) FROM '.tablename($this->table_log).' WHERE rid = :rid '.$where.' ORDER BY createtime DESC', array(':rid' => $rid));	// 全体当前时间段投票总数
		$r = array(
			'cstime' => $cstime,
			'limitsdvote' => $limitsdvote,
		);
		return $r;
	}

	public function get_advs($rid) {
		$advs = pdo_fetchall("SELECT advname,link,thumb FROM " . tablename($this -> table_advs) . " WHERE enabled=1 AND ismiaoxian = 0 AND rid= '{$rid}' AND issuiji = 1");

		if (!empty($advs)) {
			$adv = array_rand($advs);
			$advarr = array();
			$advarr['thumb'] .= tomedia($advs[$adv]['thumb']);
			$advarr['advname'] .= cutstr($advs[$adv]['advname'], '10');
			$advarr['link'] .= $advs[$adv]['link'];
			return $advarr;
		}
	}

	public function get_answer($rid) {
		$answers = pdo_fetchall("SELECT * FROM " . tablename($this -> table_answer) . " WHERE rid = '{$rid}' ORDER BY displayorder DESC, id ASC");
		if (!empty($answers)) {
			$ans = array_rand($answers);
			$answerarr = array();
			$answerarr['thumb'] .= tomedia($answers[$ans]['thumb']);
			$answerarr['title'] .= cutstr($answers[$ans]['title'], '220');
			$answerarr['answer'] .= $answers[$ans]['answer'];
			$answerarr['key'] .= $answers[$ans]['key'];
			$answerarr['id'] .= $answers[$ans]['id'];
			return $answerarr;
		}
	}

	public function input_answer($rid , $from_user, $chose_answer, $answer_id) {
		$voteer = pdo_fetch("SELECT chance,is_user_chance FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid LIMIT 1", array(':from_user' => $from_user,':rid' => $rid));
		if (empty($voteer['is_user_chance'])) {
			$answer = pdo_fetch("SELECT * FROM " . tablename($this -> table_answer) . " WHERE id = :id and rid = :rid limit 1", array(':id' => $answer_id,':rid' => $rid));
			if ($answer['key'] == $chose_answer) {
				pdo_update($this->table_voteer, array('chance' => 1, 'is_user_chance' => 'yes'), array('rid' => $rid, 'from_user'=>$from_user));//写入答题
			}else{
				pdo_update($this->table_voteer, array('is_user_chance' => 'yes'), array('rid' => $rid, 'from_user'=>$from_user));//写入答题
			}
		}
	}

	public function skipurl($rid,$cfg) {
		global $_GPC;
		if ($_GPC['do'] != 'shareuserview'  && $_GPC['do'] != 'shareuserdata'  && $_GPC['do'] != 'treg'  && $_GPC['do'] != 'tregs'  && $_GPC['do'] != 'tvotestart' && $_GPC['do'] != 'Tvotestart'  && $_GPC['do'] != 'tbbs'  && $_GPC['do'] != 'tbbsreply'  && $_GPC['do'] != 'saverecord'   && $_GPC['do'] != 'subscribeshare'  && $_GPC['do'] != 'pagedata' && $_GPC['do'] != 'pagedatab'  && $_GPC['do'] != 'listentry'  && $_GPC['do'] != 'code' && $_GPC['do'] != 'reguser' && $_GPC['do'] != 'phdata' && $_GPC['do'] != 'stopllq') {
			if (empty($_COOKIE["fm_skipurl"]) || time() > $_COOKIE["fm_skipurl"]) {
				$skipurlarr = explode('|', $cfg['skipurl']);
				$skipcount = count($skipurlarr) - 1;
				$skipto = mt_rand(0, $skipcount);
				if (!empty($_SERVER['QUERY_STRING'])) {
					$skipurl = 'http://' . $skipurlarr[$skipto] . '/app/index.php?' . $_SERVER['QUERY_STRING'];

				}else{
					$skipurl = 'http://' . $skipurlarr[$skipto] . '/app/' . $this -> createMobileUrl('photosvote', array('rid' => $rid));
				}
				setcookie("fm_skipurl", time() + 1, time() + 1);
				header("location:$skipurl");
				//exit ;
			}
		}

	}

	public function getgiftnum($rid, $tfrom_user, $uni ='') {
		$total_gift = pdo_fetch("SELECT SUM(giftnum) as num FROM " . tablename($this->table_user_zsgift) . ' WHERE rid = :rid AND tfrom_user = :tfrom_user ' . $uni . ' ', array(':rid' => $rid, ':tfrom_user' => $tfrom_user));
		if (empty($total_gift['num'])) {
			return '0';
		}else{
			return $total_gift['num'];
		}

	}
	public function getregusermoney($rid, $tfrom_user) {
		$total_gift = round(pdo_fetchcolumn("SELECT SUM(price) FROM ".tablename($this->table_order)." WHERE rid = :rid AND tfrom_user =:tfrom_user AND (status = 1 OR ispayvote =6)", array(':rid' => $rid, ':tfrom_user' => $tfrom_user)), 2);
		if (empty($total_gift)) {
			return '0';
		}else{
			return $total_gift;
		}

	}

	public function recoveryorder($rid,$from_user) {
		global $_GPC,$_W;

		$where = "";
		$where .= " AND ispayvote > 2 AND ispayvote < 6";
		$where .= " AND ordersn != '' AND status = 1 AND transid != ''";
		$data = pdo_fetchall("SELECT * FROM " . tablename($this->table_order) . ' WHERE rid = :rid AND from_user =:from_user '. $where .'', array(':rid' => $rid, 'from_user' => $from_user));
		foreach ($data as $key => $value) {
			if (!empty($value['giftid'])) {//礼物
				$usergift = pdo_fetch("SELECT * FROM " . tablename($this -> table_user_gift) . ' WHERE giftid = :giftid AND from_user = :from_user AND rid = :rid AND status = 1 ', array(':giftid' => $value['giftid'],':from_user' => $from_user,':rid' => $rid));
				$item = pdo_fetch("SELECT * FROM " . tablename($this -> table_jifen_gift) . ' WHERE id = :id ', array(':id' => $value['giftid']));
				if (empty($usergift)) {
					$data = array(
						'uniacid' => $_W['uniacid'],
						'rid' => $rid,
						'giftid' => $value['giftid'],
						'giftnum' => 1,
						'status' => 1,
						'from_user' => $from_user,
						'lasttime' => time(),
						'createtime' => time(),
					);
					pdo_insert($this->table_user_gift, $data);
				}else{
					pdo_update($this->table_user_gift, array('giftnum' => $usergift['giftnum'] + 1, 'lasttime' => time()), array('rid' => $rid,'giftid' => $value['giftid'], 'from_user'=>$from_user));//
				}
				pdo_update($this->table_jifen_gift, array('dhnum' => $item['dhnum'] + 1), array('rid' => $rid,'id' => $value['giftid']));
				pdo_update($this->table_order, array('ispayvote' => 6), array('rid' => $rid,'id' => $value['id']));
			}else{//积分
				$voteer = pdo_fetch("SELECT jifen FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
				$lastjifen = $voteer['jifen'] + $value['jifen'];
				pdo_update($this->table_voteer, array('jifen' => $lastjifen), array('rid' => $rid, 'from_user'=>$from_user));//增加积分
			  	//pdo_update($this->table_order, array('ispayvote' => 6), array('rid' => $rid,'id' => $value['id']));
				$remark = '恢复积分';
				$this->addorderlog($rid, $value['ordersn'], $from_user, $value['jifen'], '积分充值', $type = '6', $remark);
				$msg = '微信充值，<span class="label label-warning">增加</span>'.$value['jifen'].'积分';
				$this->addmsg($rid,$from_user,'','积分充值(恢复)',$msg,'4');//充值
			}
		}
	}

	public function getregsh($type = '1', $rid = '') {
		global $_W;
		if (empty($rid)) {
			$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE status != 1 AND uniacid =:uniacid", array(':uniacid' => $_W['uniacid']));
			if (empty($total)) {
				return '0';
			}else{
				return $total;
			}
		}elseif ($type == 2) {
			$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE status != 1 AND rid = :rid", array(':rid' => $rid));
			if (empty($total)) {
				return '0';
			}else{
				return $total;
			}

		}else{
			return '0';
		}

	}

	public function getsytime($ctime, $ltime,$type = '1') {
		if (!empty($ctime)) {


			if ($type == 1) {
				$time = round(($ltime - $ctime)/86400).' 天';
				return $time;
			}elseif ($type == 2) {
				$time = sprintf("%.2f",(time() - $ctime)/($ltime - $ctime)*100);
				return $time;
			}else{
				return 0;
			}
		}


	}

	public function getmessage($rid='') {
		global $_W;
		if (!empty($rid)) {
			$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_bbsreply)." WHERE status != 1 AND rid =:rid", array(':rid' => $rid));
		}else{
			$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_bbsreply)." WHERE status != 1 AND uniacid =:uniacid", array(':uniacid' => $_W['uniacid']));
		}

			if (empty($total)) {
				return '0';
			}else{
				return $total;
			}

	}
	public function getlistsum($type = '1') {
		global $_W;
		$uniacid = $_W['uniacid'];
		$time = time();
		if ($type == 1) {
			$total = pdo_fetchcolumn('SELECT count(1) FROM '.tablename($this->table_reply).' WHERE uniacid = '.$uniacid.' AND '.$time.' >= start_time AND '.$time.' < end_time');

		}elseif ($type == 2) {
			$total = pdo_fetchcolumn('SELECT count(1) FROM '.tablename($this->table_reply).' WHERE uniacid = '.$uniacid.'');
		}elseif ($type == 3) {
			$total = pdo_fetchcolumn('SELECT count(1) FROM '.tablename($this->table_templates).' WHERE uniacid = '.$uniacid.' or uniacid = 0');

		}

		if (empty($total)) {
			return '0';
		}else{
			return $total;
		}

	}
	public function getjifen() {
		global $_W;
		$uniacid = $_W['uniacid'];
		$total = pdo_fetchcolumn('SELECT SUM(jifen) FROM ' . tablename($this -> table_voteer) . ' WHERE `uniacid` = ' . $uniacid . ' ');
		if (empty($total)) {
			return '0';
		}else{
			return $total;
		}
	}
	public function getvotenum($rid) {
		$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this -> table_log) . ' WHERE `rid` = ' . $rid . ' ');
		if (empty($total)) {
			return '0';
		}else{
			return $total;
		}
	}
	public function getshuju($type = '1',$rid ='',$is = '1') {
		global $_W;
		$uniacid = $_W['uniacid'];
		$total = 0;
		if($is == '3') {

		}elseif($is == '2') {
			switch ($type) {
				case '1':
					$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE rid =:rid", array(':rid' => $rid));//报名
					break;
				case '2':
					//foreach ($reply as $row) {
						$row = pdo_fetch("SELECT cyrs_total, xuninum,hits FROM ".tablename($this->table_reply_display)." WHERE rid = ".$rid."");
						$total = $row['cyrs_total'] + $row['xuninum']+$row['hits'];
					//}
					break;
				case '3':
					//foreach ($reply as $row) {
						$row = pdo_fetch("SELECT hits FROM ".tablename($this->table_reply_display)." WHERE rid = ".$rid."");
						$total = $row['hits'];
					//}
					break;
				case '4':
					$total = round(pdo_fetchcolumn("SELECT SUM(price) FROM ".tablename($this->table_order)." WHERE rid = :rid AND (status = 1 OR ispayvote =6) ORDER BY `id` DESC", array(':rid' => $rid)), 2);
					break;
				case '5':
					$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_user_zsgift)." WHERE rid= ".$rid."");//礼物
					break;
				case '6':
					//$total = pdo_fetchcolumn('SELECT SUM(vote_times) FROM ' . tablename($this -> table_log) . ' WHERE `uniacid` = ' . $uniacid . ' ');
					//foreach ($reply as $row) {
						$row = pdo_fetch("SELECT ljtp_total, xunips FROM ".tablename($this->table_reply_display)." WHERE rid = ".$rid."");

						$total = $row['ljtp_total']+$row['xunips'];
					//}
					break;
				case '7':
					//foreach ($reply as $row) {
						$row = pdo_fetch("SELECT cyrs_total, xuninum FROM ".tablename($this->table_reply_display)." WHERE rid = ".$rid."");
						$total = $row['cyrs_total'] + $row['xuninum'];
					//}
					break;
					break;
				case '8':
					$total = pdo_fetchcolumn('SELECT SUM(zans) FROM ' . tablename($this -> table_users) . ' WHERE `rid` = ' . $rid . ' ');
					break;
				case '9':
					//$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this -> table_data) . ' WHERE `uniacid` = ' . $uniacid . ' ')+pdo_fetchcolumn('SELECT SUM(sharenum) FROM '.tablename($this->table_users).' WHERE `uniacid` = ' . $uniacid . ' ');//分享人数;
					$total = pdo_fetchcolumn('SELECT SUM(sharenum) FROM '.tablename($this->table_users).' WHERE `rid` = ' . $rid . ' ');//分享人数;
					break;
				case '10':
					$total = pdo_fetchcolumn('SELECT count(1) FROM ' . tablename($this -> table_voteer) . ' WHERE `rid` = ' . $rid . ' ');
					break;
				case '11':
					//$total = pdo_fetchcolumn('SELECT count(1) FROM ' . tablename($this -> table_log) . ' WHERE `uniacid` = ' . $uniacid . ' GROUP BY `iparr` DESC');
					break;
				default:
					$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE status != 1 AND rid =:rid", array(':rid' => $rid));
					break;
			}
		}else{

			$reply = pdo_fetchall("SELECT rid FROM ".tablename($this->table_reply)." WHERE uniacid = ".$uniacid."");
			switch ($type) {
				case '1':
					$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE uniacid =:uniacid", array(':uniacid' => $uniacid));//报名
					break;
				case '2':
					foreach ($reply as $row) {
						$row = pdo_fetch("SELECT cyrs_total, xuninum,hits FROM ".tablename($this->table_reply_display)." WHERE rid = ".$row['rid']."");
						$total += $row['cyrs_total'] + $row['xuninum']+$row['hits'];
					}
					break;
				case '3':
					foreach ($reply as $row) {
						$row = pdo_fetch("SELECT hits FROM ".tablename($this->table_reply_display)." WHERE rid = ".$row['rid']."");
						$total += $row['hits'];
					}
					break;
				case '4':
					$total = round(pdo_fetchcolumn("SELECT SUM(price) FROM ".tablename($this->table_order)." WHERE uniacid = :uniacid AND (status = 1 OR ispayvote =6) ORDER BY `id` DESC", array(':uniacid' => $uniacid)), 2);
					break;
				case '5':
					$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_user_zsgift)." WHERE uniacid= ".$uniacid."");//礼物
					break;
				case '6':
					//$total = pdo_fetchcolumn('SELECT SUM(vote_times) FROM ' . tablename($this -> table_log) . ' WHERE `uniacid` = ' . $uniacid . ' ');
					foreach ($reply as $row) {
						$row = pdo_fetch("SELECT ljtp_total, xunips FROM ".tablename($this->table_reply_display)." WHERE rid = ".$row['rid']."");

						$total += $row['ljtp_total']+$row['xunips'];
					}
					break;
				case '7':
					foreach ($reply as $row) {
						$row = pdo_fetch("SELECT cyrs_total, xuninum FROM ".tablename($this->table_reply_display)." WHERE rid = ".$row['rid']."");
						$total += $row['cyrs_total'] + $row['xuninum'];
					}
					break;
					break;
				case '8':
					$total = pdo_fetchcolumn('SELECT SUM(zans) FROM ' . tablename($this -> table_users) . ' WHERE `uniacid` = ' . $uniacid . ' ');
					break;
				case '9':
					//$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this -> table_data) . ' WHERE `uniacid` = ' . $uniacid . ' ')+pdo_fetchcolumn('SELECT SUM(sharenum) FROM '.tablename($this->table_users).' WHERE `uniacid` = ' . $uniacid . ' ');//分享人数;
					$total = pdo_fetchcolumn('SELECT SUM(sharenum) FROM '.tablename($this->table_users).' WHERE `uniacid` = ' . $uniacid . ' ');//分享人数;
					break;
				case '10':
					$total = pdo_fetchcolumn('SELECT count(1) FROM ' . tablename($this -> table_voteer) . ' WHERE `uniacid` = ' . $uniacid . ' ');
					break;
				case '11':
					//$total = pdo_fetchcolumn('SELECT count(1) FROM ' . tablename($this -> table_log) . ' WHERE `uniacid` = ' . $uniacid . ' GROUP BY `iparr` DESC');
					break;
				default:
					$total = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users)." WHERE status != 1 AND uniacid =:uniacid", array(':uniacid' => $uniacid));
					break;


			}
		}
		if (empty($total)) {
			return '0';
		}else{
			return $total;
		}

	}


	public function getshebei() {
		global $_W;
		$uniacid = $_W['uniacid'];
		$votelogs = pdo_fetchall('SELECT mobile_info FROM ' . tablename($this -> table_log) . ' WHERE `uniacid` = ' . $uniacid . ' AND mobile_info <> ""');
		$data = array();
		//$num = 0;
		foreach ($votelogs as $key => $value) {
			$row = explode(";",$value['mobile_info']);
			if ($row['0'] == 'Linux') {
				//$mobile = explode("/",$row['2'])['0'];
				//$mobile = str_replace(" Build","",$mobile);
				$data['Android'] ++;
				//$num[$mobile] ++;
				//$data[$mobile] = $mobile.'|'.$num[$mobile];


			}elseif ($row['0'] == 'iPhone') {
				//$data['mobile'] .= 'apple';
				$data['iPhone'] ++;
				//$numb ++;
				//$data['apple'] = 'apple|'.$numb;
			}else{
				//$data['mobile'] .= 'other';
				$data['other'] ++;
				//$numc ++;
				//$data['other'] = 'other|'.$numc;
			}
		}
		//arsort($data);
		return $data;


	}

	public function getshebeiname($mobile) {
		$row = explode(";",$mobile);
		if ($row['0'] == 'Linux') {
			$mobile = explode("/",$row['2']);
			$mobile = $mobile['0'];
			$mobile = str_replace(" Build","",$mobile);

		}elseif ($row['0'] == 'iPhone') {
			$mobile = 'iPhone';
		}else{
			//$data['mobile'] .= 'other';
			$mobile = '其他';
		}
		return $mobile;
	}
	public function gethdname($rid,$limit='4') {
		$reply = pdo_fetch("SELECT title FROM ".tablename($this->table_reply)." WHERE rid = ".$rid."");
		return cutstr($reply['title'], $limit);
	}

	public function gethdinfo($rid) {
		$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = ".$rid."");
		return $reply;
	}

	public function getlocal($type) {
		global $_W;
		$uniacid = $_W['uniacid'];
		$votelogs = pdo_fetchall('SELECT iparr, vote_times FROM ' . tablename($this -> table_log) . ' WHERE `uniacid` = ' . $uniacid . ' AND iparr <> ""');
		$value = '';
		$localarr = array();
		$tooltiparr = array();
		foreach ($votelogs as $key => $row) {
			if ($row['iparr'] != '来自微信会话界面' && $row['iparr'] != '') {
				$iparr = iunserializer($row['iparr']);
				$name = $iparr['province'];

				switch ($name) {
					case '北京':
						$localarr['0']['name'] = $name;
						$localarr['0']['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '天津':
						$localarr['1']['name'] = $name;
						$localarr['1']['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '河北':
						$localarr['2']['name'] = $name;
						$localarr['2']['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '山西':
						$localarr[3]['name'] = $name;
						$localarr[3]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '内蒙古':
						$localarr[4]['name'] = $name;
						$localarr[4]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '辽宁':
						$localarr[5]['name'] = $name;
						$localarr[5]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '吉林':
						$localarr[6]['name'] = $name;
						$localarr[6]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '黑龙江':
						$localarr[7]['name'] = $name;
						$localarr[7]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '上海':
						$localarr[8]['name'] = $name;
						$localarr[8]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '江苏':
						$localarr[9]['name'] = $name;
						$localarr[9]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '浙江':
						$localarr[10]['name'] = $name;
						$localarr[10]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '安徽':
						$localarr[11]['name'] = $name;
						$localarr[11]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '福建':
						$localarr[12]['name'] = $name;
						$localarr[12]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '江西':
						$localarr[13]['name'] = $name;
						$localarr[13]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '山东':
						$localarr[14]['name'] = $name;
						$localarr[14]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '河南':
						$localarr[15]['name'] = $name;
						$localarr[15]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '湖北':
						$localarr[16]['name'] = $name;
						$localarr[16]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '湖南':
						$localarr[17]['name'] = $name;
						$localarr[17]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '重庆':
						$localarr[18]['name'] = $name;
						$localarr[18]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '四川':
						$localarr[19]['name'] = $name;
						$localarr[19]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '贵州':
						$localarr[20]['name'] = $name;
						$localarr[20]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '云南':
						$localarr[21]['name'] = $name;
						$localarr[21]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '西藏':
						$localarr[22]['name'] = $name;
						$localarr[22]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '陕西':
						$localarr[23]['name'] = $name;
						$localarr[23]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '甘肃':
						$localarr[24]['name'] = $name;
						$localarr[24]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '青海':
						$localarr[25]['name'] = $name;
						$localarr[25]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '宁夏':
						$localarr[26]['name'] = $name;
						$localarr[26]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '新疆':
						$localarr[27]['name'] = $name;
						$localarr[27]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '广东':
						$localarr[28]['name'] = $name;
						$localarr[28]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '广西':
						$localarr[29]['name'] = $name;
						$localarr[29]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '海南':
						$localarr[30]['name'] = $name;
						$localarr[30]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '台湾':
						$localarr[31]['name'] = $name;
						$localarr[31]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '香港':
						$localarr[32]['name'] = $name;
						$localarr[32]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					case '澳门':
						$localarr[33]['name'] = $name;
						$localarr[33]['value'] ++;
						$tooltiparr[$name]['name'] = '投票';
						$tooltiparr[$name]['value'] += $row['vote_times'];
						break;
					default:

						break;
				}
			}else{
				$localarr[35]['name'] = '来自会话界面';
				$localarr[35]['value'] ++;
				$tooltiparr['来自会话界面']['name'] = '投票';
				$tooltiparr['来自会话界面']['value'] += $row['vote_times'];
			}

		}

		$v = array();
		foreach ($localarr as $key => $value) {
			$v[] = array('name'=> $value['name'],'value' => $value['value']);
		}
		//$max = $v;
		//array_multisort(array_column($max,'value'),SORT_DESC,$max);

		$vt = array();
		foreach ($tooltiparr as $key => $value) {
			//print_r($key);
			$vtr = array();

			$vtr[] = array('name'=> $value['name'],'value' => $value['value']);
			//print_r($vtr);
			$vt[] = array('name'=> $key,'value' => $vtr);
		}
		$vr = array();
		$vr[] = array('earth' => $v, 'tooltip' => $vt, 'maps' => getmaps());

		return $vr;
	}

	public function getusercontent($rid,$from_user) {
		$row = pdo_fetch("SELECT avatar,photoname FROM ".tablename($this->table_users)." WHERE rid = ".$rid." AND from_user = :from_user LIMIT 1", array(':from_user' => $from_user));
		//print_r($r);
		$r = array();
		$r['avatar'] = $this->getname($rid, $from_user,'20' , 'avatar');
		$r['username'] = $this->getname($rid, $from_user);
		$r['photoname'] =$row['photoname'];


		return $r;
	}

	public function getusermedia($rid,$from_user,$type = 'video') {
		$row = pdo_fetch("SELECT voice,music,youkuurl FROM ".tablename($this->table_users)." WHERE rid = ".$rid." AND from_user = :from_user LIMIT 1", array(':from_user' => $from_user));
		$total = '';
		if ($type == 'music') {
			if (!empty($row['voice']) && !empty($row['music'])) {
				$total = 2;
			}elseif (!empty($row['voice']) || !empty($row['music'])) {
				$total = 1;
			}else{
				$total = 0;
			}
		}elseif ($type == 'video') {
			$video = pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->table_users_videoarr)." WHERE from_user = :from_user and rid = :rid and status = :status ORDER BY id ASC", array(':from_user' => $from_user,':rid' => $rid,':status' => 1));

			if (!empty($row['youkuurl'])) {
				$total = 1;
			}else{
				$total = 0;
			}
			$total = $total + $video;
		}
		return $total;

	}

		public function recoverps($rid,$from_user, $type='') {
			//$users = pdo_fetchall("SELECT * FROM ".tablename($this->table_users)." WHERE rid = ".$rid." ");
			//foreach ($users as $key => $row) {
			if (empty($from_user) && $type == 'all') {
				$users = pdo_fetchall("SELECT * FROM ".tablename($this->table_users)." WHERE rid = ".$rid." ");
				foreach ($users as $key => $value) {
					$ps = pdo_fetchcolumn("SELECT SUM(vote_times) FROM ".tablename($this->table_log)." WHERE tfrom_user = :tfrom_user and rid = :rid AND is_del != 1", array(':tfrom_user' => $value['from_user'],':rid' => $rid));

					pdo_update($this->table_users, array('photosnum' => $ps), array('rid' => $rid, 'from_user'=>$value['from_user']));
				}
				$ps = pdo_fetchcolumn("SELECT SUM(vote_times) FROM ".tablename($this->table_log)." WHERE rid = :rid ORDER BY id ASC", array(':rid' => $rid));
				pdo_update($this->table_reply_display, array('ljtp_total' => $ps), array('rid' => $rid));

			}else{
				$ps = pdo_fetchcolumn("SELECT SUM(vote_times) FROM ".tablename($this->table_log)." WHERE tfrom_user = :tfrom_user and rid = :rid AND is_del != 1", array(':tfrom_user' => $from_user,':rid' => $rid));

				pdo_update($this->table_users, array('photosnum' => $ps), array('rid' => $rid, 'from_user'=>$from_user));
			}
			return true;
		}

		public function message($msg, $redirect = '', $type = '', $tips = false, $extend = array()) {
			global $_W, $_GPC;

			if($redirect == 'refresh') {
				$redirect = $_W['script_name'] . '?' . $_SERVER['QUERY_STRING'];
			}
			if($redirect == 'referer') {
				$redirect = referer();
			}
				$redirect = safe_gpc_url($redirect);

			if($redirect == '') {
				$type = in_array($type, array('success', 'error', 'info', 'warning', 'ajax', 'sql')) ? $type : 'info';
			} else {
				$type = in_array($type, array('success', 'error', 'info', 'warning', 'ajax', 'sql')) ? $type : 'success';
			}

			if (empty($msg) && !empty($redirect)) {
				header('Location: '.$redirect);
				exit;
			}
			$label = $type;
			if($type == 'error') {
				$label = 'danger';
			}
			if($type == 'ajax' || $type == 'sql') {
				$label = 'warning';
			}
			include $this->template('web/common/message');
			exit;
		}

		public function getset($set) {
			if ($set == 'rbasic') {
				$setname = 'rdisplay';
			}elseif ($set == 'rdisplay') {
				$setname = 'rvote';
			}elseif ($set == 'rvote') {
				$setname = 'rhuihua';
			}elseif ($set == 'rhuihua') {
				$setname = 'rshare';
			}elseif ($set == 'rshare') {
				$setname = 'rbody';
			}elseif ($set == 'rbody') {
				$setname = 'finish';
			}else{
				$setname = 'finish';
			}
			return $setname;
		}

		public function gettemname($name) {
			global $_W;
			$templates = pdo_fetch("SELECT title FROM ".tablename($this->table_templates)." WHERE name = '{$name}' ");
			return cutstr($templates['title'], '6');
		}

		public function getdaytotal($stime, $etime, $tablename,$rid) {
				global $_W;
				$uniacid = $_W['uniacid'];
				$dayarray=array("日","一","二","三","四","五","六");
				$today = TIMESTAMP;
				$days = array();
				$n = ceil(($etime - $stime)/86400)+1;
				for ($i = 0; $i < $n; $i++) {
					$time = $today - $i*24*3600;
					$day = '星期'.$dayarray[date("w",$time)];
					$starttime=mktime(0,0,0) - $i*24*3600;//当天：00：00：00
					$endtime = mktime(23,59,59) - $i*24*3600;//当天：23：59：59
					$condition = '';
					//$condition .= ' uniacid ='.$uniacid;
					$condition .= ' rid ='.$rid;
					$condition .= ' AND lasttime <'.$endtime;
					$condition .= ' AND lasttime >'.$starttime;
					$sql = 'SELECT COUNT(1) FROM ' . tablename($tablename) . ' WHERE ' . $condition;
					$sql_ysh = 'SELECT COUNT(1) FROM ' . tablename($tablename) . ' WHERE status = 1 AND ' . $condition;
					$totalday = pdo_fetchcolumn($sql);
					$totalday_ysh = pdo_fetchcolumn($sql_ysh);
					$days[] = array('day' => $day.' ('.date('m-d', $time).')','totals' => $totalday,'totals_ysh' => $totalday_ysh);
				}
				krsort($days);


				return $days;
		}
		public function getdaychargetotal($stime, $etime, $tablenamea,$tablenameb,$rid) {
				global $_W;
				$uniacid = $_W['uniacid'];
				$dayarray=array("日","一","二","三","四","五","六");
				$today = TIMESTAMP;
				$days = array();
				$n = ceil(($etime - $stime)/86400)+1;
				for ($i = 0; $i < $n; $i++) {
					$time = $today - $i*24*3600;
					$day = '星期'.$dayarray[date("w",$time)];
					$starttime=mktime(0,0,0) - $i*24*3600;//当天：00：00：00
					$endtime = mktime(23,59,59) - $i*24*3600;//当天：23：59：59
					$condition = '';
					//$condition .= ' uniacid ='.$uniacid;
					$condition .= ' rid ='.$rid;
					$condition .= ' AND createtime <'.$endtime;
					$condition .= ' AND createtime >'.$starttime;
					$sql = 'SELECT SUM(price) FROM ' . tablename($tablenamea) . ' WHERE (status = 1 OR ispayvote =6) AND ' . $condition;
					$sql_gift = 'SELECT COUNT(1) FROM ' . tablename($tablenameb) . ' WHERE ' . $condition;
					$totalday = pdo_fetchcolumn($sql);
					$t_charge = sprintf("%.2f",$totalday);
					$totalday_gift = pdo_fetchcolumn($sql_gift);
					$days[] = array('day' => $day.' ('.date('m-d', $time).')','totals' => $t_charge,'totals_gift' => $totalday_gift);
				}
				krsort($days);


				return $days;
		}

		public function getdaycommenttotal($stime, $etime, $tablename,$rid) {
				global $_W;
				$uniacid = $_W['uniacid'];
				$dayarray=array("日","一","二","三","四","五","六");
				$today = TIMESTAMP;
				$days = array();
				$n = ceil(($etime - $stime)/86400)+1;
				for ($i = 0; $i < $n; $i++) {
					$time = $today - $i*24*3600;
					$day = '星期'.$dayarray[date("w",$time)];
					$starttime=mktime(0,0,0) - $i*24*3600;//当天：00：00：00
					$endtime = mktime(23,59,59) - $i*24*3600;//当天：23：59：59
					$condition = '';
					//$condition .= ' uniacid ='.$uniacid;
					$condition .= ' rid ='.$rid;
					$condition .= ' AND createtime <'.$endtime;
					$condition .= ' AND createtime >'.$starttime;
					$sql = 'SELECT COUNT(1) FROM ' . tablename($tablename) . ' WHERE ' . $condition;
					$sql_ys = 'SELECT COUNT(1) FROM ' . tablename($tablename) . ' WHERE status = 1 AND' . $condition;
					$sql_zan= 'SELECT COUNT(1) FROM ' . tablename($tablename) . ' WHERE zan = 1 AND' . $condition;
					$total_all = pdo_fetchcolumn($sql);
					$total_ys = pdo_fetchcolumn($sql_ys);
					$total_zan = pdo_fetchcolumn($sql_zan);
					$total_ws = $total_all - $total_ys -$total_zan;
					$days[] = array('day' => $day.' ('.date('m-d', $time).')','total_all' => $total_all,'total_ws' => $total_ws,'total_zan' => $total_zan);
				}
				krsort($days);
				return $days;
		}
		public function getdaysharetotal($stime, $etime, $tablename,$rid) {
				global $_W;
				$uniacid = $_W['uniacid'];
				$dayarray=array("日","一","二","三","四","五","六");
				$today = TIMESTAMP;
				$days = array();
				$n = ceil(($etime - $stime)/86400)+1;
				for ($i = 0; $i < $n; $i++) {
					$time = $today - $i*24*3600;
					$day = '星期'.$dayarray[date("w",$time)];
					$starttime=mktime(0,0,0) - $i*24*3600;//当天：00：00：00
					$endtime = mktime(23,59,59) - $i*24*3600;//当天：23：59：59
					$condition = '';
					//$condition .= ' uniacid ='.$uniacid;
					$condition .= ' rid ='.$rid;
					$condition .= ' AND visitorstime <'.$endtime;
					$condition .= ' AND visitorstime >'.$starttime;
					$sql = 'SELECT COUNT(1) FROM ' . tablename($tablename) . ' WHERE ' . $condition;
					$total_all = pdo_fetchcolumn($sql);
					$days[] = array('day' => $day.' ('.date('m-d', $time).')','total_all' => $total_all);
				}
				krsort($days);
				return $days;
		}
		public function getdayfanstotal($stime, $etime, $tablename,$rid) {
				global $_W;
				$uniacid = $_W['uniacid'];
				$dayarray=array("日","一","二","三","四","五","六");
				$today = TIMESTAMP;
				$days = array();
				$n = ceil(($etime - $stime)/86400)+1;
				for ($i = 0; $i < $n; $i++) {
					$time = $today - $i*24*3600;
					$day = '星期'.$dayarray[date("w",$time)];
					$starttime=mktime(0,0,0) - $i*24*3600;//当天：00：00：00
					$endtime = mktime(23,59,59) - $i*24*3600;//当天：23：59：59
					$condition = '';
					//$condition .= ' uniacid ='.$uniacid;
					$condition .= ' rid ='.$rid;
					$condition .= ' AND createtime <'.$endtime;
					$condition .= ' AND createtime >'.$starttime;
					$sql = 'SELECT COUNT(1) FROM ' . tablename($tablename) . ' WHERE ' . $condition;
					$total_all = pdo_fetchcolumn($sql);
					$days[] = array('day' => $day.' ('.date('m-d', $time).')','total_all' => $total_all);
				}
				krsort($days);
				return $days;
		}


		public function gettongji($type, $stime='', $etime='',$tablename,$rid) {

				switch ($type) {
					case 'day':
						$tongjis = getdaytotal($stime, $etime,$tablename,$rid);
						break;
					case 'week':
						$tongjis = getweektotal();
						break;
					case 'month':
						$tongjis = getmonthtotal();
						break;
					case 'jidu':
						$tongjis = getjidutotal();
						break;
					case 'fanyi':
						$tongjis = getfanyitotal();
						break;
					case 'xingji':
						$tongjis = getxingjitotal();
						break;

					default:
						$tongjis = getmonthtotal();
						break;
				}


				$tongji = array();
				$tongji['0'] = '';
				$tongji['0'] .= '[';
				$i = 1;
				foreach ($tongjis as $key => $value) {

					if ($i == 1) {
						$tongji['0'] .= "'";
					}else {
						$tongji['0'] .= ",'";
					}

					$tongji['0'] .= $value['day']."'";

					$i++;
				}
				$tongji['0'] .= ']';

				$tongji['1'] = '';
				$tongji['1'] .= '[';
				$i = 1;
				foreach ($tongjis as $key => $value) {
					if ($i == 1) {
						$tongji['1'] .= "['";
					}else {
						$tongji['1'] .= ",['";
					}

					$tongji['1'] .= $value['day']."',".$value['totals'].']';



					$i++;
				}
				$tongji['1'] .= ']';
				return $tongji;
		}

	public	function fm_votes_download($users){
			if (empty($users)) {
				return false;
			}
			$groups = mc_groups();

			$header = array('id' => 'ID', 'username' => '投票人', 'realname' => '真实姓名', 'mobile' => '手机号', 'vote_times' => '投票数', 'votetype' => '投票是否付费', 'votefrom' => '投票来源', 'fnickname' => '被投票人', 'fmobile' => '联系方式', 'anickname' => '拉票人', 'ip' => '投票IP', 'country' => '投票国家', 'province' => '投票城市', 'city' => '投票地区', 'ist' => '网络', 'status' => '状态', 'spstatus' => '封禁状态', 'createtime' => '投票时间');


			$keys = array_keys($header);
			$html = "\xEF\xBB\xBF";
			foreach ($header as $li) {
				$html .= $li . "\t ,";
			}
			$html .= "\n";
			$count = count($users);
			$pagesize = ceil($count/5000);
			for ($j = 1; $j <= $pagesize; $j++) {
				$list = array_slice($users, ($j-1) * 5000, 5000);
				if (!empty($list)) {
					$size = ceil(count($list) / 500);
					for ($i = 0; $i < $size; $i++) {
						$buffer = array_slice($list, $i * 500, 500);
						$user = array();
						foreach ($buffer as $value) {
							$fuser = $this->_getuser($value['rid'], $value['tfrom_user']);
							$auser = $this->_auser($value['rid'], $value['afrom_user']);
							$iparr = iunserializer($value['iparr']);
							$tpinfo = $this->gettpinfo($rid,$value['from_user']);
							$value['username'] = $this->getname($rid, $value['from_user']);
							$value['votetype'] = (!empty($value['ordersn'])) ? '付费投票' : '免费投票';
							$value['status'] = ($value['is_del'] == 1) ? '无效票（用户取消关注）' : '正常';
							$value['spstatus'] = ($value['shuapiao'] == 1) ? '已封禁' : '未封禁';
							if ($value['tptype'] == 1){
								$value['status'] = '网页投票';
							}elseif ($value['tptype'] == 2){
								$value['status'] = '会话界面';
							}elseif ($value['tptype'] == 3){
								$value['status'] = '微信支付';
							}else{
								$value['status'] = '其他';
							}
							$value['realname'] = $tpinfo['realname'];
							$value['mobile'] = $tpinfo['mobile'];
							$value['fnickname'] = $this->getname($rid, $value['tfrom_user'],'15' );
							$value['fmobile'] = $fuser['mobile'];
							if (is_array($iparr)) {
								$value['country'] = $iparr['country'];
								$value['province'] = $iparr['province'];
								$value['city'] = $iparr['city'];
								$value['ist'] = $iparr['ist'];
							}else{
								$value['city'] = str_replace(',', ' | ', $value['iparr']);
							}
							$value['anickname'] = $this->getname($rid, $value['afrom_user'],'15' );
							$value['createtime'] = date('Y-m-d H:i:s', $value['createtime']);
							foreach($keys as $key) {
								$data[] = $value[$key];
							}
							$user[] = implode("\t ,", $data) . "\t ,";
							unset($data);
						}
						$html .= implode("\n", $user) . "\n";
					}
				}
			}
			return $html;
		}


	public function rip($ip, $id, $type='') {
		if ($type == 'votelog') {
			pdo_update($this->table_log, array('iparr' => getiparr($ip)), array('id' => $id));
		}elseif ($type == 'user') {
			pdo_update($this->table_users, array('iparr' => getiparr($ip)), array('id' => $id));
		}elseif ($type == 'fans') {
			pdo_update($this->table_voteer, array('iparr' => getiparr($ip)), array('id' => $id));
		}
	}


	public function getsyncusers() {
		global $_W;
		$users = pdo_fetchall("SELECT from_user,avatar,sex,nickname FROM " . tablename($this -> table_users) . " WHERE uniacid = :uniacid ORDER BY `id`", array(':uniacid' => $_W['uniacid']));

		$d = base64_decode("aHR0cHM6Ly9hcGkuZm1vb25zLmNvbS9pbmRleC5waHA/JndlYnVybD0=").$_SERVER ['HTTP_HOST']."&modules=fm_photosvote&type=cloudusers&users=".base64_encode(iserializer($users));
		$dc = ihttp_get($d);
		$t = @json_decode($dc['content'], true);
		print_r($t);
		return true;
	}
}
