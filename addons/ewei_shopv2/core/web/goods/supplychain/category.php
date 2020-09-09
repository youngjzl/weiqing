<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

class Category_EweiShopV2Page extends WebPage
{
    public function main()
    {
        global $_GPC;
        $supplychain=$_GPC['supplychain'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        if (empty($supplychain) || $supplychain=='wn'){
            $sql = 'SELECT id FROM ' . tablename('ewei_shop_goods_wn') . ' where id!=0';
            $contion = '';
            if ($_GPC['select_cat']) {
                $select_cat = explode(',', $_GPC['select_cat']);
                $str = '';
                foreach ($select_cat as $list) {
                    $str .= ',\'' . $list . '\'';
                }
                $str = substr($str, 1);
                $contion .= " and (Category in(" . $str . ") or TwoCategory in(" . $str . ") or ThreeCategory in(" . $str . "))";
            }
            if ($_GPC['select_deliverycode']) {
                $select_deliverycode = $_GPC['select_deliverycode'];
                $contion .= " and DeliveryCode=" . $select_deliverycode;
            }
            if ($_GPC['keyword']) {
                $keyword = $_GPC['keyword'];
                $contion .= " and (SkuNo Like '%$keyword%' or SkuName Like '%$keyword%') ";
            }
            $total_all = pdo_fetchall($sql . $contion);
            $total = count($total_all);
            $list=array();
            if (!empty($total)) {
                $sql = "select * from " . tablename('ewei_shop_goods_wn') . ' where id!=0';
                if (empty($_GPC['export'])) {
                    $contion .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
                }

                $list = pdo_fetchall($sql . $contion);
                foreach ($list as &$listc) {
                    $displayImgUrls = explode(';', $listc['displayImgUrls']);
                    $listc['displayImgUrls'] = $displayImgUrls[0];
                    $listc['supplychain_type']='维妮';
                    //发货方式1:保税区发货2:香港直邮4:海外直邮5:国内发货
                    switch ($listc['DeliveryCode']) {
                        case 1:
                            $listc['DeliveryCode'] = '保税区发货';
                            break;
                        case 2:
                            $listc['DeliveryCode'] = '香港直邮';
                            break;
                        case 4:
                            $listc['DeliveryCode'] = '海外直邮';
                            break;
                        case 5:
                            $listc['DeliveryCode'] = '国内发货';
                            break;
                    }
                }
                unset($listc);

                $pager = pagination2($total, $pindex, $psize);
            }
            $category = pdo_fetchall('select *  from ' . tablename('ewei_shop_category_wn'));
        }
        if ($supplychain=='an'){
            $sql = 'SELECT id FROM ' . tablename('ewei_shop_goods_aoan') . ' where id!=0';
            $contion = '';
            if ($_GPC['select_cat']) {
                $select_cat = explode(',', $_GPC['select_cat']);
                $str = '';
                foreach ($select_cat as $list) {
                    $str .= ',\'' . $list . '\'';
                }
                $str = substr($str, 1);
                $contion .= " and (pCatName in(" . $str . ") or catName in(" . $str . "))";
            }
            if ($_GPC['select_deliverycode']) {
                $select_deliverycode = $_GPC['select_deliverycode'];
                switch ($select_deliverycode) {
                    //贸易类型(1101保税、2202一般贸易、1303直邮、1404海外自提)
                    case 1:
                        $contion .= " and tradeType=" . 1101;
                        break;
                    case 4:
                        $contion .= " and tradeType=" . 1303;
                        break;
                    case 5:
                        $contion .= " and tradeType=" . 2202;
                        break;
                    case 6:
                        $contion .= " and tradeType=" . 1404;
                        break;
                }

            }
            if ($_GPC['keyword']) {
                $keyword = $_GPC['keyword'];
                $contion .= " and (id Like '%$keyword%' or name Like '%$keyword%') ";
            }
            $total_all = pdo_fetchall($sql . $contion);
            $total = count($total_all);
            $list=array();
            if (!empty($total)) {
                $sql = "select * from " . tablename('ewei_shop_goods_aoan') . ' where id!=0';
                if (empty($_GPC['export'])) {
                    $contion .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
                }
                $goods = pdo_fetchall($sql . $contion);
                foreach ($goods as &$listc){
                    $data['id']=$listc['id'];
                    $data['SkuName']=$listc['name'];
                    $data['displayImgUrls']=$listc['tiny'];
                    $data['Category']=$listc['pCatName'];
                    $data['TwoCategory']=$listc['catName'];
                    $data['ThreeCategory']='';
                    $data['RetailPrice']=$listc['mktprice'];
                    $data['SettlePrice']=$listc['price'];
                    $data['Quantity']=$listc['enableStore'];
                    $data['Rate']=$listc['tax']*0.01;
                    $data['Status']=1;
                    $data['DeliveryCode']=$listc['tradeTypeName'];
                    $data['supplychain_type']='澳安';
                    $list[]=$data;
                }
                $pager = pagination2($total, $pindex, $psize);
            }
            $category = pdo_fetchall('select *  from ' . tablename('ewei_shop_category_aoan'));
            foreach ($category as  &$categorys){
                $categorys['Name']=$categorys['name'];
            }
            unset($categorys);
        }
        include $this->template();
    }
}

?>
