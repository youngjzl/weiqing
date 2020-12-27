<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'merch/core/inc/page_merch.php';
require EWEI_SHOPV2_PATH.'payment/wechatpay/wechat.php';

class Index_EweiShopV2Page extends MerchWebPage
{

    private $parms=array();

    function main($goodsfrom = '')
    {

        global $_W, $_GPC;

        if(empty($_W['shopversion'])){
            $goodsfrom = strtolower(trim($_GPC['goodsfrom']));
            if(empty($goodsfrom)){
                exit();
                header('location: ' . webUrl('goods', array('goodsfrom'=>'sale')));
            }
        }


        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sqlcondition = $groupcondition = '';
        $condition = ' WHERE g.`uniacid` = :uniacid and g.`supplychainid`=:supplychainid';
        $supplychainid=de_supplychain_code($_W['merch_user']['code']);
        $params = array(':uniacid' => $_W['uniacid'], ':supplychainid' => $supplychainid);

        $not_add = 0;
        $merch_user = $_W['merch_user'];
//        $maxgoods = intval($merch_user['maxgoods']);
//        if ($maxgoods > 0) {
            $sql = 'SELECT COUNT(1) FROM ' . tablename('ewei_shop_goods') . ' where uniacid=:uniacid and supplychainid=:supplychainid';
            $goodstotal = pdo_fetchcolumn($sql, $params);

//            if ($goodstotal >= $maxgoods) {
//                $not_add = 1;
//            }
//        }

        if (!empty($_GPC['keyword'])&&!empty($_GPC['k_type'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $_GPC['k_type']=trim($_GPC['k_type']);

            switch ($_GPC['k_type']){
                case '1':
                    $sqlcondition = ' left join ' . tablename('ewei_shop_goods_option') . ' op on g.id = op.goodsid';
                    $groupcondition = ' group by g.`id`';

                    $condition .= ' AND (g.`title` LIKE :keyword or g.`productsn` LIKE :keyword or op.`title` LIKE :keyword or op.`goodssn` LIKE :keyword or op.`productsn` LIKE :keyword)';
                    break;
                case '2':
                    $condition .=' AND (g.`id`=:id LIKE :keyword or g.`goodssn` LIKE :keyword or g.`goodssn` LIKE :keyword)';
                    break;
                case '3':
                    $condition .='AND  productsn=:productsn';
                    $params[':productsn'] = '%' . $_GPC['keyword'] . '%';
                    break;
            }
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
            $params[':id'] = $_GPC['keyword'];
        }
        if (!empty($_GPC['cats'])){
            $_GPC['cats'] = trim($_GPC['cats']);
            $cats=explode(',',$_GPC['cats']);
            if (is_array($cats)){
                foreach ($cats as $list){
                    $condition .= " AND FIND_IN_SET({$list},cates)<>0 ";
                }
            }
        }
        if (!empty($_GPC['goodsbusinesstype'])){
            $goodsbusinesstype=trim($_GPC['goodsbusinesstype']);
            $condition .= " AND goodsbusinesstype=:goodsbusinesstype ";
            $params[':goodsbusinesstype'] = $goodsbusinesstype;
        }
        if (!empty($_GPC['brand'])) {
            $brand = $_GPC['brand'];
            $condition .= " AND brand LIKE :brand ";
            $params[':brand'] = '%' .$brand.'%';
        }

        $condition .= ' AND g.`status` = 1  and g.`total`>0 and g.`deleted`=0  AND g.`checked`=0';
        $status = 1;


        $sql = 'SELECT COUNT(g.`id`) FROM ' . tablename('ewei_shop_goods') . 'g' . $sqlcondition . $condition . $groupcondition;
        $total = pdo_fetchcolumn($sql, $params);
        $list = array();
        if (!empty($total)) {
            $sql = 'SELECT g.* FROM ' . tablename('ewei_shop_goods') . 'g' . $sqlcondition . $condition . $groupcondition . ' ORDER BY g.`status` DESC, g.`supplychaindisplayorder` DESC,
                g.`id` DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql, $params);
            foreach ($list as $key => &$value) {
                $url = mobileUrl('goods/detail', array('id' => $value['id']), true);
                $value['qrcode'] = m('qrcode')->createQrcode($url);
                switch ($value['goodsbusinesstype']){
                    case 1:
                        $value['goodsbusinesstype']='保税区发货';
                        break;
                    case 2:
                        $value['goodsbusinesstype']='香港直邮';
                        break;
                    case 4:
                        $value['goodsbusinesstype']='海外直邮';
                        break;
                    case 5:
                        $value['goodsbusinesstype']='国内发货';
                        break;
                }
            }
            $pager = pagination2($total, $pindex, $psize);
        }

        $categorys = m('shop')->getFullCategory(true);
        $category = array();
        foreach ($categorys as $cate) {
            $category[$cate['id']] = $cate;
        }


        include $this->template('goods');
    }

    function sale() {
        $this->main('sale');
    }

    public function verify(){
        global $_W;
        global $_GPC;

        if ($_W['isajax']){
            $goods_json=json_decode(html_entity_decode($_GPC['goods']),true);
            if (empty($goods_json)){
                show_json(0,'支付失败，请检查商品数据');
            }

            //检查供应商微信支付是否填写完整
            $supplychainId=de_supplychain_code($_W['merch_user']['code']);
            $getPayInfo=pdo_fetch('SELECT wechatpay FROM '.tablename('ewei_shop_supplychain_sysset').'WHERE supplychainid=:supplychainid',array(':supplychainid'=>$supplychainId));
            if (empty($getPayInfo)){
                show_json(0,'支付失败，供应商未开通支付');
            }

            //获取商品数据
            $goods=pdo_fetchall('SELECT * FROM '.tablename('ewei_shop_goods').'WHERE id in('.implode(',',array_column($goods_json,'id')).')');
            $parms=array();
            $parms['time_expire']=date(DATE_RFC3339,time());//购物结束时间
            $params['des']='购物车商品信息';//商品信息
            $params['order_no']=m('common')->createNO('order', 'ordersn', 'SC');//订单号
            $money_total=0;//总商品金额
            foreach ($goods as $list){
                $num=intval($goods_json[array_search($list['id'], array_column($goods_json,'id'))]['num']);//商品数量
                $money_total+=$list['marketprice'];//总价格

                $goods_detail[]=array(
                    'goods_name'=>$list['title'],
                    'wechatpay_goods_id'=>$list['id'],
                    'merchant_goods_id'=>$list['id'],
                    'unit_price'=>sprintf("%01.2f", $list['marketprice']*10)*$num
                );
                //前端展示的数据
                $goodsarr[]=array(
                    'id'=>$list['id'],
                    'title'=>$list['title'],
                    'num'=> $num,
                    'price'=> sprintf("%01.2f", $num*$list['marketprice']),
                    'img'=>tomedia($list['thumb']),
                );
            }
            $params['detail'] = array(
                'invoice_id'=>'小票-'.$params['order_no'],
                'goods_detail'=>$goods_detail,
                'original_price'=>$money_total,
            );
            $params['money_total']=sprintf("%01.2f",$money_total);
            $params['scene_info']=array(
                'store_info'=>array(
                    'address'=>'广东省深圳市南山区科技中一道10000号',
                    'area_code'=>'440305',
                    'name'=>'腾讯大厦分店',
                    'id'=>'0001',
                ),
                'device_id'=>'',
                'payer_client_ip'=>$this->get_client_ip(),
            );
            $params['goodsarr']=$goodsarr;
            show_json(1,$params);
        }
    }
    public function creatorder(){
        global $_W;
        $parms=$this->parms;
        unset($parms['goodsarr']);
        if (empty($parms)){
            show_json(0,'商品错误，请联系管理员');
        }
        //检查供应商微信支付是否填写完整
        $supplychainId=de_supplychain_code($_W['merch_user']['code']);
        $getPayInfo=pdo_fetch('SELECT wechatpay FROM '.tablename('ewei_shop_supplychain_sysset').'WHERE supplychainid=:supplychainid',array(':supplychainid'=>$supplychainId));
        if (empty($getPayInfo)){
            show_json(0,'支付失败，供应商未开通支付');
        }

        //下单支付
        $getPayInfo=unserialize($getPayInfo['wechatpay']);
        $wechatPay = new wechat(
            $getPayInfo['app_wechat']['appid'],
            $getPayInfo['app_wechat']['merchid'],
            null,
            $getPayInfo['app_wechat']['apikey'],
            $getPayInfo['app_wechat']['key']
        );
        var_dump($wechatPay->unifiedOrder($parms));
        die;
        if ($this->parms){

        }
    }
    //获取当前服务器ip
    private function get_client_ip(){
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv('REMOTE_ADDR')) {
            $cip = getenv('REMOTE_ADDR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $cip = getenv('HTTP_CLIENT_IP');
        } else {
            $cip = 'unknown';
        }
        return $cip;
    }

    public function download(){
        global $_W;
        global $_GPC;
        $id = intval(trim($_GPC['id']));
        $code=de_supplychain_code($_W['merch_user']['code']);
        $parms=array(':id'=>$id,':uniacid'=>$_W['uniacid'],':supplychainid'=>$code);

        if (empty($id)) {
            exit();
        }

        $goods = pdo_fetch('SELECT id,thumb_url FROM ' . tablename('ewei_shop_goods') . ' WHERE id=:id AND uniacid=:uniacid AND supplychainid=:supplychainid',$parms);
        if (empty($goods)) {
            exit();
        }
        $img_url=unserialize($goods['thumb_url']);
        $filename = $_SERVER['DOCUMENT_ROOT'] . '/app/download/'.time().'.zip';
        $zip = new ZipArchive();
        $zip->open($filename, ZIPARCHIVE::CREATE);
        foreach ($img_url as $value) {
            //抓取图片内容
            if (strpos($value,'http') !== false){
                $fileContent = file_get_contents($value);
            }else{
                $fileContent = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/attachment/'.$value);
            }

            $jpg=pathinfo($value);
            $zip->addFromString($jpg['basename'], $fileContent);
        }
        $zip->close();
        ob_end_clean();
        header('Content-Type: application/force-download');
        header('Content-Transfer-Encoding: binary');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . time() . '.zip');
        header('Content-Length: ' . filesize($filename));
        error_reporting(0);
        readfile($filename);
        flush();
        ob_flush();
        @unlink($filename);
        exit();
    }

