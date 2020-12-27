<?php
require EWEI_SHOPV2_PLUGIN . 'supplychain/core/inc/function.php';
class Login_EweiShopV2Page extends Page
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $i = intval($_GPC['i']);
        if (empty($i))
        {
            $_W['uniacid'] = $_COOKIE[$_W['config']['cookie']['pre'] . '__uniacid'];
        }
        else
        {
            $_W['uniacid'] = $i;
        }

        $_SESSION['__supplychain_uniacid'] = $_W['uniacid'];
        $set = m('common')->getPluginset('supplychain', $_W['uniacid']);
        if ($_W['ispost'])
        {
            $username = trim($_GPC['username']);
            $pwd = trim($_GPC['pwd']);
            if (empty($username))
            {
                show_json(0, '请输入用户名!');
            }
            if (empty($pwd))
            {
                show_json(0, '请输入密码!');
            }
//            $time=time();
//            $pwd2=md5(md5('supplychain'.trim($pwd).$time));
//            $str='md5：'.$pwd2.';账号：'.$username.';密码：'.$pwd.'--'.$time;
//            var_dump($str);die;
            $account = pdo_fetch('select * from ' . tablename('ewei_shop_supplychain_account') . ' where uniacid=:uniacid and loginname=:loginname', array(':uniacid' => $_W['uniacid'], ':loginname' => $username));
            if (empty($account))
            {
                show_json(0, '用户未找到!');
            }
            $pwd=md5(md5('supplychain'.trim($pwd).$account['regtime']));
            if ($account['pwd'] != $pwd)
            {
                show_json(0, '用户密码错误!');
            }
            $user = pdo_fetch('select status from ' . tablename('ewei_shop_supplychain_user') . ' where uniacid=:uniacid and supplychainid=:supplychainid limit 1', array(':uniacid' => $_W['uniacid'], ':supplychainid' => $account['id']));
            if (!(empty($user)))
            {
                if ($user['status'] == 2)
                {
                    show_json(0, '帐号暂停中,请联系管理员!');
                }
            }
            $account['hash'] = md5($account['pwd'].$account['regtime']);
            $session = base64_encode(json_encode($account));
            $session_key = '__supplychain_' . $account['uniacid'] . '_session';
            isetcookie($session_key, $session, 0, true);
            $status = array();
            $status['lastvisit'] = TIMESTAMP;
            $status['lastip'] = CLIENT_IP;
            pdo_update('ewei_shop_supplychain_account', $status, array('id' => $account['id']));
            $url = $_W['siteroot'] . 'web/supplychain.php?c=site&a=entry&i=' . $account['uniacid'] . '&m=ewei_shopv2&do=web';
            show_json(1, array('url' => $url));
        }
        $submitUrl = $_W['siteroot'] . 'web/supplychain.php?c=site&a=entry&i=' . $_COOKIE[$_W['config']['cookie']['pre'] . '__uniacid'] . '&m=ewei_shopv2&do=web&r=login';
        include $this->template('supplychain/manage/login');
    }
}
?>