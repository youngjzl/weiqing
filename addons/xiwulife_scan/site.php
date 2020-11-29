<?php
defined('IN_IA') or exit('Access Denied');
header("Content-type: text/html; charset=utf-8");
include 'xiwu/Print/HttpClient.class.php';
define('USER', '704011699@qq.com');	//*必填*：飞鹅云后台注册账号
define('UKEY', 'e9nTrfZnxr32wZJa');	//*必填*: 飞鹅云注册账号后生成的UKEY
//以下参数不需要修改
define('IP','api.feieyun.cn');			//接口IP或域名
define('PORT',80);						//接口IP端口
define('PATH','/Api/Open/');		//接口路径
define('STIME', time());			    //公共参数，请求时间
define('SIG', sha1(USER.UKEY.STIME));   //公共参数，请求公钥
require_once 'xiwu/lib/AipSpeech.php';
//百度语音
const APP_ID = '11031189';
const API_KEY = 'rtQm8FCeruv1qhmOU9xtppie';
const SECRET_KEY = '32edd09ba1fa15025caccc078e4eb35d';
class Xiwulife_scanModuleSite extends WeModuleSite {
	//显示logo
	public function doWeblogo() {
		global $_W, $_GPC;
		$item =  pdo_fetch("SELECT title,logo FROM ".tablename('xiwu_scan_store')."WHERE `uniacid` = '{$_W['uniacid']}'");
		if($item){
			$item['logo'] = tomedia($item['logo']);
			return json_encode($item);
		}
	}
	//后台管理
	function ordercon($start,$end){
		global $_W, $_GPC;
		$con = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_order'). "WHERE `time` between ".$start." and ".$end."  and `uniacid` = '{$_W['uniacid']}' and `state` = '1'");
		$conmoney = pdo_fetchcolumn("SELECT sum(payprice) FROM ".tablename('xiwu_scan_order'). "WHERE `time` between ".$start." and ".$end." and `uniacid` = '{$_W['uniacid']}' and `state` = '1'");
		if($conmoney == ''){
			$conmoney = 0;
		}
		$data['con'] = $con;
		$data['conmoney'] = $conmoney;
		return $data;
	}
	public function doWebindex() {
		global $_W, $_GPC;
		$title = "后台管理";
		if($_GPC['sql'] != ''){
			$rel = pdo_query("ALTER TABLE ims_xiwu_scan_card ADD `xianzhi` int(1) DEFAULT '0'");
			if($rel){
				return '成功';
			}else{
				return 'shibai';
			}
		}
		$count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_order'). "WHERE `uniacid` = '{$_W['uniacid']}' and `state` = '1'");
		$countmoney = pdo_fetchcolumn("SELECT sum(payprice) FROM ".tablename('xiwu_scan_order'). "WHERE `uniacid` = '{$_W['uniacid']}' and  `state` = '1'");
		//php获取今日开始时间戳和结束时间戳
		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$today = $this->ordercon($beginToday,$endToday);
		//php获取上周起始时间戳和结束时间戳
		$beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
		$endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
		$Lastweek = $this->ordercon($beginLastweek,$endLastweek);
		//php获取本月起始时间戳和结束时间戳
		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
		$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
		$Thismonth = $this->ordercon($beginThismonth,$endThismonth);
		include $this->template('index');
	}
	public function doWebechartsgoods() {
		global $_W, $_GPC;
		$goodlist = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_goods'). "WHERE `uniacid` = '{$_W['uniacid']}' ORDER BY id ASC");
		return json_encode($goodlist);
	}
	public function doWebechartsorder() {
		global $_W, $_GPC;
		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$data['count'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_order')."WHERE `uniacid` = '{$_W['uniacid']}' and `state` = '1' ");
		$data['list'] = pdo_fetchcolumn("SELECT  COUNT(*) FROM ".tablename('xiwu_scan_order')."WHERE `time` between ".$beginToday." and ".$endToday." and `uniacid` = '{$_W['uniacid']}' and `state` = '1' ");
		return json_encode($data);
	}
	public function doWebecharts() {
		global $_W, $_GPC;
		$title = "图表";
		include $this->template('echarts');
	}
	//桌面码
	public function doWebprogram() {
		global $_W, $_GPC;
		$title = "桌面码管理";
		load()->func('communication');
		$programlist = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_program')."WHERE `uniacid` = '{$_W['uniacid']}'");
		if($_GPC['sub'] == 'program' && $_GPC['desknum'] != ''){
			$rel = pdo_delete('xiwu_scan_program', array('uniacid' => $_W['uniacid']));
			$color = $this->hex2rgb($_GPC['deskcolor']);
			for($i=0; $i<=$_GPC['desknum']; $i++){
				$path="bluemoon/pages/drink/drink?table=".$i;
				$width=400;
				if($_GPC['desktype'] == '1'){
					$data = array(
						'scene' => 'xiwulife_scan/zhuomian',
						'path'	=> $path,
						'width' => $width,
						'auto_color' => false,
						'line_color' => $color,
					);
					$url="https://api.weixin.qq.com/wxa/getwxacode?access_token=".$this->access_token();
				}else{
					$data = array(
						'path'	=> $path,
						'width' => $width,
					);
					$url="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$this->access_token();
				}
				$response = ihttp_post($url, json_encode($data));
				$urlfile="/addons/xiwulife_scan/programimg/";
				$new_file = $_SERVER['DOCUMENT_ROOT'].$urlfile;
				if(!file_exists($new_file)){
					//检查是否有该文件夹，如果没有就创建，并给予最高权限
					mkdir($new_file, 0777, true);
				}
				$img = $_W['uniacid']."_".$i.'.jpg';
				$fileput = file_put_contents($new_file.$img, $response['content']);
				pdo_insert('xiwu_scan_program',array('uniacid'	=> $_W['uniacid'],'number' => $i,'url' => $urlfile.$img, 'filename' => $img));
			}
		}
		if($_GPC['sub'] == 'title'){
			$rel = pdo_update('xiwu_scan_program',array('title' => $_GPC['title']), array('id' => $_GPC['id']));
			if($rel){
				return '提交成功！';
			}else{
				return '提交失败！';
			}
		}
		include $this->template('program');
	}
	//桌面码颜色十六进制转RGB
    function hex2rgb($hexColor) {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {
            $rgb = array(
                'r' => hexdec(substr($color, 0, 2)),
                'g' => hexdec(substr($color, 2, 2)),
                'b' => hexdec(substr($color, 4, 2))
            );
        } else {
            $color = $hexColor;
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b)
            );
        }
        return $rgb;
    }
	//店铺管理
	public function doWebstore() {
		global $_W, $_GPC;
		$title = "店铺管理";
		$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
		$http = $http_type;
		load()->func('tpl');
		$item =  pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_store')."WHERE `uniacid` = '{$_W['uniacid']}'");
		//添加修改店铺
		if(checksubmit('tijiao')){
			empty($_GPC['title']) && message('请填写名称');
			empty($_GPC['logo']) && message('请上传店铺图');
			empty($_GPC['text']) && message('请填写店铺介绍');
			empty($_GPC['tel']) && message('请填写店铺电话');
			$data = array(
				'uniacid'	=> $_W['uniacid'],
				'title' 	=> $_GPC['title'],
				'logo' 		=> $_GPC['logo'],
				'text' 		=> $_GPC['text'],
				'tel' 		=> $_GPC['tel'],
				'starttime' => $_GPC['starttime'],
				'endtime' 	=> $_GPC['endtime'],
				'lng' 		=> $_GPC['lng'],
				'lat' 		=> $_GPC['lat'],
				'address' 	=> $_GPC['address'],
				'details' 	=> $_GPC['details'],
				'pics'		=> serialize($_GPC['pics']),
			);
			if($item['id'] == ''){
				$rel = pdo_insert('xiwu_scan_store',$data);
				if($rel){
					message('添加成功！',$this->createWebUrl('store'),'success');
				}else{
					message('添加失败！',$this->createWebUrl('store'),'error');
				}
			}else{
				$rel = pdo_update('xiwu_scan_store',$data, array('uniacid' => $_W['uniacid']));
				if($rel){
					message('修改成功！',$this->createWebUrl('store'),'success');
				}else{
					message('修改失败！',$this->createWebUrl('store'),'error');
				}
			}
		}
		include $this->template('store');
	}
	//订单管理
	public function doWeborder() {
		global $_W, $_GPC;
	    $title = "订单管理";
	    $psize = 15;
	    $pindex = max(1, intval($_GPC['page']));
	    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_order')."WHERE `uniacid` = :uniacid",array(':uniacid' => $_W['uniacid']));
	    $list = pdo_fetchall('SELECT * FROM' .tablename('xiwu_scan_order'). 'WHERE `uniacid` = :uniacid ORDER BY id DESC LIMIT '.($pindex - 1) * $psize.",{$psize}", array(':uniacid' => $_W['uniacid']));
	    //搜索
	    if($_GPC['date'] !=''){
	        $starttime = $_GPC['date']['start'];
	        $starttime = strtotime($starttime);
	        $endtime = $_GPC['date']['end'];
	        $endtime = strtotime($endtime) + 86400;
	        $ordertype = $_GPC['ordertype'];
	        $status = $_GPC['status'];
	        $payment = $_GPC['payment'];
	        $state = $_GPC['state'];
	        $where .= "time between ".$starttime." and ".$endtime;
	        $where .= " and `uniacid` = :uniacid";
	        if($ordertype != ''){
	            $where .= " and ordertype = {$_GPC['ordertype']} ";
	        }
	        if($status != ''){
	            $where .= " and status = {$_GPC['status']} ";
	        }
	        if($payment != ''){
	            $where .= " and payment = {$_GPC['payment']} ";
	        }
	        if($state != ''){
	            $where .= " and state = {$_GPC['state']} ";
	        }
	        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_order')." WHERE $where ",array(':uniacid' => $_W['uniacid']));
	        $list = pdo_fetchall('SELECT * FROM' .tablename('xiwu_scan_order')." WHERE  $where ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.",{$psize}",array(':uniacid' => $_W['uniacid']));
	    }
	    //openid
	    if($_GPC['openid'] !=''){
	        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_order')."WHERE `openid` = '{$_GPC['openid']}' and `uniacid` = '{$_W['uniacid']}'");
	        $sql = 'SELECT * FROM ' . tablename('xiwu_scan_order') ." WHERE `openid` = '{$_GPC['openid']}' and `uniacid` = '{$_W['uniacid']}' ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.",{$psize}";
	        $list = pdo_fetchall($sql);
	    }
	    $pager = pagination($total, $pindex, $psize);
	    //删除
	    if($_GPC['sub'] == 'delete'){
	        $rel = pdo_delete('xiwu_scan_order', array('id' => $_GPC['id']));
	        if($rel){
	            message('删除成功！',$this->createWebUrl('order'),'success');
	        }else{
	            message('删除失败！',$this->createWebUrl('order'),'error');
	        }
	    }
	    if($_GPC['sub'] == 'orderdetails'){
	        $store = pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_store')."WHERE `uniacid` = '{$_W['uniacid']}'");
	        $item = pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_order'). "WHERE `id` = '{$_GPC['id']}'");
	        if($_GPC['click'] == 'queren'){
	            if($item['ordertype'] != 2 || $item['ordertype'] != 1){
	                pdo_update('xiwu_scan_order', array('status' => 2), array('id' => $_GPC['id']));
	            }else{
	                pdo_update('xiwu_scan_order', array('status' => 1), array('id' => $_GPC['id']));
	            }
	            //发送模板消息
	            $template_id = pdo_fetch("SELECT template_id FROM ".tablename('xiwu_scan_moban')."WHERE `uniacid` = '{$_W['uniacid']}' and `mobanid` = 'AT0328'");
	            $template = array(
	                'touser' => $item['openid'],
	                'template_id' => $template_id['template_id'],
	                'page' => 'bluemoon/pages/details/details?ordernumber='.$item['ordernumber'],
	                'form_id' =>$item['formid'],
	                'data'=>array(
	                    'keyword1'=>array('value'=> '商家已接单','color'=>"#666666"),
	                    'keyword2'=>array('value'=> date('Y-m-d H:i:s',time()),'color'=>'#666666'),
	                    'keyword3'=>array('value'=> '点击查看订单详情','color'=>'#fe0023'),
	                )
	            );
	            $mobanurl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$this->access_token();
	            $moban = ihttp_request($mobanurl, urldecode(json_encode($template)));
	            $text = json_decode($moban['content'],true);
	            pdo_delete('xiwu_scan_formid', array('id' => $formid['id']));
	            $print = $this->dayinorder($item);
	            return '接单成功 - '.$text['errcode'].$print;
	        }
	        if($_GPC['click'] == 'wancheng'){
	            pdo_update('xiwu_scan_order', array('status' => 2), array('id' => $_GPC['id']));
	            //发送模板消息
	            $formid = pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_formid')."WHERE `uniacid` = '{$_W['uniacid']}' and `openid` = '{$_GPC['openid']}' ORDER BY id DESC");
	            $template_id = pdo_fetch("SELECT template_id FROM ".tablename('xiwu_scan_moban')."WHERE `uniacid` = '{$_W['uniacid']}' and `mobanid` = 'AT0391'");
	            $template = array(
	                'touser' => $item['openid'],
	                'template_id' => $template_id['template_id'],
	                'page' => 'bluemoon/pages/details/details?ordernumber='.$item['ordernumber'],
	                'form_id' =>$formid['formid'],
	                'data'=>array(
	                    'keyword1'=>array('value'=> $store['title'],'color'=>"#666666"),
	                    'keyword2'=>array('value'=> $item['time'],'color'=>'#666666'),
	                    'keyword3'=>array('value'=> '点击反馈您的用餐体验, 将帮助我们更好提升服务','color'=>'#fe0023'),
	                )
	            );
	            $mobanurl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$this->access_token();
	            $moban = ihttp_request($mobanurl, urldecode(json_encode($template)));
	            $text = json_decode($moban['content'],true);
	            pdo_delete('xiwu_scan_formid', array('id' => $formid['id']));
	            return '确认成功 - '.$text['errcode'];
	        }
	        if($_GPC['click'] == 'xianjin'){
	            $rel = pdo_update('xiwu_scan_order', array('state' => 1,'payment' => '现金支付'), array('id' => $_GPC['id']));
	            $this -> dayinorder($item);
	            if($rel){
	            	$user = pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_user')."WHERE `uniacid` =:uniacid AND `openid` = :openid ", array(':uniacid' => $_W['uniacid'],':openid' => $item['openid'],));
	            	//增加积分
		        	$parameter = pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_parameter')."WHERE `uniacid` = '{$_W['uniacid']}'");
		        	if($parameter['chongzhi'] == '0'){
		        		$jifen = intval($user['jifen']) + intval($item['payprice']);
		        		pdo_update('xiwu_scan_user',array('jifen' => $jifen), array('openid' => $item['openid'], 'uniacid' => $_W['uniacid']));
		        	}
					//余额充值
					if($item['ordertype'] == 5){
						$balance = floatval($user['balance']) + floatval($item['payprice']);
						$arr = array(
							'uniacid'		=> $_W['uniacid'],
							'openid'		=> $item['openid'],
							'title'			=> '余额充值',
							'time'			=> time(),
							'state'			=> 0,//增加
							'money'			=> $item['payprice'],
						);
						pdo_insert('xiwu_scan_itemize',$arr);
						if($parameter['czmarketing'] == 0){
							$czmarketing = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_czmarketing')."WHERE `uniacid` = :uniacid ORDER BY czpay DESC", array(':uniacid' => $_W['uniacid']));
							if(count($czmarketing) != 0){
								$czmarketing = $czmarketing;
							}else{
								$czmarketing = false;
							}
							foreach ($czmarketing as $key => $value) {
								if(floatval($item['payprice']) >= floatval($value['czpay'])){
										$arr = array(
										'uniacid'		=> $_W['uniacid'],
										'openid'		=> $item['openid'],
										'title'			=> '店铺赠送',
										'time'			=> time(),
										'state'			=> 0,//增加
										'money'			=> floatval($value['zspay']),
									);
									pdo_insert('xiwu_scan_itemize',$arr);
									$balance = $balance + floatval($value['zspay']);
									break;
								}
							}
						}
						$rel = pdo_update('xiwu_scan_user',array('balance' => $balance), array('openid' => $item['openid'], 'uniacid' => $_W['uniacid']));
						if($rel){
							return '确认成功';
						}else{
							return '确认失败';
						}
					}
	            }else{
	                return '确认失败';
	            }
	        }
	        if($_GPC['click'] == 'baidutts'){
	        	$Path = "/addons/xiwulife_scan/xiwu/mp3/";
	        	$fileName = $_SERVER['DOCUMENT_ROOT'] . $Path;//文件路径
	        	$qucanma = $item['qucanma'];
	        	if(!file_exists($fileName.$qucanma.'.mp3')){
					$client = new AipSpeech(APP_ID, API_KEY, SECRET_KEY);
		            $result = $client->synthesis('请，'.$qucanma.'号，取餐', 'zh', 1, array(
		            	'spd' => 4,
		                'vol' => 8,
		            ));
		            if(!is_array($result)){
		                if (!file_exists($fileName)) {
		                    //进行文件创建
		                    mkdir($fileName, 0777, true);
		                }
		                $file = file_put_contents($fileName.$qucanma.'.mp3', $result);
		                if($file){
		                    $mp3url = $_W['siteroot'].$Path.$qucanma.'.mp3';
		                    return $mp3url;
		                }else{
		                    return '播报失败，请联系管理员';
		                }
		            }else{
		                return '播报失败，请联系管理员 - '.$result;;
		            }
	        	}else{
	        		$mp3url = $_W['siteroot'].$Path.$qucanma.'.mp3';
		            return $mp3url;
	        	}

	        }
	        if($_GPC['click'] == 'dayin'){
	            return $this->dayinorder($item);
			}
	        include $this->template('orderdetails');
	    }else{
	        include $this->template('order');
	    }
	}
	//评价管理
	public function doWebevaluate() {
		global $_W, $_GPC;
		$title = "评价管理";
		if($_GPC['sub'] == ''){
			$pindex = max(1, intval($_GPC['page']));
	        $psize = 15;
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_evaluate'));
			$pager = pagination($total, $pindex, $psize);
			$list = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_evaluate'). "WHERE `uniacid` = '{$_W['uniacid']}' ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.",{$psize}");
			foreach ($list as &$v) {
			if($v['evaluateimg'] != ''){
					$v['evaluateimg'] = explode(",", $v['evaluateimg']);
				}
				$v['time'] = date('Y-m-d H:i:s',$v['time']);
			}
		}
		if($_GPC['sub'] == 'yincang'){
			$rel = pdo_update('xiwu_scan_evaluate',array('state' => '1'), array('id' => $_GPC['id']));
			if($rel){
				message('操作成功！',$this->createWebUrl('evaluate'),'success');
			}else{
				message('操作失败！',$this->createWebUrl('evaluate'),'error');
			}
		}
		if($_GPC['sub'] == 'xianshi'){
			$rel = pdo_update('xiwu_scan_evaluate',array('state' => '0'), array('id' => $_GPC['id']));
			if($rel){
				message('操作成功！',$this->createWebUrl('evaluate'),'success');
			}else{
				message('操作失败！',$this->createWebUrl('evaluate'),'error');
			}
		}
		if($_GPC['sub'] == 'delete'){
			$rel = pdo_delete('xiwu_scan_evaluate', array('id' => $_GPC['id']));
			if($rel){
				message('删除成功！',$this->createWebUrl('evaluate'),'success');
			}else{
				message('删除失败！',$this->createWebUrl('evaluate'),'error');
			}
		}
		include $this->template('evaluate');
	}
	//商品管理
	public function doWebgoods() {
		global $_W, $_GPC;
		$title = "商品管理";
		load()->func('tpl');
		if($_GPC['sub'] == 'addgoods'){
			if($_GPC['id'] != ''){
				$item = pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_goods')." WHERE `id` = '{$_GPC['id']}' and `uniacid` = '{$_W['uniacid']}' ");
				$item['spec'] = json_decode(htmlspecialchars_decode($item['spec']),true);
			}
			//添加修改商品
			if(checksubmit('tijiao')){
				empty($_GPC['title']) && message('请填写名称');
				empty($_GPC['pic']) && message('请上传商品图');
				$data = array(
					'uniacid'	=> $_W['uniacid'],
					'title' 	=> $_GPC['title'],
					'pic' 		=> $_GPC['pic'],
					'price' 	=> $_GPC['price'],
					'status'	=> $_GPC['status'],
					'spec' 		=> $_GPC['spec'],
					'text' 		=> $_GPC['text'],
					'pics' 		=> serialize($_GPC['pics']),
					'index'		=> $_GPC['index'],
					'goodtype'	=> $_GPC['goodtype'],
					'specname'	=> $_GPC['specname'],
				);
				if($_GPC['id'] == ''){
					$rel = pdo_insert('xiwu_scan_goods',$data);
					if($rel){
						message('添加成功！',$this->createWebUrl('goods'),'success');
					}else{
						message('添加失败！',$this->createWebUrl('goods'),'error');
					}
				}else{
					$rel = pdo_update('xiwu_scan_goods',$data, array('id' => $_GPC['id']));
					if($rel){
						message('修改成功！',$this->createWebUrl('goods'),'success');
					}else{
						message('修改失败！',$this->createWebUrl('goods'),'error');
					}
				}
			}
			include $this->template('addgoods');
		}else{
			$pindex = max(1, intval($_GPC['page']));
	        $psize = 15;
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_goods'));
			$pager = pagination($total, $pindex, $psize);
			$list = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_goods'). "WHERE `uniacid` = '{$_W['uniacid']}' ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.",{$psize}");
			//搜索
			if($_GPC['sousuo'] !=''){
				$storeid = $_GPC['storeid'];
				$sql = 'SELECT * FROM ' . tablename('xiwu_scan_goods') ." WHERE `status` = '{$_GPC['status']}' AND `title` LIKE :title and `uniacid` = '{$_W['uniacid']}' ORDER BY id DESC";
				$params = array();
				$params[':title'] = "%{$_GPC['sousuo']}%";
				$list = pdo_fetchall($sql, $params);
			}
			//下架
			if($_GPC['sub'] == 'off'){
				$rel = pdo_update('xiwu_scan_goods',array('status' => '1'), array('id' => $_GPC['id']));
				if($rel){
					message('下架成功！',$this->createWebUrl('goods'),'success');
				}else{
					message('下架失败！',$this->createWebUrl('goods'),'error');
				}
			}
			//上架
			if($_GPC['sub'] == 'on'){
				$rel = pdo_update('xiwu_scan_goods',array('status' => '0'), array('id' => $_GPC['id']));
				if($rel){
					message('上架成功！',$this->createWebUrl('goods'),'success');
				}else{
					message('上架失败！',$this->createWebUrl('goods'),'error');
				}
			}
			//删除
			if($_GPC['sub'] == 'delete'){
				$rel = pdo_delete('xiwu_scan_goods', array('id' => $_GPC['id']));
				if($rel){
					message('删除成功！',$this->createWebUrl('goods'),'success');
				}else{
					message('删除失败！',$this->createWebUrl('goods'),'error');
				}
			}
			include $this->template('goods');
		}
	}
	//分类管理
	public function doWebclass() {
		global $_W, $_GPC;
		$title = "分类管理";
		load()->func('tpl');
		if($_GPC['sub'] == 'addclass'){
			if($_GPC['id'] != ''){
				$item = pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_class')." WHERE `id` = '{$_GPC['id']}' and `uniacid` = '{$_W['uniacid']}' ");
			}
			$goods = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_goods')."WHERE `status` = '0' and `uniacid` = '{$_W['uniacid']}'");
			//添加修改商品
			if(checksubmit('tijiao')){
				empty($_GPC['classname']) && message('请填写名称');
				empty($_GPC['goods']) && message('请选择商品');
				$data = array(
					'uniacid'	=> $_W['uniacid'],
					'classname' => $_GPC['classname'],
					'goods' =>  $_GPC['goods'],
				);
				if($_GPC['id'] == ''){
					$rel = pdo_insert('xiwu_scan_class',$data);
					if($rel){
						message('添加成功！',$this->createWebUrl('class'),'success');
					}else{
						message('添加失败！',$this->createWebUrl('class'),'error');
					}
				}else{
					$rel = pdo_update('xiwu_scan_class',$data, array('id' => $_GPC['id']));
					if($rel){
						message('修改成功！',$this->createWebUrl('class'),'success');
					}else{
						message('修改失败！',$this->createWebUrl('class'),'error');
					}
				}
			}
			include $this->template('addclass');
		}else{
			$pindex = max(1, intval($_GPC['page']));
	        $psize = 15;
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_class'). "WHERE `uniacid` = '{$_W['uniacid']}'");
			$list = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_class'). "WHERE `uniacid` = '{$_W['uniacid']}' ORDER BY paixu DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
			$pager = pagination($total, $pindex, $psize);
			//排序
			if($_GPC['sub'] == 'paixu'){
				$rel = pdo_update('xiwu_scan_class',array('paixu' => $_GPC['number']), array('id' => $_GPC['id']));
				if($rel){
					return '提交成功！';
				}else{
					return '提交失败！';
				}
			}
			//删除
			if($_GPC['sub'] == 'delete'){
				$rel = pdo_delete('xiwu_scan_class', array('id' => $_GPC['id']));
				if($rel){
					message('删除成功！',$this->createWebUrl('class'),'success');
				}else{
					message('删除失败！',$this->createWebUrl('class'),'error');
				}
			}
			include $this->template('class');
		}
	}
	//会员管理
	public function doWebxiwuuser() {
		global $_W, $_GPC;
		$title = '会员管理';
		if($_GPC['sub'] == 'usercard'){
			$usercard = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_receive')."WHERE `uniacid` = '{$_W['uniacid']}' and `openid` = '{$_GPC['openid']}'");
		}else{
			$pindex = max(1, intval($_GPC['page']));
	        $psize = 15;
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_user'). "WHERE `uniacid` = '{$_W['uniacid']}'");
			$userlist = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_user'). "WHERE `uniacid` = '{$_W['uniacid']}' LIMIT ".($pindex - 1) * $psize.",{$psize}");
			$pager = pagination($total, $pindex, $psize);
			foreach ($userlist as $k=>$value) {
				$userlist[$k]['formid'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_formid')."WHERE `openid` = '{$value['openid']}' and `uniacid` = '{$_W['uniacid']}'");
				$userlist[$k]['order'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_order')."WHERE `openid` = '{$value['openid']}' and `uniacid` = '{$_W['uniacid']}'");
				$userlist[$k]['card'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_receive')."WHERE `openid` = '{$value['openid']}' and `uniacid` = '{$_W['uniacid']}'");
			}
			if($_GPC['sub'] == 'receive'){
				$data = array(
					'uniacid'	=> $_W['uniacid'],
					'nickname' => $_GPC['nickname'],
					'openid' => $_GPC['openid'],
					'money' => $_GPC['money'],
					'maxmoney' => $_GPC['maxmoney'],
					'receive' => 2,//店铺赠讼
					'starttime'	=> time(),
					'endtime'	=> strtotime($_GPC['endtime']),
				);
				$rel = pdo_insert('xiwu_scan_receive',$data);
				if($rel){
					//发送模板消息
					$title = pdo_fetch("SELECT title FROM ".tablename('xiwu_scan_store')."WHERE `uniacid` = '{$_W['uniacid']}'");
					$formid = pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_formid')."WHERE `uniacid` = '{$_W['uniacid']}' and `openid` = '{$_GPC['openid']}' ORDER BY id DESC");
					$template_id = pdo_fetch("SELECT template_id FROM ".tablename('xiwu_scan_moban')."WHERE `uniacid` = '{$_W['uniacid']}' and `mobanid` = 'AT0551' ORDER BY id asc LIMIT 1");
					if($_GPC['text'] == ''){
						$text = $title['title'].'送您'.$_GPC['money'].'元优惠券，点击查看';
					}else{
						$text = $_GPC['text'];
					}
					$template = array(
						'touser' => $_GPC['openid'],
						'template_id' => $template_id['template_id'],
						'page' => 'bluemoon/pages/cardlist/cardlist',
						'form_id' =>$formid['formid'],
						'data'=>array(
							'keyword1'=>array('value'=> $title['title'],'color'=>"#666666"),
							'keyword2'=>array('value'=> '店铺优惠券','color'=>'#666666'),
							'keyword3'=>array('value'=> $_GPC['money'].'元（订单满'.$_GPC['maxmoney'].'元可用）','color'=>'#666666'),
							'keyword4'=>array('value'=> date('Y-m-d H:i:s',time()),'color'=>'#666666'),
							'keyword5'=>array('value'=> $text,'color'=>"#fe0023"),
						)
					);
					$mobanurl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$this->access_token();
					$moban = ihttp_request($mobanurl, urldecode(json_encode($template)));
					pdo_delete('xiwu_scan_formid', array('id' => $formid['id']));
					$text = json_decode($moban['content'],true);
					return '添加成功！- '.$text['errcode'];
				}else{
					return '添加失败！';
				}
			}
			include $this->template('user');
		}


	}
	//打印机管理
	public function doWebprint(){
		global $_W, $_GPC;
		load()->func('tpl');
		$title = '打印机管理';
		if($_GPC['sub'] == 'addprint'){
			if($_GPC['id'] != ''){
				$item = pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_print')." WHERE `id` = '{$_GPC['id']}' and `uniacid` = '{$_W['uniacid']}' ");
			}
			//添加修改打印机
			if(checksubmit('tijiao')){
				empty($_GPC['title']) && message('请填写名称');
				empty($_GPC['style']) && message('请选择打印机类型');
				$data = array(
					'uniacid'	=> $_W['uniacid'],
					'title' => $_GPC['title'],
					'number' =>  $_GPC['number'],
					'key' => $_GPC['key'],
					'default'=>$_GPC['default'],
					'method' => $_GPC['method'],
					'printnum' => $_GPC['printnum'],
					'style' => $_GPC['style'],
					'printtype' => $_GPC['printtype'],
				);
				if($_GPC['id'] == ''){
					$rel = pdo_insert('xiwu_scan_print',$data);
					//添加打印机
					$snlist = $_GPC['number']."#".$_GPC['key']."#".$_GPC['title'];
					$add = $this->addprinter($snlist);
					if($rel){
						message('添加成功！'.$add,$this->createWebUrl('print'),'success');
					}else{
						message('添加失败！'.$add,$this->createWebUrl('print'),'error');
					}
				}else{
					$rel = pdo_update('xiwu_scan_print',$data, array('id' => $_GPC['id']));
					if($rel){
						message('修改成功！',$this->createWebUrl('print'),'success');
					}else{
						message('修改失败！',$this->createWebUrl('print'),'error');
					}
				}
			}
		}else{
			$pindex = max(1, intval($_GPC['page']));
	        $psize = 15;
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xiwu_scan_print'));
			$pager = pagination($total, $pindex, $psize);
			$list = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_print'). "WHERE `uniacid` = '{$_W['uniacid']}' ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.",{$psize}");
			//关闭
			if($_GPC['sub'] == 'off'){
				$rel = pdo_update('xiwu_scan_print',array('default' => '1'), array('id' => $_GPC['id']));
				if($rel){
					message('关闭成功！',$this->createWebUrl('print'),'success');
				}else{
					message('关闭失败！',$this->createWebUrl('print'),'error');
				}
			}
			//打开
			if($_GPC['sub'] == 'on'){
				$rel = pdo_update('xiwu_scan_print',array('default' => '0'), array('id' => $_GPC['id']));
				if($rel){
					message('打开成功！',$this->createWebUrl('print'),'success');
				}else{
					message('打开失败！',$this->createWebUrl('print'),'error');
				}
			}
			//删除
			if($_GPC['sub'] == 'delete'){
				$rel = pdo_delete('xiwu_scan_print', array('id' => $_GPC['id']));
				if($rel){
					message('删除成功！',$this->createWebUrl('print'),'success');
				}else{
					message('删除失败！',$this->createWebUrl('print'),'error');
				}
			}
		}
		include $this->template('print');
	}
	//营销管理
	public function doWebmarketing() {
		global $_W, $_GPC;
		load()->func('tpl');
		$title = '营销管理';
		if(empty($_GPC['sub'])){
			$list =  pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_activity')."WHERE `uniacid` = '{$_W['uniacid']}'");
			$cardlist = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_card')."WHERE `uniacid` = '{$_W['uniacid']}'");
			$czlist = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_czmarketing')."WHERE `uniacid` = '{$_W['uniacid']}'");
		}
		if($_GPC['sub'] == 'carddiscount'){
			//添加优惠券
			if(checksubmit('tijiao')){
				empty($_GPC['price']) && message('请输入满减金额');
				empty($_GPC['maxmoney']) && message('请输入满减金额');
				$data = array(
					'uniacid'	=> $_W['uniacid'],
					'price' 	=> $_GPC['price'],
					'maxmoney' 	=> $_GPC['maxmoney'],
					'sharemen' 	=> $_GPC['sharemen'],
					'xianzhi'	=> $_GPC['xianzhi'],
					'jifen'		=> $_GPC['jifen'],
					'lingqument'=> $_GPC['lingqument'],
					'endtime'	=> $_GPC['endtime'],
				);
				$rel = pdo_insert('xiwu_scan_card',$data);
				if($rel){
					message('添加成功！',$this->createWebUrl('marketing'),'success');
				}else{
					message('添加失败！',$this->createWebUrl('marketing'),'error');
				}
			}
		}
		//删除店铺优惠
		if($_GPC['sub'] == 'delete'){
			$rel = pdo_delete('xiwu_scan_activity', array('id' => $_GPC['id']));
			if($rel){
				message('删除成功！',$this->createWebUrl('marketing'),'success');
			}else{
				message('删除失败！',$this->createWebUrl('marketing'),'error');
			}
		}
		//删除优惠券
		if($_GPC['sub'] == 'deletecard'){
			$rel = pdo_delete('xiwu_scan_card', array('id' => $_GPC['id']));
			if($rel){
				message('删除成功！',$this->createWebUrl('marketing'),'success');
			}else{
				message('删除失败！',$this->createWebUrl('marketing'),'error');
			}
		}
		//删除优惠券
		if($_GPC['sub'] == 'deletecz'){
			$rel = pdo_delete('xiwu_scan_czmarketing', array('id' => $_GPC['id']));
			if($rel){
				message('删除成功！',$this->createWebUrl('marketing'),'success');
			}else{
				message('删除失败！',$this->createWebUrl('marketing'),'error');
			}
		}
		if($_GPC['sub'] == 'storediscount'){
			$item =  pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_activity')."WHERE `id` ='{$_GPC['id']}' and `uniacid` = '{$_W['uniacid']}'");
			//添加优惠活动
			if(checksubmit('tijiao')){
				empty($_GPC['man']) && message('请输入满减金额');
				empty($_GPC['jian']) && message('请输入满减金额');
				$data = array(
					'uniacid'	=> $_W['uniacid'],
					'man' => $_GPC['man'],
					'jian' => $_GPC['jian'],
					'details' => $_GPC['details'],
				);
				if($_GPC['id'] == ''){
					$rel = pdo_insert('xiwu_scan_activity',$data);
					if($rel){
						message('添加成功！',$this->createWebUrl('marketing'),'success');
					}else{
						message('添加失败！',$this->createWebUrl('marketing'),'error');
					}
				}else{
					$rel = pdo_update('xiwu_scan_activity',$data,array('id' => $_GPC['id']));
					if($rel){
						message('修改成功！',$this->createWebUrl('marketing'),'success');
					}else{
						message('修改失败！',$this->createWebUrl('marketing'),'error');
					}
				}
			}
		}
		if($_GPC['sub'] == 'czmarketing'){
			$item =  pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_czmarketing')."WHERE `id` ='{$_GPC['id']}' and `uniacid` = '{$_W['uniacid']}'");
			//添加充值活动
			if(checksubmit('tijiao')){
				empty($_GPC['czpay']) && message('请输入金额');
				empty($_GPC['zspay']) && message('请输入金额');
				$data = array(
					'uniacid'	=> $_W['uniacid'],
					'czpay' => $_GPC['czpay'],
					'zspay' => $_GPC['zspay'],
				);
				if($_GPC['id'] == ''){
					$rel = pdo_insert('xiwu_scan_czmarketing',$data);
					if($rel){
						message('添加成功！',$this->createWebUrl('marketing'),'success');
					}else{
						message('添加失败！',$this->createWebUrl('marketing'),'error');
					}
				}else{
					$rel = pdo_update('xiwu_scan_czmarketing',$data,array('id' => $_GPC['id']));
					if($rel){
						message('修改成功！',$this->createWebUrl('marketing'),'success');
					}else{
						message('修改失败！',$this->createWebUrl('marketing'),'error');
					}
				}
			}
		}

		include $this->template('marketing');

	}
	public function doWebparameter() {
		global $_W, $_GPC;
		$title = '参数设置';
		load()->func('tpl');
		$item =  pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_parameter')."WHERE `uniacid` = '{$_W['uniacid']}'");
		if(checksubmit('tijiao')){
			$data = array(
				'uniacid' 		=> $_W['uniacid'],
				'themscolor' 	=> $_GPC['themscolor'],			//主题颜色
				'saoma' 		=> $_GPC['saoma'],				//扫码入口
				'yuyue' 		=> $_GPC['yuyue'],				//预约入口
				'waimai' 		=> $_GPC['waimai'],				//外卖入口
				'maidan' 		=> $_GPC['maidan'],
				'quhao' 		=> $_GPC['quhao'],
				'chongzhi' 		=> $_GPC['chongzhi'],
				'storemarketing'=> $_GPC['storemarketing'],
				'marketingcard' => $_GPC['marketingcard'],
				'wxpay' 		=> $_GPC['wxpay'],
				'xjpay' 		=> $_GPC['xjpay'],
				'yepay' 		=> $_GPC['yepay'],
				'ljpay' 		=> $_GPC['ljpay'],
				'wxpaycs' 		=> $_GPC['wxpaycs'],
				'czmarketing' 	=> $_GPC['czmarketing'],
				'scanprice' 	=> $_GPC['scanprice'],
				'waimaixianzhi' => $_GPC['waimaixianzhi'],
				'waimaipspay' 	=> $_GPC['waimaipspay'],
				'waimaijvli' 	=> $_GPC['waimaijvli'],
				'banquan1'		=> $_GPC['banquan1'],
				'banquan2'		=> $_GPC['banquan2'],			//版权信息
				'indexlist'		=> $_GPC['indexlist'],			//首页样式
				'receipt'		=> $_GPC['receipt'],			//自动接单
			);
			if($item['id'] == ''){
				$rel = pdo_insert('xiwu_scan_parameter',$data);
				if($rel){
					message('添加成功！',$this->createWebUrl('parameter'),'success');
				}else{
					message('添加失败！',$this->createWebUrl('parameter'),'error');
				}
			}else{
				$rel = pdo_update('xiwu_scan_parameter',$data,array('uniacid' => $_W['uniacid']));
				if($rel){
					message('修改成功！',$this->createWebUrl('parameter'),'success');
				}else{
					message('修改失败！',$this->createWebUrl('parameter'),'error');
				}
			}
		}
		include $this->template('parameter');

	}
	//获取access_token
	function access_token(){
		global $_GPC, $_W;
		$account_api = WeAccount::create();
		$access_token = $account_api->getAccessToken();
		return $access_token;
	}
	//模板消息
	public function doWebmoban() {
		global $_GPC, $_W;
		$title = '模板消息';
		//模板消息添加至帐号下的个人模板库
		if($_GPC['sub'] == 'addmoban'){
			pdo_delete('xiwu_scan_moban', array('uniacid' => $_W['uniacid']));
			$zhurl = "https://api.weixin.qq.com/cgi-bin/wxopen/template/add?access_token=".$this->access_token();
			$keyword_id_list = array(2,3,9);
			$zhdata = array(
				"id" => "AT0328",
				"keyword_id_list" => $keyword_id_list,
			);
			$result = ihttp_request($zhurl,(json_encode($zhdata)));
			if($result['code'] == 200){
				$template_id = json_decode($result['content'], true);
				$data = array(
					'uniacid'	=> $_W['uniacid'],
					'name'		=> '接单提醒通知',
					'mobanid'	=> 'AT0328',
					'keyword_id_list'	=> serialize($keyword_id_list),
					'template_id'	=> $template_id['template_id'],
				);
				$rel = pdo_insert('xiwu_scan_moban',$data);
				if($rel){
					$keyword_id_list = array(1,2,3);
					$zhdata = array(
						"id" => "AT0391",
						"keyword_id_list" => $keyword_id_list,
					);
					$result = ihttp_request($zhurl,(json_encode($zhdata)));
					if($result['code'] == 200){
						$template_id = json_decode($result['content'], true);
						$data = array(
							'uniacid'	=> $_W['uniacid'],
							'name'		=> '用餐评价通知',
							'mobanid'	=> 'AT0391',
							'keyword_id_list'	=> serialize($keyword_id_list),
							'template_id'	=> $template_id['template_id'],
						);
						$rel = pdo_insert('xiwu_scan_moban',$data);
						if($rel){
							$keyword_id_list = array(7,10,27,20,5);
							$zhdata = array(
								"id" => "AT0551",
								"keyword_id_list" => $keyword_id_list,
							);
							$result = ihttp_request($zhurl,(json_encode($zhdata)));
							if($result['code'] == 200){
								$template_id = json_decode($result['content'], true);
								$data = array(
									'uniacid'	=> $_W['uniacid'],
									'name'		=> '领取成功通知',
									'mobanid'	=> 'AT0551',
									'keyword_id_list'	=> serialize($keyword_id_list),
									'template_id'	=> $template_id['template_id'],
								);
								$rel = pdo_insert('xiwu_scan_moban',$data);
								if($rel){
									message('添加成功！',$this->createWebUrl('moban'),'success');
								}else{
									message('添加失败！-0004',$this->createWebUrl('moban'),'error');
								}
							}
						}else{
							message('添加失败！-0003',$this->createWebUrl('moban'),'error');
						}
					}
				}else{
					message('添加失败！-0002',$this->createWebUrl('moban'),'error');
				}
			}else{
				message('添加失败！-0001',$this->createWebUrl('moban'),'error');
			}
		}
		$mobanlist = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_moban')."WHERE `uniacid` = '{$_W['uniacid']}' ORDER BY id desc");
		include $this->template('moban');
	}
	public function doMobileindex(){
		global $_GPC, $_W;
		load()->func('tpl');
		//var_dump($_W);
		include $this->template('index');
	}
	//首次加载消息
	public function doWebfirstservice(){
		global $_GPC, $_W;
		$item = pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_parameter')."WHERE `uniacid` = '{$_W['uniacid']}'");
		$data = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_order')."WHERE `uniacid` = '{$_W['uniacid']}' and `status` = '0' and `state` != '0' ORDER BY id desc");
		foreach ($data as $k=>$value) {
			if($item['ljpay'] == 0){
				if($value['state'] != 1){
					unset($data[$k]);
				}
			}elseif($item['ljpay'] == 1){
				if($value['ordertype'] == 2 && $value['state'] != 1){
					unset($data[$k]);
				}
			}
		}
		$errno=0;
		$message="首次加载消息";
		return json_encode($data);
	}
	//接收消息
	public function doWebReceiveservice(){
		global $_GPC, $_W;
		$item = pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_parameter')."WHERE `uniacid` = '{$_W['uniacid']}'");
		$data = pdo_fetch("SELECT * FROM ".tablename('xiwu_scan_order')."WHERE `uniacid` = '{$_W['uniacid']}' ORDER BY id desc LIMIT 1");
		if($item['ljpay'] == 0){
			if($data['state'] == 0){
				$order = '';
			}elseif($data['state'] != 0 || $data['state'] != ''){
				$order = $data;
			}
		}elseif($item['ljpay'] == 1){
			if($data['ordertype'] == 2 && $data['state'] == 1){
				$order = $data;
			}elseif($data['ordertype'] != 2){
				$order = $data;
			}
		}
		return json_encode($order);
	}
	//打印订单
	function dayinorder($item){
		global $_GPC, $_W;
		$dayinlist = pdo_fetchall("SELECT * FROM ".tablename('xiwu_scan_print'). "WHERE `uniacid` = :uniacid and `default` = 0",array(':uniacid' => $_W['uniacid']));
		foreach ($dayinlist as $v) {
			if($v['default'] == 0){
				if($v['style'] == '58mm'){
					$orderInfo = $this->test($v['printtype'],$item,14,7,3,6,'58mm');
					return $this->wp_print($v['number'],$orderInfo,$v['printnum']);
				}elseif($v['style'] == '80mm'){
					$orderInfo = $this->test($v['printtype'],$item,26,7,4,6,'80mm');
					return $this->wp_print($v['number'],$orderInfo,$v['printnum']);
				}
			}
		}
	}
	//添加打印机
	function addprinter($snlist){
		$content = array(
			'user'=>USER,
			'stime'=>STIME,
			'sig'=>SIG,
			'apiname'=>'Open_printerAddlist',
		    'printerContent'=>$snlist
		);
		$client = new HttpClient(IP,PORT);
		if(!$client->post(PATH,$content)){
			return 'error';
		}
		else{
			return '添加成功'.$client->getContent();
		}
	}
	//打印订单
    function wp_print($printer_sn,$orderInfo,$times){
        $content = array(
            'user'=>USER,
            'stime'=>STIME,
            'sig'=>SIG,
            'apiname'=>'Open_printMsg',
            'sn'=>$printer_sn,
            'content'=>$orderInfo,
            'times'=>$times//打印次数
        );

	    $client = new HttpClient(IP,PORT);
	    if(!$client->post(PATH,$content)){
			return '打印失败';
		}
		else{
			return '打印成功'.$client->getContent();
		}
	}
//打印排版
	function test($printtype,$arr,$A,$B,$C,$D,$style){
    	global $_GPC, $_W;
    	if($arr['ordertype'] == 0 || $arr['ordertype'] == ''){
    		$ordertype = $arr['tablenumber'];
    	}elseif($arr['ordertype'] == 1){
    		$ordertype = "预约";
    	}elseif($arr['ordertype'] == 2){
			$ordertype = "外卖";
    	}elseif($arr['ordertype'] == 3){
    		$ordertype = "买单";
    	}elseif($arr['ordertype'] == 4){
    		$ordertype = "排队";
    	}elseif($arr['ordertype'] == 5){
    		$ordertype = "充值";
    	}
    	$title = pdo_fetch("SELECT title FROM ".tablename('xiwu_scan_store')."WHERE `uniacid` = '{$_W['uniacid']}'");
      	$orderInfo = "<CB>".$title['title']."#".$ordertype."</CB><BR>";
      	$arr['orderlist'] = unserialize($arr['orderlist']);
      	if($arr['orderlist'] != ""){
      		$orderInfo .= '取餐码：'.$arr['qucanma'].'<BR>';
			if($style == '58mm'){
	      		$orderInfo .= '名称           单价   数量 金额<BR>';
	      		$orderInfo .= "-------------------------------<BR>";
	      	}elseif($style == '80mm'){
	      		$orderInfo .= "名称                       单价   数量  金额<BR>";
	      		$orderInfo .= "---------------------------------------------<BR>";
	      	}
	      	if($arr['pindan'] == 0){
				foreach ($arr['orderlist'] as $k5 => $v5) {
					if($v5['goodtype'] == 0){
						$name = $v5['title'];
				        $price = $v5['price'];
				        $num = $v5['num'];
				        $prices = $v5['price']*$v5['num'];
				        $kw3 = '';
				        $kw1 = '';
				        $kw2 = '';
				        $kw4 = '';
				        $str = $name;
				        $blankNum = $A;//名称控制为26个字节
				        $lan = mb_strlen($str,'utf-8');
				        $m = 0;
				        $j=1;
				        $blankNum++;
				        $result = array();
				        for ($i=0;$i<$lan;$i++){
				          $new = mb_substr($str,$m,$j,'utf-8');
				          $j++;
				          if(mb_strwidth($new,'utf-8')<$blankNum) {
				            if($m+$j>$lan) {
				              $m = $m+$j;
				              $tail = $new;
				              $lenght = iconv("UTF-8", "GBK//IGNORE", $new);
				              $k = $A - strlen($lenght);
				              for($q=0;$q<$k;$q++){
				                $kw3 .= ' ';
				              }
				              $tail .= $kw3;
				              break;
				            }else{
				              $next_new = mb_substr($str,$m,$j,'utf-8');
				              if(mb_strwidth($next_new,'utf-8')<$blankNum) continue;
				              else{
				                $m = $i+1;
				                $result[] = $new.'<BR>';
				                $j=1;
				              }
				            }
				          }
				        }
				        $head = '';
				        foreach ($result as $value) {
				          $head .= $value;
				        }
				        if(strlen($price) < $B){
				              $k1 = $B - strlen($price);
				              for($q=0;$q<$k1;$q++){
				                    $kw1 .= ' ';
				              }
				              $price = $price.$kw1;
				        }
				        if(strlen($num) < $C){
				              $k2 = $C - strlen($num);
				              for($q=0;$q<$k2;$q++){
				                    $kw2 .= ' ';
				              }
				              $num = $num.$kw2;
				        }
				        if(strlen($prices) < $D){
				              $k3 = $D - strlen($prices);
				              for($q=0;$q<$k3;$q++){
				                    $kw4 .= ' ';
				              }
				              $prices = $prices.$kw4;
				        }
				        $orderInfo .= $head.$tail.' '.$price.' '.$num.' '.$prices.'<BR>';
				        @$nums += $prices;
					}elseif($v5['goodtype'] == 1){
						foreach ($v5['spec'] as $key => $value) {
							if( $value['num'] > 0){
								$name = $v5['title'].'('.$value['title'].')';
						        $price = $value['price'];
						        $num = $value['num'];
						        $prices = $value['price']*$value['num'];
						        $kw3 = '';
						        $kw1 = '';
						        $kw2 = '';
						        $kw4 = '';
						        $str = $name;
						        $blankNum = $A;//名称控制为26个字节
						        $lan = mb_strlen($str,'utf-8');
						        $m = 0;
						        $j=1;
						        $blankNum++;
						        $result = array();
						        for ($i=0;$i<$lan;$i++){
						          $new = mb_substr($str,$m,$j,'utf-8');
						          $j++;
						          if(mb_strwidth($new,'utf-8')<$blankNum) {
						            if($m+$j>$lan) {
						              $m = $m+$j;
						              $tail = $new;
						              $lenght = iconv("UTF-8", "GBK//IGNORE", $new);
						              $k = $A - strlen($lenght);
						              for($q=0;$q<$k;$q++){
						                $kw3 .= ' ';
						              }
						              $tail .= $kw3;
						              break;
						            }else{
						              $next_new = mb_substr($str,$m,$j,'utf-8');
						              if(mb_strwidth($next_new,'utf-8')<$blankNum) continue;
						              else{
						                $m = $i+1;
						                $result[] = $new.'<BR>';
						                $j=1;
						              }
						            }
						          }
						        }
						        $head = '';
						        foreach ($result as $value) {
						          $head .= $value;
						        }
						        if(strlen($price) < $B){
						              $k1 = $B - strlen($price);
						              for($q=0;$q<$k1;$q++){
						                    $kw1 .= ' ';
						              }
						              $price = $price.$kw1;
						        }
						        if(strlen($num) < $C){
						              $k2 = $C - strlen($num);
						              for($q=0;$q<$k2;$q++){
						                    $kw2 .= ' ';
						              }
						              $num = $num.$kw2;
						        }
						        if(strlen($prices) < $D){
						              $k3 = $D - strlen($prices);
						              for($q=0;$q<$k3;$q++){
						                    $kw4 .= ' ';
						              }
						              $prices = $prices.$kw4;
						        }
						        $orderInfo .= $head.$tail.' '.$price.' '.$num.' '.$prices.'<BR>';
						        @$nums += $prices;
							}
							
						}
					}
		      	}
	      	}else{
	      		foreach ($arr['orderlist'] as $index => $cart) {
	      			$orderInfo .= '#'.$cart['nickname'].'<BR>';
		      		foreach ($cart['cart'] as $k5 => $v5) {
				        if($v5['goodtype'] == 0){
							$name = $v5['title'];
					        $price = $v5['price'];
					        $num = $v5['num'];
					        $prices = $v5['price']*$v5['num'];
					        $kw3 = '';
					        $kw1 = '';
					        $kw2 = '';
					        $kw4 = '';
					        $str = $name;
					        $blankNum = $A;//名称控制为26个字节
					        $lan = mb_strlen($str,'utf-8');
					        $m = 0;
					        $j=1;
					        $blankNum++;
					        $result = array();
					        for ($i=0;$i<$lan;$i++){
					          $new = mb_substr($str,$m,$j,'utf-8');
					          $j++;
					          if(mb_strwidth($new,'utf-8')<$blankNum) {
					            if($m+$j>$lan) {
					              $m = $m+$j;
					              $tail = $new;
					              $lenght = iconv("UTF-8", "GBK//IGNORE", $new);
					              $k = $A - strlen($lenght);
					              for($q=0;$q<$k;$q++){
					                $kw3 .= ' ';
					              }
					              $tail .= $kw3;
					              break;
					            }else{
					              $next_new = mb_substr($str,$m,$j,'utf-8');
					              if(mb_strwidth($next_new,'utf-8')<$blankNum) continue;
					              else{
					                $m = $i+1;
					                $result[] = $new.'<BR>';
					                $j=1;
					              }
					            }
					          }
					        }
					        $head = '';
					        foreach ($result as $value) {
					          $head .= $value;
					        }
					        if(strlen($price) < $B){
					              $k1 = $B - strlen($price);
					              for($q=0;$q<$k1;$q++){
					                    $kw1 .= ' ';
					              }
					              $price = $price.$kw1;
					        }
					        if(strlen($num) < $C){
					              $k2 = $C - strlen($num);
					              for($q=0;$q<$k2;$q++){
					                    $kw2 .= ' ';
					              }
					              $num = $num.$kw2;
					        }
					        if(strlen($prices) < $D){
					              $k3 = $D - strlen($prices);
					              for($q=0;$q<$k3;$q++){
					                    $kw4 .= ' ';
					              }
					              $prices = $prices.$kw4;
					        }
					        $orderInfo .= $head.$tail.' '.$price.' '.$num.' '.$prices.'<BR>';
					        @$nums += $prices;
						}elseif($v5['goodtype'] == 1){
							foreach ($v5['spec'] as $key => $value) {
								if($value['num'] > 0){
									$name = $v5['title'].'('.$value['title'].')';
							        $price = $value['price'];
							        $num = $value['num'];
							        $prices = $value['price']*$value['num'];
							        $kw3 = '';
							        $kw1 = '';
							        $kw2 = '';
							        $kw4 = '';
							        $str = $name;
							        $blankNum = $A;//名称控制为26个字节
							        $lan = mb_strlen($str,'utf-8');
							        $m = 0;
							        $j=1;
							        $blankNum++;
							        $result = array();
							        for ($i=0;$i<$lan;$i++){
							          $new = mb_substr($str,$m,$j,'utf-8');
							          $j++;
							          if(mb_strwidth($new,'utf-8')<$blankNum) {
							            if($m+$j>$lan) {
							              $m = $m+$j;
							              $tail = $new;
							              $lenght = iconv("UTF-8", "GBK//IGNORE", $new);
							              $k = $A - strlen($lenght);
							              for($q=0;$q<$k;$q++){
							                $kw3 .= ' ';
							              }
							              $tail .= $kw3;
							              break;
							            }else{
							              $next_new = mb_substr($str,$m,$j,'utf-8');
							              if(mb_strwidth($next_new,'utf-8')<$blankNum) continue;
							              else{
							                $m = $i+1;
							                $result[] = $new.'<BR>';
							                $j=1;
							              }
							            }
							          }
							        }
							        $head = '';
							        foreach ($result as $value) {
							          $head .= $value;
							        }
							        if(strlen($price) < $B){
							              $k1 = $B - strlen($price);
							              for($q=0;$q<$k1;$q++){
							                    $kw1 .= ' ';
							              }
							              $price = $price.$kw1;
							        }
							        if(strlen($num) < $C){
							              $k2 = $C - strlen($num);
							              for($q=0;$q<$k2;$q++){
							                    $kw2 .= ' ';
							              }
							              $num = $num.$kw2;
							        }
							        if(strlen($prices) < $D){
							              $k3 = $D - strlen($prices);
							              for($q=0;$q<$k3;$q++){
							                    $kw4 .= ' ';
							              }
							              $prices = $prices.$kw4;
							        }
							        $orderInfo .= $head.$tail.' '.$price.' '.$num.' '.$prices.'<BR>';
							        @$nums += $prices;
								}
							}
						}
			      	}
			    }
	      	}
      	}
      	$time = date('Y-m-d H:i:s',$arr['time']);
      	if($style == '58mm'){
      		$orderInfo .= "-------------------------------<BR>";
      	}elseif($style == '80mm'){
      		$orderInfo .= "---------------------------------------------<BR>";
      	}
      	if($printtype == 0){
			if($arr['ordertype'] == 0 || $arr['ordertype'] == ''){

			}elseif($arr['ordertype'] == 1){
				$orderInfo .= '联系人：'.$arr['men'].'<BR>';
				$orderInfo .= '联系电话：'.$arr['tel'].'<BR>';
				$orderInfo .= "预约时间：".$arr['appointday']." ".$arr['appointtime']."<BR>";
			}elseif($arr['ordertype'] == 2){
				$orderInfo .= '联系人：'.$arr['men'].'<BR>';
				$orderInfo .= '联系电话：'.$arr['tel'].'<BR>';
				$orderInfo .= '配送地点：'.$arr['address'].'<BR>';
				$orderInfo .= '配送费：'.$arr['receivemoney'].'<BR>';
			}
			if(intval($arr['storediscount']) != 0 || $arr['storediscount'] != ''){
				$orderInfo .= '店铺优惠：-'.$arr['storediscount'].'元<BR>';
			}
			if(intval($arr['carddiscount']) != 0 || $arr['carddiscount'] != ''){
				$orderInfo .= '优惠券：-'.$arr['carddiscount'].'元<BR>';
			}
			if(intval($arr['state']) == 0){
				$state = '未付款';
			}elseif(intval($arr['state']) == 1){
				$state = '已付款';
			}
			$orderInfo .= '合计：'.$arr['payprice'].'元('.$state.')<BR>';
      	}
        $orderInfo .= '下单时间：'.$time.'<BR>';
        $orderInfo .= '备注：'.$arr['remarks'].'<BR>';
      	return $orderInfo;
    }
}