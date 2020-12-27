<?php

require EWEI_SHOPV2_PLUGIN . 'supplychain/core/inc/page_supplychain.php';
class Index_EweiShopV2Page extends SupplychainWebPage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $user = pdo_fetch('select `id`,`logo`,`supplychainname`,`supplychaindesc` from ' . tablename('ewei_shop_supplychain_user') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $_W['uniaccount']['supplychainid'], ':uniacid' => $_W['uniacid']));
        $user['code']=$this->createCode($user['id'],'lpkostrv');
        $order_sql = 'select id,ordersn,createtime,address,price,invoicename from ' . tablename('ewei_shop_order') . ' where uniacid = :uniacid and merchid=:merchid and isparent=0 and deleted=0 AND ( status = 1 or (status=0 and paytype=3) ) ORDER BY createtime ASC LIMIT 20';
        $order = pdo_fetchall($order_sql, array(':uniacid' => $_W['uniacid'], ':merchid' => $_W['supplychainid']));

        foreach ($order as &$value) {
            $value['address'] = iunserializer($value['address']);
        }

        unset($value);
        $order_ok = $order;
        $supplychain = $_W['supplychainid'];
        $url = mobileUrl('supplychain', array('supplychainid' => $supplychain), true);
        $qrcode = m('qrcode')->createQrcode($url);

        include $this->template('home/index');
    }
    private function createCode($user_id,$salt) {

        static $source_string = 'E5FCDG3HQA4B1NOPIJ2RSTUV67MWX89KLYZ';

        $num = $user_id;

        $code = '';

        while ( $num > 0) {

            $mod = $num % 35;

            $num = ($num - $mod) / 35;

            $code = $source_string[$mod].$code;

        }

        if(empty($code[3]))
            $code = str_pad($code,4,$salt,STR_PAD_LEFT);

        return $code;

    }
    private function decode($code,$salt) {

        static $source_string = 'E5FCDG3HQA4B1NOPIJ2RSTUV67MWX89KLYZ';

        if (strrpos($code, $salt) !== false)

            $code = substr($code, strrpos($code, $salt)+1);

        $len = strlen($code);

        $code = strrev($code);

        $num = 0;

        for ($i=0; $i < $len; $i++) {

            $num += strpos($source_string, $code[$i]) * pow(35, $i);

        }

        return $num;

    }
    public function quit()
    {
        global $_W;
        global $_GPC;
        isetcookie('__supplychain_' . $_W['uniacid'] . '_session', 0);
        isetcookie('__uniacid', 0);
        isetcookie('__acid', 0);
        unset($_SESSION['__supplychain_uniacid']);
        header('location: ' . supplychainUrl('login') . '&i=' . $_W['uniacid']);
        exit();
    }
}

?>
