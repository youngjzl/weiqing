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
        if ($supplychain=='all'||empty($supplychain)){
            $sql=' SELECT * FROM((SELECT id,SkuName,displayImgUrls,Category,TwoCategory,RetailPrice,SettlePrice,Quantity,Rate,DeliveryCode,Status,ThreeCategory,Brand,1 FROM '. tablename('ewei_shop_goods_wn').') UNION ALL (SELECT id,name as SkuName,tiny as displayImgUrls,pCatName as Category,catName as TwoCategory,mktprice as RetailPrice,price as SettlePrice,enableStore as Quantity, tax as Rate,tradeTypeName as DeliveryCode,1,\'\',brandName as Brand,2 FROM '. tablename('ewei_shop_goods_aoan').')) as a WHERE a.`id`!=0';
            $contion = '';
            if (!empty($_GPC['select_brand'])||is_array($_GPC['select_brand'])){
                $select_brand = $_GPC['select_brand'];
                foreach ($select_brand as $list) {
                    $str .= ',\'' . $list . '\'';
                }
                $str = substr($str, 1);
                $contion .= ' and (a.Brand in('.$str.'))';
            }
            if ($_GPC['keyword']) {
                $keyword = $_GPC['keyword'];
                $contion .= " and (id Like '%$keyword%' or SkuName Like '%$keyword%') ";
            }
            $total_all = pdo_fetchall($sql . $contion);
            $total = count($total_all);
            $list=array();
            if (!empty($total)) {
                $sql=' SELECT * FROM((SELECT id,SkuName,displayImgUrls,Category,TwoCategory,RetailPrice,SettlePrice,Quantity,Rate,DeliveryCode,Status,ThreeCategory,Brand,1 FROM '. tablename('ewei_shop_goods_wn').') UNION ALL (SELECT id,name as SkuName,tiny as displayImgUrls,pCatName as Category,catName as TwoCategory,mktprice as RetailPrice,price as SettlePrice,enableStore as Quantity, tax as Rate,tradeTypeName as DeliveryCode,1,\'\',brandName as Brand,2 FROM '. tablename('ewei_shop_goods_aoan').')) as a WHERE a.`id`!=0';
                if (empty($_GPC['export'])) {
                    $contion .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
                }

                $list = pdo_fetchall($sql . $contion);
                foreach ($list as &$listc) {
                    $displayImgUrls = explode(';', $listc['displayImgUrls']);
                    $listc['displayImgUrls'] = $displayImgUrls[0];
                    switch ($listc[1]) {
                        case 1:
                            $listc['supplychain_type']='维妮';
                            $listc['supplychain_bg']='danger';
                            break;
                        case 2:
                            $listc['supplychain_type']='澳安';
                            $listc['supplychain_bg']='primary';
                            break;
                    }
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
            $brand=pdo_fetchall('SELECT * FROM((SELECT id,Brand,1 FROM '. tablename('ewei_shop_goods_wn').') UNION ALL (SELECT id,brandName as Brand,2 FROM '. tablename('ewei_shop_goods_aoan').')) as a WHERE a.`id`!=0 and a.Brand!=\'\' GROUP BY a.Brand');

        }
        if ($supplychain=='wn'){
            $sql = 'SELECT id FROM ' . tablename('ewei_shop_goods_wn') . ' where id!=0';
            $contion = '';
            if ($_GPC['select_cat']) {
                $select_cat =  $_GPC['select_cat'];
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
            if ($_GPC['select_brand']){
                $select_brand =  $_GPC['select_brand'];
                foreach ($select_brand as $list) {
                    $str .= ',\'' . $list . '\'';
                }
                $str = substr($str, 1);
                $contion .= ' and (Brand in('.$str.'))';
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
                    $listc['supplychain_bg']='danger';
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
            $brand=pdo_fetchall('select Brand  from ' . tablename('ewei_shop_goods_wn').' where Brand!=\'\' GROUP BY Brand');
        }
        if ($supplychain=='an'){
            $sql = 'SELECT id FROM ' . tablename('ewei_shop_goods_aoan') . ' where id!=0';
            $contion = '';
            if ($_GPC['select_cat']) {
                $select_cat = $_GPC['select_cat'];
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
            if ($_GPC['select_brand']){
                $select_brand =  $_GPC['select_brand'];
                foreach ($select_brand as $list) {
                    $str .= ',\'' . $list . '\'';
                }
                $str = substr($str, 1);
                $contion .= ' and (brandName in('.$str.'))';
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
                    $data['supplychain_bg']='primary';
                    $list[]=$data;
                }
                $pager = pagination2($total, $pindex, $psize);
            }
            $category = pdo_fetchall('select `name` as `Name`  from ' . tablename('ewei_shop_category_aoan'));
            $brand=pdo_fetchall('select brandName as Brand  from ' . tablename('ewei_shop_goods_aoan').' where brandName!=\'\' GROUP BY brandName');
        }
        include $this->template();
    }
}

?>
