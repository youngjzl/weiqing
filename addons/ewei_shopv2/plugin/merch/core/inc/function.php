<?php
function merchUrl($do = '', $query = NULL, $full = false)
{
    global $_W;
    global $_GPC;
    $dos = explode('/', trim($do));
    $routes = array();
    $routes[] = $dos[0];

    if (isset($dos[1])) {
        $routes[] = $dos[1];
    }


    if (isset($dos[2])) {
        $routes[] = $dos[2];
    }


    if (isset($dos[3])) {
        $routes[] = $dos[3];
    }


    $r = implode('.', $routes);

    if (!(is_array($query))) {
        $query = array();
    }


    if (!(empty($r))) {
        $query = array_merge(array('r' => $r), $query);
    }


    $query = array_merge(array('do' => 'web'), $query);
    $query = array_merge(array('m' => 'ewei_shopv2'), $query);
    return str_replace('./index.php', './merchant.php', wurl('site/entry', $query));
}