    public function goods_cat(){
        global $_W;
        $parms=array(':supplychainid'=>$_W['supplychainid'],':uniacid'=>$_W['uniacid']);
        $leve1=pdo_fetchall('select * from'.tablename('ewei_shop_supplychain_category').'where parentid=0 and level=1 and supplychainid=:supplychainid and uniacid=:uniacid',$parms);
        $data=array();
        foreach ($leve1 as $l1){
            $leve_list=array(
                'id'=>$l1['id'],
                'name'=> $l1['name'],
                'pid'=> -1,
                'cities'=>array()
            );
            $leve2=pdo_fetchall('select * from'.tablename('ewei_shop_category').'where level=2 and parentid='.$l1['id']);
            foreach ($leve2 as $k=>$l2){
                $leve_list['cities'][]=array(
                    'id'=> $l2['id'],
                    'name'=> $l2['name'],
                    'pid'=> $l1['id'],
                    'district'=>array(),
                );
                $leve3=pdo_fetchall('select * from'.tablename('ewei_shop_category').'where level=3 and  parentid='.$l2['id']);
                foreach ($leve3 as $l3){
                    $leve_list['cities'][$k]['district'][]=array(
                        'id'=> $l3['id'],
                        'name'=> $l3['name'],
                        'pid'=> $l2['id'],
                    );
                }
            }
            $data['data'][]=$leve_list;
        }
        show_json(1, array('data' => $data));
    }
}
