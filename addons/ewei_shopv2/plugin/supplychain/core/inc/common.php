<?php
require EWEI_SHOPV2_PLUGIN . 'supplychain/core/inc/function.php';
global $_W;
global $_GPC;
$routes = explode('.', $_W['routes']);
$GLOBALS['_W']['tab'] = ((isset($routes[2]) ? $routes[2] : ''));
$uniacid = intval($_GPC['__uniacid']);
unset($_SESSION);
$session = $_SESSION['__supplychain_uniacid'];

if (!(empty($session))) {
    $uniacid = $session;
}
if ($_W['routes'] != 'supplychain.manage.login') {
    $session_key = '__supplychain_' . $uniacid . '_session';
    $session = json_decode(base64_decode($_GPC[$session_key]), true);
    if (is_array($session)) {
        $account = pdo_fetch('select * from ' . tablename('ewei_shop_supplychain_account') . ' where id=:id limit 1', array(':id' => $session['id']));
        if (!(is_array($account)) || ($session['hash'] != md5($account['pwd'] . $account['regtime']))) {
            isetcookie($session_key, false, -100);
            header('location: ' . supplychainUrl('login'));
            exit();
        }


        $GLOBALS['_W']['uniaccount'] = $account;
    }
    else {
        isetcookie($session_key, false, -100);
        header('location: ' . supplychainUrl('login'));
        exit();
    }
}


$GLOBALS['_W']['uniacid'] = $uniacid;
$GLOBALS['_W']['supplychainid'] = $session['supplychainid'];
$GLOBALS['_W']['supplychainuid'] = $session['id'];
$GLOBALS['_W']['supplychainusername'] = $session['username'];
$GLOBALS['_W']['supplychainisfounder'] = $session['isfounder'];
$supplychain_user = pdo_fetch('select * from ' . tablename('ewei_shop_supplychain_user') .' where id=:id limit 1', array(':id' => $session['supplychainid']));
$GLOBALS['_W']['supplychain_user'] = $supplychain_user;
$GLOBALS['_W']['supplychain_username'] = $supplychain_user['supplychainname'];
$GLOBALS['_W']['accounttotal'] = $supplychain_user['accounttotal'];
unset($session);
$_W['attachurl'] = $_W['attachurl_local'] = $_W['siteroot'] . $_W['config']['upload']['attachdir'] . '/';

if (!(empty($_W['setting']['remote'][$_W['uniacid']]['type']))) {
    $_W['setting']['remote'] = $_W['setting']['remote'][$_W['uniacid']];
}


if (!(empty($_W['setting']['remote']['type']))) {
    if ($_W['setting']['remote']['type'] == ATTACH_FTP) {
        $_W['attachurl'] = $_W['attachurl_remote'] = $_W['setting']['remote']['ftp']['url'] . '/';
    }
    else if ($_W['setting']['remote']['type'] == ATTACH_OSS) {
        $_W['attachurl'] = $_W['attachurl_remote'] = $_W['setting']['remote']['alioss']['url'] . '/';
    }
    else if ($_W['setting']['remote']['type'] == ATTACH_QINIU) {
        $_W['attachurl'] = $_W['attachurl_remote'] = $_W['setting']['remote']['qiniu']['url'] . '/';
    }
    else if ($_W['setting']['remote']['type'] == ATTACH_COS) {
        $_W['attachurl'] = $_W['attachurl_remote'] = $_W['setting']['remote']['cos']['url'] . '/';
    }

}


load()->func('tpl');

function mce($permtype = '', $item = NULL)
{
    $perm = plugin_run('supplychain::check_edit', $permtype, $item);
    return $perm;
}

function mcp($plugin = '')
{
    return true;
}

function mcv($permtypes = '')
{
    $perm = plugin_run('supplychain::check_perm', $permtypes);
    return $perm;
}

function mplog($type = '', $op = '')
{
    plugin_run('merch::log', $type, $op);
}

function mca($permtypes = '')
{
}

function mp($plugin = '')
{
    $plugin = p($plugin);

    if (!($plugin)) {
        return false;
    }


    if (mcp($plugin)) {
        return $plugin;
    }


    return false;
}

function mcom($com = '')
{
    return true;
}