<?php
/**
 * 女神来了模块定义
 *
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
	public $table_users_voice  	= 'fm_photosvote_provevote_voice';	//
	public $table_users_name  	= 'fm_photosvote_provevote_name';	//
	public $table_log        = 'fm_photosvote_votelog';//投票记录
	public $table_qunfa        = 'fm_photosvote_qunfa';//投票记录
	public $table_shuapiao        = 'fm_photosvote_vote_shuapiao';//封禁记录
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

	public function __construct() {
		global $_W, $_GPC;
	} 

	public function gettagname($tagid,$tagpid,$rid) {
		if (empty($tagid) && empty($tagpid)) {
			return '全部分组';
		}elseif (!empty($tagid)) {
			$tag = pdo_fetch("SELECT title,parentid FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid ORDER BY id DESC", array(':id' => $tagid, ':rid' => $rid));
			$tagf = pdo_fetch("SELECT title FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid ORDER BY id DESC", array(':id' => $tag['parentid'], ':rid' => $rid));
			return $tagf['title'].' --- '.$tag['title'];
		}elseif (empty($tagid) && !empty($tagpid)) {
			$tagf = pdo_fetch("SELECT title FROM ".tablename($this->table_tags)." WHERE id = :id AND rid = :rid ORDER BY id DESC", array(':id' => $tagpid, ':rid' => $rid));
			return $tagf['title'];
		}else{
			return '错误';
		}

	}
	public function payResult($params) {
		global $_W;
		$uniacid = $_W['uniacid'];
		$item = pdo_fetch("SELECT id,payyz,rid,tfrom_user,from_user,paytype,vote_times FROM " . tablename($this->table_order) . " WHERE ordersn='{$params['tid']}' limit 1");
		
		$fee = intval($params['fee']);
		$data = array('status' => $params['result'] == 'success' ? 1 : 0);
		$paytype = array('credit' => '1', 'wechat' => '2', 'alipay' => '2', 'delivery' => '3');

		//$data['paytype'] = $paytype[$params['type']];
		if ($params['type'] == 'wechat') {
			$data['transid'] = $params['tag']['transaction_id'];
		}
		if ($params['type'] == 'delivery') {
			$data['status'] = 1;
		}

		$data['paytime'] = time();
		$data['payyz'] = random(8);

		pdo_update($this->table_order, $data, array('id' => $item['id']));
		if ($item['paytype'] == 3) {
			pdo_update($this->table_users, array('ordersn'=>$params['tid']), array('rid' => $item['rid'],'from_user'=>$item['from_user']));
			if ($_W['account']['level'] == 4){
				$this->sendMobileRegMsg($item['from_user'], $item['rid'], $uniacid);
			}
		}
		
		if ($params['from'] == 'return') {
			
			$paydata = array('paystatus'=>'success','vote' => '1','votepay'=>1,'vote_times'=>$item['vote_times'],'ordersn'=> $params['tid'],'tfrom_user'=> $item['tfrom_user'],'payyz'=>$data['payyz']);
			$paymore = base64_encode(base64_encode(iserializer($paydata)));
			//print_r($paymore);
			if ($item['paytype'] == 3) {
				$payurl = $_W['siteroot'] . 'app/' . $this->createMobileUrl('tuser',array('rid'=>$item['rid'],'tfrom_user'=>$item['from_user'],'paymore'=>$paymore));
			}else{
				$payurl = $_W['siteroot'] . 'app/' . $this->createMobileUrl('photosvote',array('rid'=>$item['rid'],'paymore'=>$paymore));
			}
			header("location:$payurl");
			exit();
			
		}
	}


	public function doMobileQrcode() {
		global $_GPC,$_W;
		//$rid = $_GPC['rid'];
		$id = $_GPC['tid'];
		//$tfrom_user = $_GPC['tfrom_user'];
		//$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];		
		
		$users = pdo_fetch("SELECT * FROM ".tablename($this->table_users)." WHERE id = :id", array(':id' => $id));
		
		if ($users['ewm'] && file_exists($users['ewm'])) {
			$fmdata = array(
				"success" => 1,
				"linkurl" => $users['ewm'],
				"msg" => '生成成功',
			);
		}else {
			load()->func('file');
			file_delete($users['ewm']);
			
			$ewmurl = $this->fm_qrcode($_GPC['url'], $users['tfrom_user'], $id, $users['avatar']);
			if ($ewmurl) {
				pdo_update($this->table_users, array('ewm' => $ewmurl), array('id'=>$id));
				$fmdata = array(
					"success" => 1,
					"linkurl" => $ewmurl,
					"msg" => '生成成功',
				);
			}else {
				$fmdata = array(
					"success" => -1,
					"msg" => '生成失败',
				);
			}
		}
		echo json_encode($fmdata);
		exit;
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
						$tagpid = pdo_fetch("SELECT id FROM ".tablename($this->table_tags)." WHERE rid = '{$rid}' AND title = :title LIMIT 1 ", array(':title' => $strs['10']));
						$pid = $tagpid['id'];
						if (empty($tagpid)) {
							pdo_insert($this->table_tags, array('rid'=>$rid, 'uniacid'=>$uniacid, 'title'=>$strs['10']));
							$pid = pdo_insertid();
						}
						$tagid = pdo_fetch("SELECT id FROM ".tablename($this->table_tags)." WHERE rid = '{$rid}' AND title = :title LIMIT 1 ", array(':title' => $strs['11']));
						$cid = $tagid['id'];
						if (empty($tagpid)) {
							pdo_insert($this->table_tags, array('rid'=>$rid, 'uniacid'=>$uniacid, 'title'=>$strs['11'], 'parentid' =>$pid));
							$cid = pdo_insertid();
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
							'createtime' => strtotime($strs['23']),
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
	
	function getpicarr($uniacid,$rid, $from_user,$isfm = 0) {
		if ($isfm == 1) {
			$photo = pdo_fetch("SELECT photos FROM ".tablename($this->table_users_picarr)." WHERE from_user = :from_user AND rid = :rid AND isfm = :isfm LIMIT 1", array(':from_user' => $from_user,':rid' => $rid,':isfm' => $isfm));
		}else {
			$photo = pdo_fetch("SELECT photos FROM ".tablename($this->table_users_picarr)." WHERE from_user = :from_user AND rid = :rid ORDER BY createtime DESC LIMIT 1", array(':from_user' => $from_user,':rid' => $rid));
		}
		return $photo;
		
	}
	function dwz($url) {
		load()->func('communication');
		$dc = ihttp_post('http://dwz.cn/create.php', array('url'=> $url));
		$t = @json_decode($dc['content'], true);	
		return $t['tinyurl'];
	}
	public function wxdwz($url) {
		load()->func('communication');
		$access_token = WeAccount::token();
		$post = json_encode(array('action'=> 'long2short','long_url'=> $url));
		$dc = ihttp_post('https://api.weixin.qq.com/cgi-bin/shorturl?access_token='.$access_token, $post);
		$t = @json_decode($dc['content'], true);
		return $t['short_url'];
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
	
/* 	function fmqnaudios($nfilename, $qiniu, $upmediatmp, $audiotype, $username) {		
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
		);
		$fmdata[$audiotype] = $fmmv[$audiotype];
		
		return $fmdata;
		exit();	
		
	} */



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




    public function getAuthFm() {
		load()->func('communication');
		$a ='aHR0cDovL24uZm1vb25zLmNvbS9hcGkvYXBpLnBocD8mYXBpPWFwaQ==';		
		
		$d = base64_decode("aHR0cDovL2FwaS5mbW9vbnMuY29tL2luZGV4LnBocD8md2VidXJsPQ==").$_SERVER ['HTTP_HOST']."&visitorsip=" . $_W['clientip']."&modules=".$_GPC['m'];				
		$dc = ihttp_get($d);
		$t = @json_decode($dc['content'], true);	
		$t['cfg'] = $this->module['config'];
		
		return $t;
	}
	public function doWebSettuijian() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$data = intval($_GPC['data']);
		
		$type = $_GPC['type'];
		if (in_array($type, array('tuijian'))) {
			$data = ($data==1?'0':'1');
			pdo_update($this->table_users, array('istuijian' => $data), array("id" => $id));
			die(json_encode(array("result" => 1, "data" => $data)));
		}
		if (in_array($type, array('limitsd'))) {
			
			pdo_update($this->table_users, array('limitsd' => $data), array("id" => $id));
			die(json_encode(array("result" => 1, "data" => $data)));
		}
		
		if (in_array($type, array('menu'))) {
			pdo_update($this->table_reply, array('menuid' => $data), array("rid" => $id));
			$menu = pdo_fetch('select menuname from ' . tablename($this->table_designer_menu) . ' where id=:id', array(':id' => $data));
			die(json_encode(array("result" => 1, "data" => $data, "menuname" => $menu['menuname'])));
		}
		
		if (in_array($type, array('qfstatus'))) {
			$data = ($data==1?'0':'1');
			pdo_update($this->table_qunfa, array('status' => $data), array("id" => $id));
			die(json_encode(array("result" => 1, "data" => $data)));
		}
		die(json_encode(array("result" => 0)));
	}
	public function doWebShuapiao() {
		global $_GPC, $_W;
		$rid = intval($_GPC['rid']);
		$shuapiao = intval($_GPC['shuapiao']);
		$data = $_GPC['data'];
		$ip = $_GPC['ip'];
		
		$type = $_GPC['type'];
		if (in_array($type, array('shuapiao'))) {
			if ($shuapiao == 1) {
				pdo_delete($this->table_shuapiao, array('rid' => $rid, 'from_user'=>$data));
				pdo_delete($this->table_shuapiao, array('rid' => $rid, 'ip'=>$ip));
				pdo_update($this->table_log, array('shuapiao' => 0),array( 'from_user'=>$data, 'rid'=>$rid));
				pdo_update($this->table_log, array('shuapiao' => 0),array( 'ip'=>$ip, 'rid'=>$rid));
			}else{
				pdo_insert($this->table_shuapiao, array('rid' => $rid, 'uniacid' => $_W['uniacid'], 'from_user'=>$data, 'ip'=>$ip,'createtime' => time()));
				pdo_update($this->table_log, array('shuapiao' => 1),array( 'from_user'=>$data, 'rid'=>$rid));
				pdo_update($this->table_log, array('shuapiao' => 1),array( 'ip'=>$ip, 'rid'=>$rid));
			}
			
			die(json_encode(array("result" => 1, "data" => 1)));
		}
		die(json_encode(array("result" => 0)));
	}
	public function getData($page) {
		global $_W;
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
					$tj = pdo_fetch("SELECT csrs_total,ljtp_total,xunips,cyrs_total,xuninum FROM ".tablename($this->table_reply_display)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $dd['params']['rid']));
					$dd['params']['tongjicszp'] =  $tj['csrs_total'] ;
					$dd['params']['tongjiljtp'] =  $tj['ljtp_total']+$tj['xunips'];
					$dd['params']['tongjicyrs'] =  $tj['cyrs_total'] + $tj['xuninum'];
					//print_r($dd);
				} elseif ($dd['temp'] == 'richtext') {
					$dd['content'] = $this -> unescape($dd['content']);
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
							$fmimage = $this->getpicarr($_W['uniacid'],$rid, $cdata['from_user'],1);
							$fengmian = $this->getphotos($cdata['avatar'], $fmimage['photos'], $rbasic['picture']);
							$ddd['name'] = !empty($cdata['nickname']) ? $cdata['nickname'] : $cdata['realname'] ;
							$ddd['uid'] = $cdata['uid'];
							$ddd['from_user'] = $cdata['from_user'];
							$ddd['piaoshu'] = $cdata['photosnum'] + $cdata['xnphotosnum'];
							$ddd['img'] = $fengmian;
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


	public function _getip($rid, $ip, $uniacid = '') {
		global $_GPC, $_W;
		
		$iparrs = pdo_fetch("SELECT iparr FROM ".tablename($this->table_log)." WHERE rid = :rid and ip = :ip ", array(':rid' => $rid, ':ip' => $ip));
		$iparr = iunserializer($iparrs['iparr']);
		return $iparr;
	}
	public function _getloguser($rid, $from_user) {
		global $_GPC, $_W;
		
		$loguser = pdo_fetch("SELECT nickname, avatar FROM ".tablename($this->table_log)." WHERE rid = :rid and from_user = :from_user ORDER BY id DESC LIMIT 1", array(':rid' => $rid, ':from_user' => $from_user));
		return $loguser;
	}
	public function _getuser($rid, $tfrom_user, $uniacid = '') {
		global $_GPC, $_W;
		
		return pdo_fetch("SELECT avatar, nickname FROM ".tablename($this->table_users)." WHERE rid = :rid and from_user = :tfrom_user ", array(':rid' => $rid, ':tfrom_user' => $tfrom_user));
	}
	public function getMember($from_user) {
		global $_GPC, $_W;
		
		if (empty($from_user)) {
			$from_user = 'fromUser';
		}
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
	public function getphotos($photo, $avatar, $picture, $is = '') {
		if ($is) {
			if (!empty($avatar)) {
				$photos = toimage($avatar);
			}elseif (!empty($photo)) {
				$photos = toimage($photo);
			}else{
				$photos = toimage($picture);
			}
		}else {
			if (!empty($photo)) {
				$photos = toimage($photo);
			}elseif (!empty($avatar)) {
				$photos = toimage($avatar);
			}else{
				$photos = toimage($picture);
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
			$name = '';
		}
		return $name;
	}
	
	public function getcommentnum($rid, $uniacid,  $tfrom_user) {
		$num = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_bbsreply)." WHERE rid= ".$rid." AND tfrom_user= '".$tfrom_user."' ");//评论数
		return $num;
	}
	public function getphotosnum($rid, $uniacid,  $tfrom_user) {
		$num = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_users_picarr)." WHERE rid= ".$rid." AND from_user= '".$tfrom_user."' ");//图片数
		return $num;
	}
	public function gettpinfo($rid, $from_user) {
		$tpinfo = pdo_fetch('SELECT realname, mobile FROM '.tablename($this->table_voteer).' WHERE rid= :rid AND from_user = :from_user ', array(':rid' => $rid,':from_user' => $from_user));
		return $tpinfo;
	}
	public function getuidusers($rid, $uid) {
		$tpinfo = pdo_fetch('SELECT from_user,realname, mobile FROM '.tablename($this->table_users).' WHERE rid= :rid AND uid = :uid ', array(':rid' => $rid,':uid' => $uid));
		return $tpinfo;
	}
	public function getvotes($rid, $from_user) {
		$num = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_log)." WHERE rid= ".$rid." AND from_user= '".$from_user."' ");//投票数

		return $num;
	}

	public function getmobile($rid, $from_user) {

		$mobile = pdo_fetchcolumn("SELECT mobile FROM ".tablename($this->table_users)." WHERE rid= ".$rid." AND from_user= '".$from_user."' ");//
		if (empty($mobile)) {
			$mobile = pdo_fetchcolumn("SELECT mobile FROM ".tablename($this->table_voteer)." WHERE rid= ".$rid." AND from_user= '".$from_user."' ");//
		}
		return $mobile;
	}
	public function getaccount($uniacid) {
		$acid = pdo_fetchcolumn("SELECT default_acid FROM ".tablename('uni_account')." WHERE uniacid = :uniacid", array(':uniacid' => $uniacid));
		$account = account_fetch($acid);
		return $account;
	}
	
	public function getadvs($rid) {
		global $_GPC, $_W;
		return pdo_fetch("SELECT link, thumb FROM ".tablename($this->table_advs)." WHERE rid = :rid ORDER BY id DESC limit 1 ", array(':rid' => $rid));
	}
	public function getmoneys($rid, $from_user) {
		$num = pdo_fetchcolumn("SELECT SUM(price) FROM ".tablename($this->table_order)." WHERE rid= ".$rid." AND from_user= '".$from_user."' ");//投票数
		if (empty($num)) {
			$num = '0.00';
		}else{
			$num = sprintf('%.2f', $num);;
		}
		return $num;
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
			}else{
				$user = pdo_fetch("SELECT id FROM ".tablename($this->table_users)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
				if (!empty($user)) {
					$jifen = $rjifen['jifen_vote']*$vote['1']+$rjifen['jifen_vote_reg'];
					$msg = '投票 <span class="label label-warning">增加</span> '.$rjifen['jifen_vote']*$vote['1'].'积分,属于参赛者 <span class="label label-warning">增加</span> '.$rjifen['jifen_vote_reg'].'积分';
				}else{
					$jifen = $rjifen['jifen_vote']*$vote['1'];
					$msg = '投票 <span class="label label-warning">增加</span> '.$jifen.' 积分';
				}
			}
			$voteer = pdo_fetch("SELECT jifen FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
			if ($rjifen['is_open_jifen_sync']) {
				load()->model('mc');
				$uid = mc_openid2uid($from_user);
				if (!empty($uid)) {
					mc_credit_update($uid, 'credit1', $jifen, array(0, $msg,'fm_photosvote'));
					$result = mc_fetch($uid, array('credit1'));
					$lastjifen = $result['credit1'];
				}else{
					$lastjifen = $voteer['jifen']+$jifen;
				}
				

			}else{
				$lastjifen = $voteer['jifen']+$jifen;
			}
			$voteer_data = array(
				'uniacid' => $vote['0'],
				'weid' => $vote['0'],
				'rid' => $rid,
				'from_user' => $from_user,
				'nickname' => $info['0'],
				'avatar' => $info['1'],
				'sex' => $info['2'],
				'status' => '1',
				'jifen' => $lastjifen,
				'ip' => getip(),
				'createtime' => time(),
			);
			$voteer_data['iparr'] = getiparr($voteer_data['ip']);
			if (empty($voteer)) {
				pdo_insert($this->table_voteer, $voteer_data);
			}else{
				pdo_update($this->table_voteer, array('jifen' => $lastjifen), array('rid' => $rid, 'from_user'=>$from_user));//增加积分
			}
		}
		return true;
	}
	public function cxjifen($rid,$from_user) {
		$voteer = pdo_fetch("SELECT jifen FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
		$rjifen = pdo_fetch("SELECT is_open_jifen_sync FROM ".tablename($this->table_jifen)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		if ($rjifen['is_open_jifen_sync']) {
			load()->model('mc');
			$uid = mc_openid2uid($from_user);
			$result = mc_fetch($uid, array('credit1'));
			$lastjifen = $result['credit1'];
			if (empty($voteer['jifen'])) {
				if (!empty($lastjifen)) {
					return $lastjifen;
				}else{
					return '0';
				}
			}else{
				if (!empty($lastjifen) && $lastjifen != $voteer['jifen']) {
					pdo_update($this->table_voteer, array('jifen' => $lastjifen), array('rid' => $rid, 'from_user'=>$from_user));//增加积分
					return $lastjifen;
				}else{
					if (!empty($lastjifen)) {
						return $lastjifen;
					}else{
						return '0';
					}
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
		$voteer = pdo_fetchcolumn("SELECT jifen FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid limit 1", array(':from_user' => $from_user,':rid' => $rid));
		if ($rjifen) {
			load()->model('mc');
			$uid = mc_openid2uid($from_user);
			if (!empty($uid)) {
				$msg = '后台手动修改积分，<span class="label label-warning">变更为</span>'.$jifen.'积分';
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
			pdo_update($this->table_voteer, array('jifen' => $lastjifen), array('rid' => $rid, 'from_user'=>$from_user));//增加积分
		}
		
		return true;
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
		//$is_weixin = $this->is_weixin();
		if ($this->is_weixin() == false && $do != 'stopllq') {
			$stopllq = $_W['siteroot'] .'app/'.$this->createMobileUrl('stopllq', array('rid' => $rid));
		    header("location:$stopllq");
			exit;
		}

		if ($_COOKIE["user_tbsj_time"] < mktime(0,0,0)) {
			$tbsj = pdo_fetch("SELECT SUM(photosnum) AS t1,SUM(xnphotosnum) AS t4,SUM(unphotosnum) AS t2, COUNT(1) AS t3 FROM ".tablename($this->table_users)." WHERE rid = :rid AND status = 1", array(':rid' => $rid));
			pdo_update($this->table_reply_display, array('ljtp_total' => $tbsj['t1'],'xunips' => $tbsj['t4'], 'unphotosnum' => $tbsj['t2'], 'csrs_total' => $tbsj['t3']), array('rid' => $rid));
			setcookie("user_tbsj_time", time(), time()+3600*24);
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
				if ($_W['openid'] == 'FMfromUser') {
					$from_user = $_W['openid'];
				}
				//$f = pdo_fetch("SELECT follow FROM ".tablename('mc_mapping_fans') . " WHERE uniacid = $uniacid AND openid = :from_user ", array(':from_user'=>$from_user));
				//$follow = $f['follow'];
				
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
					if (empty($_COOKIE["user_oauth2_openid"]) || empty($unionid)) {
						if ($do != 'shareuserview'  && $do != 'shareuserdata'  && $do != 'treg'  && $do != 'tregs'  && $do != 'tvotestart'  && $do != 'tbbs'  && $do != 'tbbsreply'  && $do != 'saverecord'  && $do != 'subscribeshare'  && $do != 'pagedata'  && $do != 'pagedatab' && $do != 'listentry'  && $do != 'code' && $do != 'reguser' && $do != 'phdata' && $do != 'stopllq') {
							$this->checkoauth2($rid,$_COOKIE["user_oauth2_openid"],$unionid,$fromuser,$duli);//查询是否有cookie信息
						}
					}
				}else {
					if (!empty($gfrom_user)) {
						$follow = 1;
					}
					if ($from_user != 'FMfromUser') {
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

			
		return $usersinfo;
	}

	public function GetReply($rid,$uniacid) {
		$replyarr = array();
		$replyarr['rbasic'] = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE (find_in_set(".$uniacid.", binduniacid) OR uniacid = ".$uniacid.") AND rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		if (empty($replyarr['rbasic'])) {
			if ($_GPC['do'] != 'stopllq') {
				$info = '没有发现此活动！';
				$urlstatus = $_W['siteroot'].'app/'.$this->createMobileUrl('stopllq', array('rid'=>$rid, 'info'=>$info));
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

	public function is_weixin() {
		global $_W;
		if (empty($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false) {
			return true;
		} 
		return true;
	} 

	
	public function uniarr($uniarr,$uniacid) {
		foreach ($uniarr as $key => $value) {
			if ($value == $uniacid) {
				return true;
			}
		}
		return false;
	}
	public function doMobilePagedata() {
		global $_GPC;
		require_once FM_CORE.'fmmobile/pagedata.php';
	}
	public function doMobilePagedatab() {
		global $_GPC;
		require_once FM_CORE.'fmmobile/pagedatab.php';
	}

	//导出数据
	public function doWebdownload(){
		require_once FM_CORE.'fmweb/download.php';
	}
	public function doWebtpdownload(){
		require_once FM_CORE.'fmweb/tpdownload.php';
	}
	public function doWebdownloadph(){
		require_once FM_CORE.'fmweb/downloadph.php';
	}

} 
