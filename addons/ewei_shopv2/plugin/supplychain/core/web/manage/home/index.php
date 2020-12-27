<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'supplychain/core/inc/page_supplychain.php';
class Index_EweiShopV2Page extends SupplychainWebPage
{
    public function main()
    {
        header('location:'.supplychainUrl());
    }
}

?>
