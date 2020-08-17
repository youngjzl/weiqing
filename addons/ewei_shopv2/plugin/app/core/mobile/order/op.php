<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
require_once EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';

class Op_EweiShopV2Page extends AppMobilePage
{
    /**
     * 取消订单
     * @global type $_W
     * @global type $_GPC
     */
    function cancel() {

        global $_W, $_GPC;
        $orderid = intval($_GPC['id']);
        if(empty($orderid)){
            return app_error(AppError::$ParamsError);
        }
        $order = pdo_fetch("select id,ordersn,openid,status,deductcredit,deductcredit2,deductprice,couponid,`virtual`,`virtual_info`,merchid  from " . tablename('ewei_shop_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1'
            , array(':id' => $orderid, ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
        if (empty($order)) {
            return app_error(AppError::$OrderNotFound);
        }
        if($order['status'] > 0){
            return app_error(AppError::$OrderCannotCancel);
        }
        if($order['status'] < 0){
            return app_error(AppError::$OrderCannotCancel);
        }

        if (!empty($order['virtual']) && $order['virtual'] != 0) {

            $goodsid = pdo_fetch('SELECT goodsid FROM '.tablename('ewei_shop_order_goods').' WHERE uniacid = '.$_W['uniacid'].' AND orderid = '.$order['id']);

            $typeid = $order['virtual'];
            $vkdata = ltrim($order['virtual_info'],'[');
            $vkdata = rtrim($vkdata,']');
            $arr = explode('}',$vkdata);
            foreach($arr as $k => $v){
                if(!$v){
                    unset($arr[$k]);
                }
            }
            $vkeynum = count($arr);

            //未付款卡密变为未使用
            pdo_query("update " . tablename('ewei_shop_virtual_data') . ' set openid="",usetime=0,orderid=0,ordersn="",price=0,merchid='.$order['merchid'].' where typeid=' . intval($typeid).' and orderid = '.$order["id"]);

            //模板减少使用数据
            pdo_query("update " . tablename('ewei_shop_virtual_type') . " set usedata=usedata-".$vkeynum." where id=" .intval($typeid));

        }

        //处理订单库存及用户积分情况(赠送积分)
        m('order')->setStocksAndCredits($orderid, 2);
        //返还抵扣积分
        if ($order['deductprice'] > 0) {
            m('member')->setCredit($order['openid'], 'credit1', $order['deductcredit'], array('0', $_W['shopset']['shop']['name'] . "购物返还抵扣积分 积分: {$order['deductcredit']} 抵扣金额: {$order['deductprice']} 订单号: {$order['ordersn']}"));
        }
        //返还抵扣余额
        m('order')->setDeductCredit2($order);
        //退还优惠券
        if (com('coupon') && !empty($order['couponid'])) {
            com('coupon')->returnConsumeCoupon($orderid); //手机关闭订单
        }
        pdo_update('ewei_shop_order', array('status' => -1, 'canceltime' => time(), 'closereason' => trim($_GPC['remark'])), array('id' => $order['id'], 'uniacid' => $_W['uniacid']));
        //模板消息
        m('notice')->sendOrderMessage($orderid);

        //加入好物圈收藏
        $goodscircle = p('goodscircle');
        if($goodscircle){
            $goodscircle->updateOrder($order['openid'],$orderid);
        }

        return app_json();
    }

    /**
     * 确认收货
     * @global type $_W
     * @global type $_GPC
     */
    function finish() {

        global $_W, $_GPC;
        $orderid = intval($_GPC['id']);
        if(empty($orderid)){
            return app_error(AppError::$ParamsError);
        }

        //单品退换货，确认收货后取消维权
        pdo_update('ewei_shop_order_goods', array('single_refundstate' => 0), array('orderid' => $orderid, 'uniacid' => $_W['uniacid']));

        $order = pdo_fetch("select id,ordersn,status,openid,couponid,refundstate,refundid, wxapp_allow_subscribe from " . tablename('ewei_shop_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1'
            , array(':id' => $orderid, ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
        if (empty($order)) {
            return app_error(AppError::$OrderNotFound);
        }

        if ($order['status'] != 2) {
            return app_error(AppError::$OrderCannotFinish);
        }
        if ($order['refundstate'] > 0 && !empty($order['refundid'])) {

            $change_refund = array();
            $change_refund['status'] = -2;
            $change_refund['refundtime'] = time();
            pdo_update('ewei_shop_order_refund', $change_refund, array('id' => $order['refundid'], 'uniacid' => $_W['uniacid']));
        }

        pdo_update('ewei_shop_order', array('status' => 3, 'finishtime' => time(), 'refundstate' => 0), array('id' => $order['id'], 'uniacid' => $_W['uniacid']));

        // 处理积分
        m('order')->setStocksAndCredits($orderid, 3);

        //商品全返
        m('order')->fullback($orderid);

        $memberSetting = m('common')->getSysset('member');
        // 会员升级 - 如果没有设置升级条件或者升级条件是1的时候,那么走收货后进行升级的流程
        if ((int)$memberSetting['upgrade_condition'] === 1 || empty($memberSetting['upgrade_condition'])) {
            m('member')->upgradeLevel($order['openid'], $orderid);
        }
        //会员升级

        //余额赠送
        m('order')->setGiveBalance($orderid, 1);

        //发送赠送优惠券
        if (com('coupon')) {
            com('coupon')->sendcouponsbytask($orderid); //确认收货发送优惠券
        }

        //优惠券返利
        if (com('coupon') && !empty($order['couponid'])) {
            com('coupon')->backConsumeCoupon($orderid); //手机收货
        }

        //模板消息
        m('notice')->sendOrderMessage($orderid);

        // 小程序确认收货订阅消息
        if (!empty($order['wxapp_allow_subscribe'])) {
            $template = explode(',', $order['wxapp_allow_subscribe']);
            if (in_array('receive', $template)) {
                $msgdata = array();
                $msgdata['ordersn'] = $order['ordersn'];
                $goods = pdo_fetchall("select og.goodsid,og.price,g.title,g.thumb,og.total,g.credit,og.optionid,og.optionname as optiontitle,g.isverify,g.storeids, og.realprice from " . tablename('ewei_shop_order_goods') . " og "
                    . " left join " . tablename('ewei_shop_goods') . " g on g.id=og.goodsid "
                    . " where og.orderid=:orderid ", array(':orderid' => $orderid));
                $title = '';
                foreach ($goods as $og) {
                    if (!empty($title)) {
                        $title .= "\n";
                    }
                    $title .=  $og['title'];
                    if (!empty($og['optiontitle'])) {
                        $title .= "(" . $og['optiontitle'].')';
                    }
                    $title .= ' 数量:' . $og['total'] . ' 总价: ' . $og['realprice'];
                }
                $msgdata['title'] = $title;
                $msgdata['time'] = date('Y年m月d日 H:i:s');
                $msgdata['page'] = '/pages/order/detail/index?id='.$orderid;
                $this->model->sendSubscribeMessage($_GPC['openid'], $msgdata, 'receive');
            }
        }
        
        //分销检测
        if (p('commission')) {
            p('commission')->checkOrderFinish($orderid);
        }

        //加入好物圈收藏
        $goodscircle = p('goodscircle');
        if($goodscircle){
            $goodscircle->updateOrder($order['openid'],$orderid);
        }

        return app_json();
    }

    /**
     * 删除或恢复订单
     * @global type $_W
     * @global type $_GPC
     */
    function delete() {
        global $_W, $_GPC;

        //删除订单
        $orderid = intval($_GPC['id']);
        $userdeleted = intval($_GPC['userdeleted']);
        if(empty($orderid)){
            return app_error(AppError::$ParamsError);
        }
        $order = pdo_fetch("select id,status,refundstate,refundid,openid,ordersn from " . tablename('ewei_shop_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1'
            , array(':id' => $orderid, ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
        if (empty($order)) {
            return app_error(AppError::$OrderNotFound);
        }

        if ($userdeleted == 0) {
            if ($order['status'] != 3) {
                return app_error(AppError::$OrderCannotRestore);
            }
        } else {
            if ($order['status'] != 3 && $order['status'] != -1) {
                return app_error(AppError::$OrderCannotDelete);
            }

            if ($order['refundstate'] > 0 && !empty($order['refundid'])) {

                $change_refund = array();
                $change_refund['status'] = -2;
                $change_refund['refundtime'] = time();
                pdo_update('ewei_shop_order_refund', $change_refund, array('id' => $order['refundid'], 'uniacid' => $_W['uniacid']));
            }
        }

        pdo_update('ewei_shop_order', array('userdeleted' => $userdeleted, 'refundstate' => 0), array('id' => $order['id'], 'uniacid' => $_W['uniacid']));

        //加入好物圈收藏
        $goodscircle = p('goodscircle');
        if($goodscircle){
            $goodscircle->deleteOrder($order['openid'],$order['ordersn']);
        }

        return app_json();
    }

}
