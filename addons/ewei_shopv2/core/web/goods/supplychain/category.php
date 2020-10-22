<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

class Category_EweiShopV2Page extends WebPage
{
    public function main()
    {
        global $_GPC;
        if ($_GPC['supplychain_goodsid']){
            $supplychain_goodsid=trim($_GPC['supplychain_goodsid']);
            $supplychain_type=trim($_GPC['supplychain_type']);
            switch ($supplychain_type){
                case 1:
                    $tablename='ewei_shop_goods_wn';
                    $select='Status as status,SkuName as title,goodsNo';
                    $supplychain_type=1;
                    break;
                case 2:
                    $tablename='ewei_shop_goods_aoan';
                    $select='name as title,sn as goodsNo,goodsId,brandName as Brand';
                    $supplychain_type=2;
                    break;
            }
            //商城商品表
            $goods = pdo_fetch("select id,pcates,ccates,tcates,title,total,marketprice,productprice,costprice,goodssn,productsn,weight,keywords,supplychain_type,isrecommand,ishot,isnew,issendfree,status,is_synchronize from" . tablename('ewei_shop_goods') . 'where supplychain_goods_id='.$supplychain_goodsid.' and supplychain_type='.$supplychain_type);
            if (!empty($goods)) {
                $goods['true_supplychain']=$goods['supplychain_type'];
                $goods['is_synchronize']=empty($goods['is_synchronize'])?1:$goods['is_synchronize'];
                $goods_spec = pdo_fetchall("select * from" . tablename('ewei_shop_goods_option') . ' where goodsid=' . $goods['id']);
                $spec = array();
                foreach ($goods_spec as $list) {
                    $spec_list['id'] = $list['goodsid'];
                    $spec_list['spec'] = $list['title'];//规格名
                    $spec_list['stock'] = $list['stock'];//库存
                    $spec_list['marketprice'] = round($list['marketprice'] * 0.05 + $list['marketprice'], 2);//现价
                    $spec_list['productprice'] = $list['productprice'];//原价
                    $spec_list['costprice'] = $list['costprice'];//成本价
                    $spec_list['goodssn'] = $list['goodssn'];//编码【sku】
                    $spec_list['productsn'] = $list['productsn'];//条码
                    $spec_list['weight'] = $list['weight'];//重量（克）
                    $spec[] = $spec_list;
                }
                //如果ewei_shop_goods_option没有数据就填充商品表的数据
                if (empty($spec)) {
                    $spec_list['id'] = '';
                    $spec_list['spec'] = $goods['title'];//规格名
                    $spec_list['stock'] = $goods['total'];//库存
                    $spec_list['marketprice'] = round($goods['marketprice'] * 0.05 + $goods['marketprice'], 2);//现价
                    $spec_list['productprice'] = $goods['productprice'];//原价
                    $spec_list['costprice'] = $goods['costprice'];//成本价
                    $spec_list['goodssn'] = $goods['goodssn'];//编码【sku】
                    $spec_list['productsn'] = $goods['productsn'];//条码
                    $spec_list['weight'] = $goods['weight'];//重量（克）
                    $spec[] = $spec_list;
                }
                show_json(1, array('goods' => $goods, 'spec' => $spec, 'is_supplychain' => 0));
            }

            //供应链商品表
            $supplychain_goods= pdo_fetch("select KeyWords as keywords,$select from" . tablename($tablename) . ' where id='.$supplychain_goodsid);
            if (!empty($supplychain_goods)){
                $supplychain_goods['true_supplychain']=$supplychain_type;
                $supplychain_goods['is_synchronize']=1;
                if ($supplychain_type===1){
                    $goods_spec=pdo_fetchall("select * from" . tablename('ewei_shop_goods_wn') . ' where goodsNo=\''.$supplychain_goods['goodsNo'].'\'');
                    $spec = array();
                    foreach ($goods_spec as $list) {
                        $spec_list['id'] = $list['id'];
                        $spec_list['spec'] = $list['Spec'];//规格名
                        $spec_list['stock'] = $list['Quantity'];//库存
                        $spec_list['marketprice'] = round($list['SettlePrice'] * 0.05 + $list['SettlePrice'], 2);//现价
                        $spec_list['productprice'] = $list['RetailPrice'];//原价
                        $spec_list['costprice'] = $list['SettlePrice'];//成本价
                        $spec_list['goodssn'] = $list['SkuNo'];//编码【sku】
                        $spec_list['productsn'] = $list['BarCode'];//条码
                        $spec_list['weight'] = $list['Weight'];//重量（克）
                        $spec[] = $spec_list;
                    }
                    $supplychain_goods['keywords']=empty($supplychain_goods['keywords']) ? '':$supplychain_goods['keywords'];
                    show_json(1,array('goods'=>$supplychain_goods,'spec'=>$spec,'cats'=>'','is_supplychain'=>1));
                }
                if ($supplychain_type===2){
                    $supplychain_goods['status']=1;
                    $goods_spec=pdo_fetchall("select * from" . tablename('ewei_shop_goods_aoan_spec') . ' where goodsId=\''.$supplychain_goods['goodsId'].'\'');
                    $spec = array();
                    foreach ($goods_spec as $list) {
                        $spec_list['id'] = $list['id'];
                        $spec_list['spec'] = $list['productName'];//规格名
                        $spec_list['stock'] = $list['productNum'];//库存
                        $spec_list['marketprice'] = round($list['price'] * 0.05 + $list['price'], 2);//现价
                        $spec_list['productprice'] = '';//原价
                        $spec_list['costprice'] = $list['price'];//成本价
                        $spec_list['goodssn'] = $list['goodsId'];//编码【sku】
                        $spec_list['productsn'] = '';//条码
                        $spec_list['weight'] = $list['weight'];//重量（克）
                        $spec[] = $spec_list;
                    }
                    $supplychain_goods['keywords']=empty($supplychain_goods['keywords']) ? '':$supplychain_goods['keywords'];
                    show_json(1,array('goods'=>$supplychain_goods,'spec'=>$spec,'cats'=>'','is_supplychain'=>1));
                }
            }
            show_json(0,'没有数据');
        }
        $supplychain_name=$_GPC['$supplychain_name'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        if ($supplychain_name=='all'||empty($supplychain_name)){
            $sql=' SELECT a.id FROM((SELECT id,SkuName,displayImgUrls,Category,TwoCategory,RetailPrice,SettlePrice,Quantity,Rate,DeliveryCode,Status,ThreeCategory,Brand,goodsNo,Keywords,1 FROM '. tablename('ewei_shop_goods_wn').') UNION ALL (SELECT id,name as SkuName,tiny as displayImgUrls,pCatName as Category,catName as TwoCategory,mktprice as RetailPrice,price as SettlePrice,enableStore as Quantity, tax as Rate,tradeTypeName as DeliveryCode,1,\'\',brandName as Brand,goodsId as goodsNo,Keywords,2 FROM '. tablename('ewei_shop_goods_aoan').')) as a WHERE a.`id`!=0 ' ;
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
                $contion .= " and (a.id Like '%$keyword%' or a.SkuName Like '%$keyword%' or a.Keywords Like '%$keyword%') ";
            }
            $contion .='group by a.goodsNo order by `1` asc';
            $total_all = pdo_fetchall($sql . $contion);
            $total = count($total_all);
            $list=array();
            if (!empty($total)) {
                $sql=' SELECT * FROM((SELECT id,SkuName,displayImgUrls,Category,TwoCategory,RetailPrice,SettlePrice,Quantity,Rate,DeliveryCode,Status,ThreeCategory,Brand,goodsNo,Keywords,1 FROM '. tablename('ewei_shop_goods_wn').') UNION ALL (SELECT id,name as SkuName,tiny as displayImgUrls,pCatName as Category,catName as TwoCategory,mktprice as RetailPrice,price as SettlePrice,enableStore as Quantity, tax as Rate,tradeTypeName as DeliveryCode,1,\'\',brandName as Brand,goodsId as goodsNo,Keywords,2 FROM '. tablename('ewei_shop_goods_aoan').')) as a WHERE a.`id`!=0 ';
                if (empty($_GPC['export'])) {
                    $contion .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
                }

                $list = pdo_fetchall($sql . $contion);
                foreach ($list as &$listc) {
                    $displayImgUrls = explode(';', $listc['displayImgUrls']);
                    $listc['displayImgUrls'] = $displayImgUrls[0];
                    $goodsid=pdo_fetch('select id from'.tablename('ewei_shop_goods').' where supplychain_goods_id='.$listc['id'].' and supplychain_type ='.$listc[1]);
                    $listc['goodsid']=empty($goodsid)?0:$goodsid['id'];
                    $listc['true_supplychain']=empty($listc['true_supplychain']) ? 0:$listc[1];
                    switch ($listc[1]) {
                        case 1:
                            $listc['supplychain_type']=1;
                            $listc['supplychain_bg']='danger';
                            break;
                        case 2:
                            $listc['supplychain_type']=2;
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
        if ($supplychain_name=='wn'){
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
                $contion .= " and (SkuNo Like '%$keyword%' or SkuName Like '%$keyword%' or Keywords Like '%$keyword%') ";
            }
            if ($_GPC['select_brand']){
                $select_brand =  $_GPC['select_brand'];
                foreach ($select_brand as $list) {
                    $str .= ',\'' . $list . '\'';
                }
                $str = substr($str, 1);
                $contion .= ' and (Brand in('.$str.'))';
            }
            $contion.=' group by goodsNo ORDER BY id ASC ';
            $total_all = pdo_fetchall($sql . $contion);
            $total = count($total_all);
            $list=array();
            if (!empty($total)) {
                $sql = 'SELECT * FROM ' . tablename('ewei_shop_goods_wn') . ' where id!=0';
                if (empty($_GPC['export'])) {
                    $contion .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
                }
                $list = pdo_fetchall($sql . $contion);
                foreach ($list as &$listc) {
                    $goodsid=pdo_fetch('select id from'.tablename('ewei_shop_goods').' where supplychain_goods_id='.$listc['id'].' and supplychain_type =1');
                    $listc['goodsid']=empty($goodsid)?0:$goodsid['id'];

                    $displayImgUrls = explode(';', $listc['displayImgUrls']);
                    $listc['displayImgUrls'] = $displayImgUrls[0];
                    $listc['supplychain_type']=1;
                    $listc['supplychain_bg']='danger';
                    $listc['true_supplychain']=1;
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
        if ($supplychain_name=='an'){
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
                $contion .= " and (id Like '%$keyword%' or name Like '%$keyword%' or Keywords Like '%$keyword%') ";
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
                    $goodsid=pdo_fetch('select id from'.tablename('ewei_shop_goods').' where supplychain_goods_id='.$listc['id'].' and supplychain_type =2');
                    $data['goodsid']=empty($goodsid)?0:$goodsid['id'];
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
                    $data['supplychain_type']=2;
                    $data['supplychain_bg']='primary';
                    $data['Brand']=$listc['brandName'];
                    $data['true_supplychain']=2;
                    $list[]=$data;
                }
                $pager = pagination2($total, $pindex, $psize);
            }
            $category = pdo_fetchall('select `name` as `Name`  from ' . tablename('ewei_shop_category_aoan'));
            $brand=pdo_fetchall('select brandName as Brand  from ' . tablename('ewei_shop_goods_aoan').' where brandName!=\'\' GROUP BY brandName');
        }
        $path=EWEI_SHOPV2_CORE.'/web/goods/supplychain/supplychaintype.php';
        $supplychainlist=include($path);
        include $this->template();
    }

    public function add(){
        $this->post();
    }
    public function edit(){
        $this->post();
    }
    public function post(){
        global $_GPC;
        $supplychain=$_GPC['supplychain'];//供应链id
        $true_supplychain=$_GPC['true_supplychain'];//用户选择的供应链
        $supplychain_goodsid=$_GPC['supplychain_goodsid'];//供应链的商品id
        $spexlist=json_decode(htmlspecialchars_decode($_GPC['spexlist']),true);//多规格数据
        $cats=$_GPC['area'];//商品分类
        if (empty($supplychain_goodsid)||empty($supplychain)||empty($spexlist)||empty($cats)||empty($true_supplychain)){
            show_json(0,'数据错误，请联系管理员');
        }

        switch ($supplychain){
            case 2:
                $tablename='ewei_shop_goods_aoan';
                $supplychain_type=2;
                break;
            case 1:
                $tablename='ewei_shop_goods_wn';
                $supplychain_type=1;
                break;
            default:
                show_json(1, '数据错误');
        }
        $goods= pdo_fetch("select * from" . tablename('ewei_shop_goods') . 'where supplychain_goods_id='.$supplychain_goodsid.' and supplychain_type='.$supplychain_type);
        if (!empty($goods)){
            //规格项目
            $spec = array();
            foreach ($spexlist as $list) {
                $spec_list['id'] = $list[1];//规格id
                $spec_list['title'] = $list[0];//规格名
                $spec_list['stock'] = $list[2];//库存
                $spec_list['marketprice'] = round($list[3] * 0.05 + $list[3], 2);//现价
                $spec_list['productprice'] = $list[4];//原价
                $spec_list['costprice'] = $list[5];//成本价
                $spec_list['goodssn'] = $list[6];//编码【sku】
                $spec_list['productsn'] = $list[7];//条码
                $spec_list['weight'] = $list[8];//重量（克）
                $spec[] = $spec_list;
            }

            //商品数据
            $data=array(
                'keywords'=>$_GPC['keywords'],
                'pcate' => $_GPC['area'][0],//一级分类
                'pcates' => $_GPC['area'][0],//一级分类
                'ccates' => empty($_GPC['area'][1])?'':$_GPC['area'][1],//二级分类
                'tcates' => empty($_GPC['area'][2])?'':$_GPC['area'][2],//三级分类
                'isrecommand' => empty($_GPC['isrecommand'])?'':$_GPC['isrecommand'],//推荐
                'isnew' => empty($_GPC['isnew'])?'':$_GPC['isnew'],//新品
                'ishot' => empty($_GPC['ishot'])?'':$_GPC['ishot'],//热卖
                'issendfree' => empty($_GPC['issendfree'])?'':$_GPC['issendfree'],//包邮
                'status'=>$_GPC['virtualsend'],//上架状态 1上架 2下架
            );
            //有产生多规格的字段另加处理
            if (count($spec)>1){
                foreach ($spec as $list){
                    if (empty($list['id'])){
                        show_json(1, '数据错误');
                    }
                    $option=pdo_fetch('select * from'.tablename('ewei_shop_goods_option').'where goodsid='.$list['id']);
                    if (!empty($option)){
                        $id=$list['id'];
                        unset($list['id']);
                        pdo_update('ewei_shop_goods_option',$list,array('goodsid'=>$id));
                    }
                }
                pdo_update("$tablename",array('KeyWords'=>$data['keywords']),array('id'=>$supplychain_goodsid));
                pdo_update('ewei_shop_goods',$data,array('supplychain_goods_id'=>$supplychain_goodsid,'supplychain_type'=>$supplychain_type));
                show_json(1, '修改成功');
            }

            //规格只有一项的话，就是ewei_shop_goods的数据
            $data=array_merge($spec[0],$data);
            $data['total']=$data['stock'];
            unset($data['stock']);
            pdo_update('ewei_shop_goods',$data,array('supplychain_goods_id'=>$supplychain_goodsid,'supplychain_type'=>$supplychain_type));
            pdo_update("$tablename",array('KeyWords'=>$data['keywords']),array('id'=>$supplychain_goodsid));
            show_json(1, '修改成功');
        }
        else{
            $supplychain_goods = pdo_fetch("select * from" . tablename($tablename) . ' where id=' . $supplychain_goodsid);
            $supplychain_goods['true_supplychain']=trim($true_supplychain);
            $supplychain_goods['is_synchronize']=trim($_GPC['is_synchronize']);
            if (!empty($supplychain_goods)) {
                //修改供应链关键词
                pdo_update("$tablename",array('KeyWords'=>$_GPC['keywords']),array('id'=>$supplychain_goodsid));
                if ($supplychain_type===1){
                    $this->_insert_wn($supplychain_goods);
                }
                if ($supplychain_type===2){
                    $this->_insert_aoan($supplychain_goods);
                }
            }
            show_json(0,'数据错误');
        }
    }

    //分类
    public function ajaxcategory(){
        global $_GPC;
        $id=$_GPC['id'];
        $category=pdo_fetchall("select * from" . tablename('ewei_shop_category').' where parentid='.$id);
        show_json(1, array('data'=>$category));
    }
    //品牌
    public function ajaxbrand(){
        global $_GPC;
        $offset=$_GPC['offset'];//每页条数
        $page=($_GPC['page']-1)*$offset;//页码
        $key=$_GPC['key'];//关键词

        $contion ='';
        if (!empty($key)){
            $contion .=" and Brand Like '%$key%' ";
        }

        $brand=pdo_fetchall("SELECT * FROM((SELECT id,Brand,1 FROM ". tablename('ewei_shop_goods_wn').") UNION ALL (SELECT id,brandName as Brand,2 FROM ". tablename('ewei_shop_goods_aoan').")) as a WHERE a.`id`!=0 and a.Brand!='' $contion GROUP BY a.Brand  limit $page,$offset ");
        $brand_repeat=array('results'=>array(),'pagination'=>array('more'=>true));
        if (!empty($brand)){
            foreach ($brand as &$list){
                $brandlist['id']=$list['Brand'];
                $brandlist['text']=$list['Brand'];
                $brand_repeat['results'][]=$brandlist;
            }
            show_json(1, array('data'=>$brand_repeat));
        }
        show_json(1, array('data'=>$brand_repeat));
    }

    //逻辑：商品分为ims_ewei_shop_goods表展示主要数据;ims_ewei_shop_goods_option展示商品多规格数据;
    private function _insert_aoan($supplychain_goods){
        global $_GPC;
        //---------准备ims_ewei_shop_goods数据
        //分类-字符串
        $cats = implode(',', $_GPC['area']);
        $data = array(
            'title' => $supplychain_goods['name'],//商品名称
            'timestart' => 1598255880,//限卖开始时间
            'timeend' => 1598860680,//限卖结束时间
            'statustimestart' => time(),//上架时间
            'statustimeend' => 1756275682,//下架时间 2025-08-27 14:21:22
            'pcate' => $_GPC['area'][0],//一级分类
            'ccate' => '',
            'tcate' => '',
            'pcates' => $_GPC['area'][0],//一级分类
            'ccates' => empty($_GPC['area'][1]) ? '' : $_GPC['area'][1],//二级分类
            'tcates' => empty($_GPC['area'][2]) ? '' : $_GPC['area'][2],//三级分类
            'diymode' => 0,
            'thumb' => $supplychain_goods['big'],//商品图(主图)
            'thumb_url' => $supplychain_goods['thumbnail'],//缩略图地址
            'type' => 1,//商品类型-默认实体商品
            'status' => $_GPC['virtualsend'],//上架状态 1上架 2下架
            'isverify' => 1,//包邮设置，默认1
            'hasoption' => 0,//是否启用商品多规格
            'uniacid' => 2,//公众号id
            'displayorder' => '',//排序方式
            'subtitle' => '',//副标题
            'shorttitle' => '',//商品短标题 用于快递打印
            'keywords' => empty($_GPC['keywords']) ? '' : $_GPC['keywords'],//商品关键字,能准确搜到商品
            'thumb_first' => 0,//是否详情显示首图
            'showsales' => 0,//是否显示销量
            'ispresell' => 0,//是否开启商品预售设置
            'presellover' => 0,//预售商品状态
            'presellovertime' => 0,//商品转为正常销售时间
            'presellprice' => 0,//预售价格0
            'presellstart' => '',//预售开始时间是否点击
            'presellend' => '',//预售结束时间是否点击
            'preselltimestart' => '',//预售开始时间（当前时间）
            'preselltimeend' => '',//预售结束时间（当前时间）
            'presellsendtype' => 0,//是否选中发货时间
            'presellsendstatrttime' => '',//发货时间（当前时间）
            'presellsendtime' => '',//购买几天后发货
            'labelname' => '',//标签名称（如正品保证，七天无理由退货）
            'isrecommand' => empty($_GPC['isrecommand']) ? '' : $_GPC['isrecommand'],//推荐
            'ishot' => empty($_GPC['ishot']) ? '' : $_GPC['ishot'],//热卖
            'isnew' => empty($_GPC['isnew']) ? '' : $_GPC['isnew'],//新品
            'isdiscount' => '',//是否选中促销
            'isdiscount_title' => '',//促销标题
            'isdiscount_time_start' => 0,//act_time['start']
            'isdiscount_time' => 0,//act_time['start']
            'issendfree' => empty($_GPC['issendfree']) ? '' : $_GPC['issendfree'],//包邮
            'isnodiscount' => '',//不参与折扣
            'istime' => '',//是否选中限时卖
            'description' => '',//分享描述
            'goodssn' => $supplychain_goods['goodsId'],//编码
            'unit' => '件',//单位, 如: 个/件/包
            'total' => $supplychain_goods['enableStore'],//库存
            'showtotal' => '',//显示库存
            'totalcnf' => 1,//付款减库存
            'unite_total' => 0,
            'marketprice' => $supplychain_goods['price'] * 0.05 + $supplychain_goods['price'],//现价（商品销售价）
            'weight' => '',//重量（克）
            'costprice' => $supplychain_goods['price'],//成本价
            'productprice' => $supplychain_goods['mktprice'],//原价
            'minprice' => $supplychain_goods['mktprice'],
            'maxprice' => $supplychain_goods['mktprice'],
            'productsn' => $supplychain_goods['sn'],//条码
            'credit' => '',//积分赠送
//                        'maxbuy' => empty($supplychain_goods['LimitNum']) ? 0: $supplychain_goods['LimitNum'],//单次最多购买
            'maxbuy' => '',//单次最多购买
            'minbuy' => '',//单次最低购买
            'usermaxbuy' => '',//用户购买过的此商品数量限制
            'sales' => '',//已出售数
            'share_icon' => '',//分享图标
            'share_title' => '',//分享标题
            'groupstype' => 0,//是否支持拼团(1支持0不)
            'virtualsend' => 0,//自动发货
            'virtualsendcontent' => '',//自动发货内容
            'buyshow' => '',//商品详情是否在购买后才可见
            'showlevels' => '',//会员等级浏览权限
            'buylevels' => '',//会员等级购买权限
            'showgroups' => '',//会员组浏览权限
            'buygroups' => '',//会员组购买权限
            'noticeopenid' => '',//商家通知
            'noticetype' => '',//通知方式
            'needfollow' => 0,//购买强制关注
            'followurl' => '',//关注引导
            'followtip' => '',//未关注提示
            'deduct' => '',//积分最多抵扣--单位元
            'manydeduct' => '',//允许多件累计抵扣,如果设置0，则不支持积分抵扣
            'manydeduct2' => '',//允许多件累计抵扣,如果设置0，则支持全额抵扣，设置-1 不支持余额抵扣
            'deduct2' => '',//
            'virtual' => 0,//type==3?intval($_GPC['virtual']):0,
            'ednum' => '',//如果设置0或空，则不支持满件包邮
            'edareas' => '',//不参与单品包邮地区
            'edareas_code' => '',//添加不参加满包邮的地区
            'edmoney' => '',//单品满额包邮 单位元
            'invoice' => '',//发票
            'repair' => '',//保修
            'seven' => '',//7天无理由退换
            'money' => '',//余额返现
            'province' => $supplychain_goods['countryName'],//商品所在地
            'city' => '',//市
            'quality' => '',//正品保证
            'sharebtn' => '',//
            'autoreceive' => '',//确认收货时间
            'cannotrefund' => 1,//是否支持退换货
            'refund' => 1,//退款
            'returngoods' => 1,//退货退款
            'exchange' => 0,//换货
            'buyagain' => '',//重复购买折扣
            'buyagain_islong' => 0,//购买一次后,以后都使用这个折扣 是否持续使用
            'buyagain_condition' => 0,//重复购买使用条件,是付款后还是完成后 , 默认是付款后
            'buyagain_sale' => 0,//重复购买时,是否与其他优惠共享!其他优惠享受后,在使用这个折扣
            'diypage' => '',//
            'cashier' => '',//支持收银台
            'video' => '',//首图视频
            'nosearch' => 0,
            'isstatustime' => 0,
            'verifytype' => '',
            'storeids' => '',
            'cash' => '',
            'isforceverifystore' => '0',
            'diyformtype' => '0',
            'detail_logo' => '',
            'detail_shopname' => '',
            'detail_totaltitle' => '',
            'detail_btntext1' => '',
            'detail_btnurl1' => '',
            'detail_btntext2' => '',
            'detail_btnurl2' => '',
            'isendtime' => '0',
            'usetime' => '',
            'endtime' => time(),//当前时间
            'cates' => $cats,//分类
            'content' => $supplychain_goods['Details'],//商品详情
            'buycontent' => '',
            'dispatchtype' => '0',
            'dispatchid' => '0',
            'dispatchprice' => '',
            'supplychain_type'=>$supplychain_goods['true_supplychain'],
            'supplychain_goods_id'=>$supplychain_goods['id'],
            'is_synchronize'=>$supplychain_goods['is_synchronize'],
        );
        //---------准备ims_ewei_shop_goods数据 end

        //准备规格项ims_ewei_shop_goods_option数据
        $goods_spec = pdo_fetchall("select * from" . tablename('ewei_shop_goods_aoan') . ' where goodsId=\'' . $supplychain_goods['goodsId'] . '\'');
        if (!empty($goods_spec)) {
            $price = array();//价格
            $spec = array();//规格项
            foreach ($goods_spec as $list) {
                $spec_list['id'] = $list['goodsId'];//规格id
                $spec_list['spec'] = $list['productName'];//规格名
                $spec_list['stock'] = $list['productNum'];//库存
                $spec_list['marketprice'] = round($list['price'] * 0.05 + $list['price']+($list['RetailPrice']*$list['tax']), 2);//现价
                $spec_list['productprice'] = '';//原价
                $spec_list['costprice'] = $list['price'];//成本价
                $spec_list['goodssn'] = $list['goodsId'];//编码【sku】
                $spec_list['productsn'] = $list['sn'];//条码
                $spec_list['weight'] = $list['weight'];//重量（克）

                $spec[] = $spec_list;
                $price[] = $spec_list['marketprice'];
            }

            $data['minprice'] = min($price);
            $data['maxprice'] = max($price);
            $data['hasoption'] = 1;
            $data['unit'] = $spec[0]['unit'];
            $data['weight'] = $spec[0]['weight'];
            //插入商品表 ewei_shop_goods
            pdo_insert('ewei_shop_goods', $data);
            $id = pdo_insertid();
            if (empty($id)) {
                show_json(0, '商品加入失败');
            }
            //规格表
            if ($id) {
                $totalstocks = 0;//总库存

                $spc = array(
                    "uniacid" => 2,
                    "goodsid" => $id,
                    "displayorder" => 0,
                    "title" => '规格',
                );
                pdo_insert("ewei_shop_goods_spec", $spc);
                $spec_id = pdo_insertid();

                //规格大概表
                if ($spec_id) {
                    foreach ($spec as $key => $vl) {
                        $spc_item = array(
                            "uniacid" => 2,
                            "specid" => $spec_id,
                            "displayorder" => $key,
                            "title" => $vl['spec'],
                            "show" => 1,
                            "thumb" => '',
                            "virtual" => 0
                        );
                        pdo_insert("ewei_shop_goods_spec_item", $spc_item);
                        $itemid = pdo_insertid();
                        //规格项详情
                        $itemids = array();
                        if ($itemid) {
                            $itemids[] = $itemid;
                            $spc_item_option = array(
                                "uniacid" => 2,
                                "title" => $vl['spec'],
                                "productprice" => $vl['productprice'],
                                "costprice" => $vl['costprice'],
                                "marketprice" => $vl['marketprice'],//销售价
                                "presellprice" => 0,
                                "stock" => $vl['stock'],
                                "weight" => $vl['weight'],
                                "goodssn" => $vl['goodssn'],
                                "productsn" => $vl['productsn'],
                                "goodsid" => $id,
                                "specs" => $itemid,
                                'virtual' => 0,
                            );
                            $totalstocks += $spc_item_option['stock'];
                            $spc_item_option_id = pdo_insert("ewei_shop_goods_option", $spc_item_option);
                            $spc_item_option_ids[] = $spc_item_option_id;
                        }
                        //修改库存为总库存+杂七杂八的参数很恼火想死
                        $is_discounts = array('type' => 1, 'default' => array());
                        $commission = array('type' => 0, 'default' => array());
                        foreach ($spc_item_option_ids as $oid) {
                            $is_discounts['default'] = ['option' . $oid];
                            $commission['default']['option' . $oid] = array();
                        }
                        $is_discounts_json = json_encode($is_discounts);
                        $commission_json = json_encode($commission);
                        pdo_update('ewei_shop_goods', array('discounts' => '{"type":"0","default":"","default_pay":""}', 'total' => $totalstocks, 'isdiscount_discounts' => $is_discounts_json, 'commission' => $commission_json), array('id' => $id));
                    }
                    pdo_update("ewei_shop_goods_spec", array("content" => serialize($itemids)), array("id" => $spec_id));
                }
            }
            show_json(1, '加入商品成功');
        }
    }

    private function _insert_wn($supplychain_goods)
    {
        global $_GPC;
        //---------准备ims_ewei_shop_goods数据
        //商品图片
        $img_url = $this->array_images($supplychain_goods['displayImgUrls']);
        $thumb = $img_url[0];
        $thumb_url = serialize($img_url);
        //分类-字符串
        $cats = implode(',', $_GPC['area']);
        $data = array(
            'title' => $this->get_unit_goodsname($supplychain_goods['SkuName'])['goodsname'],//商品名称
            'timestart' => 1598255880,//限卖开始时间
            'timeend' => 1598860680,//限卖结束时间
            'statustimestart' => time(),//上架时间
            'statustimeend' => 1756275682,//下架时间 2025-08-27 14:21:22
            'pcate' => $_GPC['area'][0],//一级分类
            'ccate' => '',
            'tcate' => '',
            'pcates' => $_GPC['area'][0],//一级分类
            'ccates' => empty($_GPC['area'][1]) ? '' : $_GPC['area'][1],//二级分类
            'tcates' => empty($_GPC['area'][2]) ? '' : $_GPC['area'][2],//三级分类
            'diymode' => 0,
            'thumb' => $thumb,//商品图(主图)
            'thumb_url' => $thumb_url,//缩略图地址
            'type' => 1,//商品类型-默认实体商品
            'status' => $_GPC['virtualsend'],//上架状态 1上架 2下架
            'isverify' => 1,//包邮设置，默认1
            'hasoption' => 0,//是否启用商品多规格
            'uniacid' => 2,//公众号id
            'displayorder' => '',//排序方式
            'subtitle' => '',//副标题
            'shorttitle' => '',//商品短标题 用于快递打印
            'keywords' => empty($_GPC['keywords']) ? '' : $_GPC['keywords'],//商品关键字,能准确搜到商品
            'thumb_first' => 0,//是否详情显示首图
            'showsales' => 0,//是否显示销量
            'ispresell' => 0,//是否开启商品预售设置
            'presellover' => 0,//预售商品状态
            'presellovertime' => 0,//商品转为正常销售时间
            'presellprice' => 0,//预售价格0
            'presellstart' => '',//预售开始时间是否点击
            'presellend' => '',//预售结束时间是否点击
            'preselltimestart' => '',//预售开始时间（当前时间）
            'preselltimeend' => '',//预售结束时间（当前时间）
            'presellsendtype' => 0,//是否选中发货时间
            'presellsendstatrttime' => '',//发货时间（当前时间）
            'presellsendtime' => '',//购买几天后发货
            'labelname' => '',//标签名称（如正品保证，七天无理由退货）
            'isrecommand' => empty($_GPC['isrecommand']) ? '' : $_GPC['isrecommand'],//推荐
            'ishot' => empty($_GPC['ishot']) ? '' : $_GPC['ishot'],//热卖
            'isnew' => empty($_GPC['isnew']) ? '' : $_GPC['isnew'],//新品
            'isdiscount' => '',//是否选中促销
            'isdiscount_title' => '',//促销标题
            'isdiscount_time_start' => 0,//act_time['start']
            'isdiscount_time' => 0,//act_time['start']
            'issendfree' => empty($_GPC['issendfree']) ? '' : $_GPC['issendfree'],//包邮
            'isnodiscount' => '',//不参与折扣
            'istime' => '',//是否选中限时卖
            'description' => substr($supplychain_goods['Details'], '0', 400),//分享描述
            'goodssn' => $supplychain_goods['goodsNo'],//编码
            'unit' => $this->get_unit_goodsname($supplychain_goods['Name'])['unit'],//单位, 如: 个/件/包
            'total' => $supplychain_goods['Quantity'],//库存
            'showtotal' => '',//显示库存
            'totalcnf' => 1,//付款减库存
            'unite_total' => 0,
            'marketprice' => $supplychain_goods['SettlePrice'] * 0.05 + $supplychain_goods['SettlePrice'],//现价（商品销售价）
            'weight' => $supplychain_goods['Weight'],//重量（克）
            'costprice' => $supplychain_goods['SettlePrice'],//成本价
            'productprice' => $supplychain_goods['RetailPrice'],//原价
            'minprice' => $supplychain_goods['SettlePrice'],
            'maxprice' => $supplychain_goods['SettlePrice'],
            'productsn' => $supplychain_goods['BarCode'],//条码
            'credit' => '',//积分赠送
//                        'maxbuy' => empty($supplychain_goods['LimitNum']) ? 0: $supplychain_goods['LimitNum'],//单次最多购买
            'maxbuy' => '',//单次最多购买
            'minbuy' => '',//单次最低购买
            'usermaxbuy' => '',//用户购买过的此商品数量限制
            'sales' => '',//已出售数
            'share_icon' => '',//分享图标
            'share_title' => '',//分享标题
            'groupstype' => 0,//是否支持拼团(1支持0不)
            'virtualsend' => 0,//自动发货
            'virtualsendcontent' => '',//自动发货内容
            'buyshow' => '',//商品详情是否在购买后才可见
            'showlevels' => '',//会员等级浏览权限
            'buylevels' => '',//会员等级购买权限
            'showgroups' => '',//会员组浏览权限
            'buygroups' => '',//会员组购买权限
            'noticeopenid' => '',//商家通知
            'noticetype' => '',//通知方式
            'needfollow' => 0,//购买强制关注
            'followurl' => '',//关注引导
            'followtip' => '',//未关注提示
            'deduct' => '',//积分最多抵扣--单位元
            'manydeduct' => '',//允许多件累计抵扣,如果设置0，则不支持积分抵扣
            'manydeduct2' => '',//允许多件累计抵扣,如果设置0，则支持全额抵扣，设置-1 不支持余额抵扣
            'deduct2' => '',//
            'virtual' => 0,//type==3?intval($_GPC['virtual']):0,
            'ednum' => '',//如果设置0或空，则不支持满件包邮
            'edareas' => '',//不参与单品包邮地区
            'edareas_code' => '',//添加不参加满包邮的地区
            'edmoney' => '',//单品满额包邮 单位元
            'invoice' => '',//发票
            'repair' => '',//保修
            'seven' => '',//7天无理由退换
            'money' => '',//余额返现
            'province' => $supplychain_goods['DeliveryCity'],//商品所在地
            'city' => '',//市
            'quality' => '',//正品保证
            'sharebtn' => '',//
            'autoreceive' => '',//确认收货时间
            'cannotrefund' => 1,//是否支持退换货
            'refund' => 0,//退款
            'returngoods' => 0,//退货退款
            'exchange' => 0,//换货
            'buyagain' => '',//重复购买折扣
            'buyagain_islong' => 0,//购买一次后,以后都使用这个折扣 是否持续使用
            'buyagain_condition' => 0,//重复购买使用条件,是付款后还是完成后 , 默认是付款后
            'buyagain_sale' => 0,//重复购买时,是否与其他优惠共享!其他优惠享受后,在使用这个折扣
            'diypage' => '',//
            'cashier' => '',//支持收银台
            'video' => '',//首图视频
            'nosearch' => 0,
            'isstatustime' => 0,
            'verifytype' => '',
            'storeids' => '',
            'cash' => '',
            'isforceverifystore' => '0',
            'diyformtype' => '0',
            'detail_logo' => '',
            'detail_shopname' => '',
            'detail_totaltitle' => '',
            'detail_btntext1' => '',
            'detail_btnurl1' => '',
            'detail_btntext2' => '',
            'detail_btnurl2' => '',
            'isendtime' => '0',
            'usetime' => '',
            'endtime' => time(),//当前时间
            'cates' => $cats,//分类
            'content' => $this->_goods_detail($supplychain_goods['detailImgUrls'], $supplychain_goods['Details']),//商品详情
            'buycontent' => '',
            'dispatchtype' => '0',
            'dispatchid' => '0',
            'dispatchprice' => '',
            'supplychain_type'=>$supplychain_goods['true_supplychain'],
            'supplychain_goods_id'=>$supplychain_goods['id'],
            'is_synchronize'=>$supplychain_goods['is_synchronize'],
        );
        //---------准备ims_ewei_shop_goods数据 end

        //准备规格项ims_ewei_shop_goods_option数据
        $goods_spec = pdo_fetchall("select * from" . tablename('ewei_shop_goods_wn') . ' where goodsNo=\'' . $supplychain_goods['goodsNo'] . '\'');
        if (count($goods_spec) > 1) {
            $status = array();//上下架状态
            $price = array();//价格
            $spec = array();//规格项
            foreach ($goods_spec as $list) {
                $spec_list['id'] = $list['id'];//规格名
                $spec_list['spec'] = $list['Spec'];//规格名
                $spec_list['stock'] = $list['Quantity'];//库存
                $spec_list['marketprice'] = round($list['SettlePrice'] * 0.05 + $list['SettlePrice'], 2);//现价
                $spec_list['productprice'] = $list['RetailPrice'];//原价
                $spec_list['costprice'] = $list['SettlePrice'];//成本价
                $spec_list['goodssn'] = $list['SkuNo'];//编码【sku】
                $spec_list['productsn'] = $list['BarCode'];//条码
                $spec_list['weight'] = $list['Weight'];//重量（克）

                $spec[] = $spec_list;
                $price[] = $spec_list['marketprice'];
                $status[] = $list['Status'];
            }

            $data['status'] = max($status);
            $data['minprice'] = min($price);
            $data['maxprice'] = max($price);
            $data['hasoption'] = 1;

            //插入商品表 ewei_shop_goods
            pdo_insert('ewei_shop_goods', $data);
            $id = pdo_insertid();
            if (empty($id)) {
                show_json(0, '商品加入失败');
            }
            //规格表
            if ($id) {
                $totalstocks = 0;//总库存

                $spc = array(
                    "uniacid" => 2,
                    "goodsid" => $id,
                    "displayorder" => 0,
                    "title" => '规格',
                );
                pdo_insert("ewei_shop_goods_spec", $spc);
                $spec_id = pdo_insertid();

                //规格大概表
                if ($spec_id) {
                    foreach ($spec as $key => $vl) {
                        $spc_item = array(
                            "uniacid" => 2,
                            "specid" => $spec_id,
                            "displayorder" => $key,
                            "title" => $vl['spec'],
                            "show" => 1,
                            "thumb" => '',
                            "virtual" => 0
                        );
                        pdo_insert("ewei_shop_goods_spec_item", $spc_item);
                        $itemid = pdo_insertid();
                        //规格项详情
                        $itemids = array();
                        if ($itemid) {
                            $itemids[] = $itemid;
                            $spc_item_option = array(
                                "uniacid" => 2,
                                "title" => $vl['spec'],
                                "productprice" => $vl['productprice'],
                                "costprice" => $vl['costprice'],
                                "marketprice" => $vl['marketprice'],//销售价
                                "presellprice" => 0,
                                "stock" => $vl['stock'],
                                "weight" => $vl['weight'],
                                "goodssn" => $vl['goodssn'],
                                "productsn" => $vl['productsn'],
                                "goodsid" => $id,
                                "specs" => $itemid,
                                'virtual' => 0,
                            );
                            $totalstocks += $spc_item_option['stock'];
                            $spc_item_option_id = pdo_insert("ewei_shop_goods_option", $spc_item_option);
                            $spc_item_option_ids[] = $spc_item_option_id;
                        }
                        //修改库存为总库存+杂七杂八的参数很恼火想死
                        $is_discounts = array('type' => 1, 'default' => array());
                        $commission = array('type' => 0, 'default' => array());
                        foreach ($spc_item_option_ids as $oid) {
                            $is_discounts['default'] = ['option' . $oid];
                            $commission['default']['option' . $oid] = array();
                        }
                        $is_discounts_json = json_encode($is_discounts);
                        $commission_json = json_encode($commission);
                        pdo_update('ewei_shop_goods', array('discounts' => '{"type":"0","default":"","default_pay":""}', 'total' => $totalstocks, 'isdiscount_discounts' => $is_discounts_json, 'commission' => $commission_json), array('id' => $id));
                    }
                    pdo_update("ewei_shop_goods_spec", array("content" => serialize($itemids)), array("id" => $spec_id));
                }
            }
            show_json(1, '加入商品成功');
        }

        //插入商品表 ewei_shop_goods
        pdo_insert('ewei_shop_goods', $data);
        $id = pdo_insertid();
        if (empty($id)) {
            show_json(0, '失败');
        }
        show_json(1, '加入商品成功');
    }
    //----------------------------------------------

    /**
     * 商品单位获取+商品名称
     * @param $goodsname
     * @return array|string[]
     */
    private function get_unit_goodsname($goodsname)
    {
        if (!empty($goodsname)) {
            //商品名称
            $preg_name = "/\[([^\]]*)\]/i";
            preg_match($preg_name, $goodsname, $arrMatches);
            $name = substr_replace($goodsname, "", strpos($goodsname, $arrMatches[0]), strlen($arrMatches[0]));

            //单位
            $preg_unit = "/(罐|块|瓶|盒|件|卷|套|片|箱|张|支|包|把|个|双|扎)/i";
            preg_match($preg_unit, $arrMatches[1], $unit);

            $res = array(
                'goodsname' => $name,
                'unit' => empty($unit[0]) ? '件' : $unit[0],
            );
            return $res;
        }
        return [ 'goodsname' => '', 'unit' => ''];
    }

    /**
     *增加图片前缀
     * @param $arr
     * @return mixed
     */
    private function array_images($arr) {
        $arr=explode(';',$arr);
        foreach ($arr as &$a) {
            $a = save_media($a);
        }
        unset($a);
        return $arr;
    }

    /**
     * 去掉详情字符的方法
     * @param array $detail
     * @return string|string[]
     */
    private function _goods_detail($detail,$detail_txt){
        $detail=explode(';',$detail);

        $html='<div style="margin: 0px auto; padding: 40px 0px 0px; text-align: center; max-width: 1000px; color: rgb(77, 77, 77); font-family: &quot;Microsoft YaHei&quot;, tahoma, arial, &quot;Hiragino Sans GB&quot;, 宋体, sans-serif; white-space: normal; background-color: rgb(255, 255, 255);">';
        $html.=$detail_txt;
        if (!empty($detail)){
            foreach ($detail as $list){
                $html.="<img src=\"$list\"/>";
            }
        }
        $html.='</div><p><br/></p>';
        return $html;
    }
}

?>
