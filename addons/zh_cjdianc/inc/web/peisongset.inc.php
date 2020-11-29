<?php
global $_GPC, $_W;
$GLOBALS['frames'] = $this->getMainMenu2();
$storeid=$_COOKIE["storeid"];
$cur_store = $this->getStoreById($storeid);
$sys=pdo_get('cjdc_system',array('uniacid'=>$_W['uniacid']),array('ps_name','is_sj','is_dada','is_kfw','is_pt','id','is_sss'));
$ps_name=empty($sys['ps_name'])?'超级跑腿':$sys['ps_name'];
$info=pdo_get('cjdc_storeset',array('store_id'=>$storeid));
$dadainfo=pdo_get('cjdc_psset',array('store_id'=>$storeid,'uniacid'=>$_W['uniacid']));
$kfwinfo=pdo_get('cjdc_kfwset',array('store_id'=>$storeid,'uniacid'=>$_W['uniacid']));
$sssinfo=pdo_get('cjdc_sssset',array('store_id'=>$storeid,'uniacid'=>$_W['uniacid']));
if(checksubmit('submit')){
	$data['ps_time']=$_GPC['ps_time'];
	$data['ps_jl']=$_GPC['ps_jl'];
	$data['ps_mode']=$_GPC['ps_mode'];
	$data['is_zt']=$_GPC['is_zt'];
	$data['is_cj']=$_GPC['is_cj'];
	$data['is_ps']=$_GPC['is_ps'];
	$data['is_hdfk']=$_GPC['is_hdfk'];
	$data['ztxy']=html_entity_decode($_GPC['ztxy']);
	if($_GPC['is_ps']==2 and $_GPC['is_zt']==2){
		message('外卖配送和到店自提必须开启一个','','error');
	}
	$res = pdo_update('cjdc_storeset', $data, array('store_id' => $storeid));
	if($_GPC['ps_mode']=='达达配送'){
		$data2['source_id']=$_GPC['source_id'];
		$data2['shop_no']=$_GPC['shop_no'];
		$data2['store_id']=$storeid;
		$data2['uniacid']=$_W['uniacid'];
		$dada=pdo_get('cjdc_psset',array('store_id'=>$storeid,'uniacid'=>$_W['uniacid']));
		if($dada){
			$res2 = pdo_update('cjdc_psset', $data2, array('store_id' => $storeid));
		}else{
			$res2 =pdo_insert('cjdc_psset', $data2);
		}
	}
	if($_GPC['ps_mode']=='快服务配送'){
		$data2['store_id']=$storeid;
		$data2['user_id']=$_GPC['user_id'];	
		$data2['uniacid']=$_W['uniacid'];
		$dada=pdo_get('cjdc_kfwset',array('store_id'=>$storeid,'uniacid'=>$_W['uniacid']));
		if($dada){
			$res2 = pdo_update('cjdc_kfwset', $data2, array('store_id' => $storeid));
		}else{
			$res2 =pdo_insert('cjdc_kfwset', $data2);
		}
	}
		if($_GPC['ps_mode']=='闪时送配送'){
				include IA_ROOT . '/addons/zh_cjdianc/peisong/SssOpenapi.php';
			$system=pdo_get('cjdc_system',array('uniacid'=>$_W['uniacid']),array('sss_appkey','sss_secret'));
			//绑定店铺
		$data = array(
			'sss_shop_id' =>$_GPC['sss_shop_id'],	
			'sss_shop_token' =>$_GPC['sss_shop_token'],	
			  "appkey" => $system['sss_appkey'],
            "timestamp" => time(),
            "version" => '1.0',

		);

		$obj = new SssOpenapi();
		$sign = $obj->getSign($data, $system['sss_secret']);
		$data['sign'] = $sign;
		$url = "https://peisongopen.51weto.com/api/v1/shop/bind";
		$result = $obj->requestWithPost($url, $data);
		$res3=json_decode($result,true);
		//var_dump($res3);die;
		if($res3['code']==0){
		$data2['store_id']=$storeid;
		$data2['sss_shop_id']=$_GPC['sss_shop_id'];	
		$data2['sss_shop_token']=$_GPC['sss_shop_token'];	
		$data2['uniacid']=$_W['uniacid'];
		$dada=pdo_get('cjdc_sssset',array('store_id'=>$storeid,'uniacid'=>$_W['uniacid']));
		if($dada){
			$res2 = pdo_update('cjdc_sssset', $data2, array('store_id' => $storeid));
		}else{
			$res2 =pdo_insert('cjdc_sssset', $data2);
		
		}
	}else{
		message($res3['message'],'','error');
	}

	}
	if($res or $res2){
		message('编辑成功',$this->createWebUrl('peisongset',array()),'success');
	}else{
		message('编辑失败','','error');
	}
}
include $this->template('web/peisongset');