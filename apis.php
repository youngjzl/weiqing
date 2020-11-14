<?php
require './framework/bootstrap.inc.php';
require './addons/ewei_shopv2/defines.php';
require './addons/ewei_shopv2/core/inc/functions.php';

$apikey = $_POST['apikey'];
$item = $_POST['item'];

$api_user = pdo_fetch("SELECT * FROM " . tablename("ewei_shop_api_user") . " WHERE apikey=:apikey ", array( ":apikey" => $apikey));

if(empty($api_user)){
	$code = "10002";
	$reason = "请输入授权API";
}
if(!empty($api_user)&&empty($item)){
	$code = "10002";
	$reason = "请输入查询范围";
}
//商品列表
if($item=="goodslist"){

	$keyword  = $_POST['keyword'];
	$brand=$_POST['brand'];
	$goodsbusinesstype=$_POST['goodsbusinesstype'];
    $status=$_POST['status'];
	$pageSize = $_POST['pageSize'];


	$pageNo = $_POST['pageNo'];
    $condition='';
	
	$sqlcondition = " left join " . tablename("ewei_shop_goods_option") . " op on g.id = op.goodsid";
    $merch_plugin = p('merch');
	if($merch_plugin ) 
	{
		$sqlcondition .= " left join " . tablename("ewei_shop_merch_user") . " merch on merch.id = g.merchid and merch.uniacid=g.uniacid";
	}
	$groupcondition = " group by g.`id`";
	if (!empty($keyword)){
        $condition .= " AND g.status=1 AND g.deleted=1 AND (g.`title` LIKE :keyword or g.`keywords` LIKE :keyword or g.`goodssn` LIKE :keyword or g.`productsn` LIKE :keyword or op.`title` LIKE :keyword or op.`goodssn` LIKE :keyword or op.`productsn` LIKE :keyword";
        if( $merch_plugin )
        {
            $condition .= " or merch.`merchname` LIKE :keyword";
        }
        $condition .= " )";
	}

	$params[":keyword"] = "%" . $keyword . "%";

	$sql = "SELECT g.id FROM " . tablename("ewei_shop_goods") . "g" . $sqlcondition . $condition . $groupcondition;
	$total_all = pdo_fetchall($sql, $params);

	$total = count($total_all);
	unset($total_all);
	if( !empty($total) ) 
	{
		$sql = "SELECT g.id as goods_id,g.title,g.thumb,g.thumb_url,g.goodsbusinesstype,g.content,g.unit,g.total,g.marketprice,g.marketprice FROM " . tablename("ewei_shop_goods") . 'g' . $sqlcondition . $condition . $groupcondition . " ORDER BY g.`status` DESC, g.`displayorder` DESC,g.`id` DESC LIMIT " . ($pageNo - 1) * $pageSize . "," . $pageSize;
		$list = pdo_fetchall($sql, $params);
		foreach( $list as $key => &$value )
		{
		    $speclist=pdo_fetchall('SELECT id as spec_id,goodsid as goods_id,title,marketprice,stock FROM '.tablename('ewei_shop_goods_option').' WHERE goodsid=:goodsid and uniacid=:uniacid',array(':goodsid'=>$value['goods_id'],':uniacid'=>2));
            $list[$key]['spec']=$speclist;
            $imgsrc=strip_tags($value["content"],'<img>');
            preg_match_all('/(http|https):\/\/([\w\d\-_]+[\.\w\d\-_]+)[:\d+]?([\/]?[\w\/\.\?=&;%@#\+,]+)/i',$imgsrc,$imgarr);
            $list[$key]["content"] =  $imgarr[0];
		}
	}
	$code = "10000";
	$result["data"] = $list;
	$result["total"]= $total;
}
//单个商品详情
if($item=="goodsdetail"){
	$goods_id = $_POST['goods_id'];
	if(empty($goods_id)){
		$code = "10002";
		$reason="请输入查询的商品ID";
	}
	$goods = pdo_fetch("select title,subtitle,thumb,thumb_url,unit,sales,description,content,goodssn,productsn,productprice,marketprice,thumb_url from " . tablename("ewei_shop_goods") . " where id=:id limit 1", array(":id" => $goods_id ));
	if(empty($goods) ) {
		$code = "10002";
		$reason="没有可以显示的信息！";
	}else{
		$code = "10000";
		$result["data"] = $goods;
		$result["total"]=1;
	}
}
//商品库存
if ($item='stock'){
    $goods_id = $_POST['goods_id'];
    if(empty($goods_id)||!is_array($goods_id)){
        $code = "10002";
        $reason="请输入查询的商品ID";
    }
    if (count($goods_id)>2000){
        $code = "10002";
        $reason="查询的商品ID超过限制";
    }
    $stock=array();
    foreach ($goods_id as $list){
        $stock[] = pdo_fetchall('select id as spec_id,goods_id as goodsid,stock,title from'.tablename('ewei_shop_goods_option').'where goodsid=:goodsid',array(':goodsid'=>$list));
    }
    $total=count($stock);
    if(empty($total) ) {
        $code = "10002";
        $reason="没有可以显示的信息！";
    }else{
        $code = "10000";
        $result["data"] = $stock;
        $result["total"]= $total;
    }
}

if(empty($code)){
	$code = "10002";
	$reason="输入的查询范围不存在";
}
//输出数据
$output = array(
	'code' => $code, //消息提示，客户端常会用此作为给弹窗信息。
	'reason' => $reason,
	'result' => $result //成功与失败的代码，一般都是正数或者负数
);
exit(json_encode($output));