<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'supplychain/core/inc/page_supplychain.php';

class Alipay_EweiShopV2Page extends SupplychainWebPage
{

    function main() {
        global $_W;
        include $this->template();
    }
}
