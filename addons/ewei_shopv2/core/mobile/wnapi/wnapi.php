<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Wnapi_EweiShopV2Page extends MobilePage
{
    //    测试
//    public $app_key = "1e55002198db11ea865a7cd30adfe8ac";
//    public $app_parenter = "6000933_6000772";
//  正式
    public $app_key = "dc2455bee0dd4cadb6f2b89603af144d";
    public $app_parenter = "47663_13267";

    public function __construct()
    {
        global $_GPC;
        $hash = 'f4a3ae4bab11c3345b8d40e444614e5a';
        if ($_GPC['hash'] !== $hash || !isset($_GPC['hash'])) {
            exit();
        }
    }
    //-------------------------------------------维妮供应链 begin----------------------------------------------
    //更新维妮分类表
    public function main()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');    //内存无限

        $interfacename = 'GoodsCategorySynchro';
        //leve1 一级分类
        $content = array(
            'Level' => '1',
            'ParentId' => '0'
        );
        $contentjson = json_encode($content);
        $res = $this->_common_api($interfacename, $contentjson);
        if (!empty($res)) {
            //插入数据表
            $this->_insert_cat($res);
            foreach ($res as $key => $cat) {
                //leve2  二级分类
                $content = array(
                    'Level' => '2',
                    'ParentId' => $cat['Id']
                );
                sleep(1);
                $cat['child'] = $this->_common_api($interfacename, json_encode($content));
                if (!empty($cat['child'])) {
                    //插入数据表
                    $this->_insert_cat($cat['child']);
                    foreach ($cat['child'] as $key2 => $child_leve2) {
                        //leve3  三级分类
                        $content = array(
                            'Level' => '3',
                            'ParentId' => $child_leve2['Id']
                        );
                        sleep(1);
                        $child_leve2['child'] = $this->_common_api($interfacename, json_encode($content));
                        if (!empty($child_leve2['child'])) {
                            //插入数据表
                            $this->_insert_cat($child_leve2['child']);
                        }
                    }
                }
            }
            exit();
        } else {
            exit();
        }
    }
    private function _insert_cat($cat_list)
    {
        foreach ($cat_list as $item) {
            $data = array();
            $data['ParentId'] = $item['ParentId'];
            $data['Name'] = $item['Name'];
            $data['Level'] = $item['Level'];
            $data['wn_id'] = $item['Id'];
            $cat = pdo_fetch("select * from " . tablename('ewei_shop_category_wn') . " WHERE wn_id = :id AND Level = :level", array(':id' => $item['Id'], ':level' => $item['Level']));
            if ($cat == false) {
                $is_up = pdo_insert('ewei_shop_category_wn', $data);
                $str = '新增:--' . $item['Id'] . '--' . $item['Level'] . '分类--';
                $str .= empty($is_up) ? '失败' : '成功';
                $str .= '时间:' . $this->getMsecToMescdate();
                file_put_contents('sitet.txt', var_export($str, true) . PHP_EOL, FILE_APPEND);
            }
        }
    }

    //更新维妮商品
    public function wn_goods_list(){
        set_time_limit(0);
        ini_set('memory_limit', '-1');    //内存无限
        $this->_wn_cron_goodsList(1);
    }
    //获取商品列表信息
    private function _wn_cron_goodsList($page)
    {
        /*查询全部商品*/
        $interfacename = 'SkuListSynchro';
        $content = array(
            'PageNo' => $page,
            'PageNum' => 100,
        );
        $contentjson = json_encode($content);
        $result = $this->_common_api($interfacename, $contentjson);
        if (!empty($result)){
            $this->_insert_goods($result);
            $count=ceil($result['TotalCount']/100);
            if ($page+1>$count+1){
                file_put_contents('sitet.txt',var_export('结束',true).PHP_EOL,FILE_APPEND);
                $this->wn_goods_num();//更新产品库存+商品上下架
                exit('success');
            }
            for ($i=$page;$i<=$count;$i++){
                file_put_contents('sitet.txt',var_export($page,true).PHP_EOL,FILE_APPEND);
                sleep(1);
                $this->_wn_cron_goodsList($page+1);
            }
        }else{
            file_put_contents('sitet.txt',var_export('空了',true).PHP_EOL,FILE_APPEND);
            exit('error');
        }
        return $result;
    }
    //维妮商品插入
    private function _insert_goods($goods_list)
    {
        //组装商品
        foreach ($goods_list['SkuList'] as $skulist) {
            $result=array();
            $result['SkuNo'] = $skulist['SkuNo'];
            $result['SkuName'] = $skulist['SkuName'];
            $result['BarCode'] = $skulist['BarCode'];
            $result['SettlePrice'] = $skulist['SettlePrice'];
            $result['RetailPrice'] = $skulist['RetailPrice'];
            $result['Brand'] = $skulist['Brand'];
            $result['Country'] = $skulist['Country'];
            $result['Category'] = $skulist['Category'];
            $result['TwoCategory'] = $skulist['TwoCategory'];
            $result['ThreeCategory'] = $skulist['ThreeCategory'];
            $result['Details'] = $skulist['Details'];
            $result['Rate'] = $skulist['Rate'];
            $result['DeliveryCode'] = $skulist['DeliveryCode'];
            $result['SaleType'] = $skulist['SaleType'];
            $result['Weight'] = $skulist['Weight'];
            $result['displayImgUrls'] = $skulist['displayImgUrls'];
            $result['detailImgUrls'] = $skulist['detailImgUrls'];
            $result['DeliveryCity'] = $skulist['DeliveryCity'];
            $result['goodsNo'] = $skulist['goodsNo'];
            $result['Spec'] = $skulist['Spec'];
            $result['LimitNum'] = $skulist['LimitNum'];
            $result['ValidDay'] = strtotime($skulist['ValidDay']);
            $result['IsLimitPrice'] = $skulist['IsLimitPrice'];
            $result['IfInvoice'] = $skulist['IfInvoice'];
            $result['Status'] = $skulist['Status']=='上架' ? 1 : 0;
            $result['ShelfTime'] = strtotime($skulist['ShelfTime']);
            $result['UpdateTime'] = strtotime($skulist['UpdateTime']);

            $goods = pdo_fetch("select * from " . tablename('ewei_shop_goods_wn') . " WHERE SkuNo = :skuno", array(':skuno' => $skulist['SkuNo']));
            if (empty($goods)) {
                $is_up=pdo_insert('ewei_shop_goods_wn', $result);
                $this->_insert_file('api.goods.status.txt',$is_up,$skulist['SkuNo'],'增加');
            }else{
                if ($goods['SettlePrice']!=$skulist['SettlePrice'] || $goods['Rate']!=$skulist['Rate'] || $goods['Status']!=$result['Status'] || $goods['LimitNum']!=$skulist['LimitNum']){
                    $is_up=pdo_update('ewei_shop_goods_wn', array('Status'=>$result['Status'],'SettlePrice'=>$skulist['SettlePrice'],'Rate'=>$skulist['Rate']),array('SkuNo'=>$skulist['SkuNo']));
                    $this->_insert_file('api.goods.status.txt',$is_up,$skulist['SkuNo'],'修改');
                }
            }
        }
    }

    //维妮库存更新+商品上下架
    public function wn_goods_num(){
        /*查询全部商品*/
        $goods = pdo_fetchall("select * from " . tablename('ewei_shop_goods_wn'));
        $count = ceil(count($goods)/2000);
        for ($i=1;$i<=$count;$i++){
            //定义页码
            $pindex = $i;
            //定义记录数
            $psize = 2000;

            $goodsList = pdo_fetchall('SELECT id,SkuNo,Quantity FROM ' . tablename('ewei_shop_goods_wn') . " ORDER BY id DESC LIMIT " . ($pindex -1) * $psize . ", {$psize}");
            $this->_goods_num($goodsList);
        }
        foreach ($goods as $list){
            $status= $list['Quantity']>0 ? 1 : 0;
            $is_up=pdo_update('ewei_shop_goods_wn', array('Status'=>$status),array('SkuNo'=>$list['SkuNo']));
            $this->_insert_file('Api.goodsnum.txt',$is_up,$list['SkuNo'],'商品上下架');
        }
        exit();
    }
    private function _goods_num($GoodsAttr){
        foreach ($GoodsAttr as $k=>$skuno){
            $id[]=$skuno['SkuNo'];
        }
        $interfacename = 'StockSynchro';
        $content = array(
            'StockReqs' => $id,
        );
        $contentjson = json_encode($content);
        $result = $this->_common_api($interfacename, $contentjson);
        if (!empty($result)){
            foreach ($result as $list){
                $is_up=pdo_update('ewei_shop_goods_wn', array('Quantity'=>$list['Quantity']),array('SkuNo'=>$list['SkuNo']));
                $this->_insert_file('Api.goodsnum.txt',$is_up,$list['SkuNo'],'修改库存');
            }
        }
    }
    //-------------------------------------------维妮供应链 end----------------------------------------------




    //-------------------------------------------微擎  begin----------------------------------------------
    //更新主表的分类
    public function up_main_cat()
    {
        $wn_cat_list = pdo_fetchall("select * from " . tablename('ewei_shop_category_wn') . " WHERE ParentId=:parentid AND Level = :level", array(':parentid'=>0,':level' => 1));
        if (!empty($wn_cat_list)) {
            //leve1 一级分类
            foreach ($wn_cat_list as $key => $cat) {
                $leve1_id = $this->_main_insert_cat($cat['wn_id'], $cat['Name'], $cat['ParentId'], 1);
                //拿到插入后的id
                $res[$key]['pid'] = $leve1_id;

                //leve2  二级分类
                $cat['child'] = pdo_fetchall("select * from " . tablename('ewei_shop_category_wn') . " WHERE ParentId=:parentid AND Level = :level", array(':parentid'=>$cat['wn_id'],':level' => 2));
                if (!empty($cat['child'])) {
                    foreach ($cat['child'] as $key2 => $child_leve2) {
                        //插入数据
                        $leve2_id = $this->_main_insert_cat($child_leve2['wn_id'], $child_leve2['Name'], $res[$key]['pid'], 2);
                        //拿到插入后的id
                        $res[$key]['child'][$key2]['pid'] = $leve2_id;//增加一个id 分类主键

                        //leve3  三级分类
                        $child_leve2['child'] = pdo_fetchall("select * from " . tablename('ewei_shop_category_wn') . " WHERE ParentId=:parentid AND Level = :level", array(':parentid'=>$child_leve2['wn_id'],':level' => 3));
                        if (!empty($child_leve2['child'])) {
                            foreach ($child_leve2['child'] as $key3 => $child_leve3) {
                                //插入数据
                                $this->_main_insert_cat($child_leve3['wn_id'], $child_leve3['Name'], $res[$key]['child'][$key2]['pid'], 3);
                            }
                        }
                    }
                }
            }
        }
    }
    private function _main_insert_cat($cat_id, $cat_name, $cat_parentid, $level)
    {
        global $_W;
        $cat_list = pdo_fetch("select * from " . tablename('ewei_shop_category') . " WHERE wn_catid = :wn_catid  AND level = :level", array(':wn_catid' => $cat_id, ':level' => $level));
        if (!empty($cat_list)){
            return $cat_list['id'];
        }
        if (!$cat_list) {
            $data['uniacid'] = $_W['uniacid'];
            $data['parentid'] = $cat_parentid;
            $data['level'] = $level;
            $data['wn_catid'] = $cat_id;
            $data['name'] = $cat_name;

            $data['isrecommand'] = 0;
            $data['displayorder'] = 0;
            $data['enabled'] = 1;
            $data['thumb'] = '';
            $data['description'] = '';
            $data['ishome'] = 0;
            $is_up = pdo_insert('ewei_shop_category', $data);
            $id = pdo_insertid();
            if ($is_up) {
                return $id;
            }
        }
        return null;
    }

    //更新商品表
    public function up_man_goods(){
        set_time_limit(0);
        ini_set('memory_limit', '-1');    //内存无限
//        $result=array(
//            'act_time'=>array(//促销起止时间
//                'start' => '1970-01-01 08:00',
//                'end' => '1970-01-01 08:00',
//            ),
//            'nocommission'=>'',
//            'saletime' => array(//限时卖时间
//                'start' => '2020-08-24 15:58',
//                'end' => '2020-08-31 15:58',
//            ),
//            'createtime' => '',//创建当前商品时间
//            'statustime' => array(
//                'start' => '2020-08-24 15:58',//当前时间
//                'end' => '2020-09-24 15:58',
//            ),
//            'intervalfloor'=>'',
//            'intervalnum1'=>'',
//            'intervalprice1'=>'',
//            'intervalnum2' => '',
//            'intervalprice2' => '',
//            'intervalnum3' => '',
//            'intervalprice3' => '',
//            'buyagain_commission'=>'',
//            'storeids_text' => '',
//            'diyformid' => '',
//            'diysave' => '0',
//            'data_type' => '0',
//            'merchsale'=>'',
//            'isforceverifystore_verifygoods' => '0',
//            'verify_type1' => '0',
//            'verifygoodsnum' => '',
//            'verifygoodstype' => '0',
//            'verifygoodslimittype' => '0',
//            'verifygoodsdays' => '',
//            'verifygoodslimitdate' => '2020-08-24 15:58:00',//当前时间
//            'opencard' => '0',
//            'custom_cell1_url' => '',
//            'prerogative' => '',
//            'card_description' => '',
//            'card_backgroundtype' => '0',
//            'color2' => '',
//            'color' => 'Color010',
//            'custom_cell1' => '0',
//            'custom_cell1_name' => '',
//            'custom_cell1_tips' => '',
//            'card_logoimg' => '',
//            'card_logolocalpath' => '',
//            'card_backgroundimg_localpath' => '',
//            'card_backgroundimg' => '',
//            'card_brand_name' => '',
//            'card_title' => '',
//            'card_totalquantity' => '',
//            'discounts' => array(
//                'type' => '0',
//                'default' => '',
//                'default_pay' => '',
//            ),
//            'tab' => '#tab_option',
//        );
        $goods_list = pdo_fetchall("select * from " . tablename('ewei_shop_goods_wn'). " as gw left join". tablename('ewei_shop_goods')."as g on g.goodssn!=gw.goodsNo");
        foreach ($goods_list as $list){
            //分类
            $Category=$this->cats($list['Category'],1);
            $TwoCategory=$this->cats($list['TwoCategory'],2);
            $ThreeCategory=$this->cats($list['ThreeCategory'],3);

            //商品图片
            $img_url=$this->array_images($list['displayImgUrls']);
            $thumb=$img_url[0];
            $thumb_url=serialize($img_url);

            //规格组装
            isset($result['goodsNo']) || $result['goodsNo'] = array();
            $data[$list['goodsNo']][]=array(
                'title' => $this->get_unit($list['SkuName'])['goodsname'],//商品名称
                'timestart' => 1598255880,//限卖开始时间
                'timeend' => 1598860680,//限卖结束时间
                'statustimestart' => time(),//上架时间
                'statustimeend' => 1756275682,//下架时间 2025-08-27 14:21:22
                'pcate' => $Category,//一级分类
                'ccate' => '',
                'tcate' => '',
                'pcates' => $Category,//一级分类
                'ccates' => $TwoCategory,//二级分类
                'tcates' => $ThreeCategory,//三级分类
                'diymode' => 0,
                'thumb' => $thumb,//商品图(主图)
                'thumb_url' => $thumb_url,//缩略图地址
                'type'=>1,//商品类型-默认实体商品
                'status'=>$list['Status'],//上架状态 1上架 2下架
                'isverify'=>1,//包邮设置，默认1
                'hasoption'=>0,//是否启用商品多规格
                'uniacid'=>2,//公众号id
                'displayorder'=>'',//排序方式
                'subtitle'=>'',//副标题
                'shorttitle'=>'',//商品短标题 用于快递打印
                'keywords'=>'',//商品关键字,能准确搜到商品
                'thumb_first'=>0,//是否详情显示首图
                'showsales'=>0,//是否显示销量
                'ispresell'=>0,//是否开启商品预售设置
                'presellover'=>0,//预售商品状态
                'presellovertime'=>0,//商品转为正常销售时间
                'presellprice'=>0,//预售价格0
                'presellstart'=>'',//预售开始时间是否点击
                'presellend'=>'',//预售结束时间是否点击
                'preselltimestart'=>'',//预售开始时间（当前时间）
                'preselltimeend'=>'',//预售结束时间（当前时间）
                'presellsendtype'=>0,//是否选中发货时间
                'presellsendstatrttime'=>'',//发货时间（当前时间）
                'presellsendtime'=>'',//购买几天后发货
                'labelname'=>'',//标签名称（如正品保证，七天无理由退货）
                'isrecommand'=>'',//商品属性(如推荐，新品)
                'ishot'=>'',//是否选中热卖
                'isnew'=>'',//是否选中新品
                'isdiscount'=>'',//是否选中促销
                'isdiscount_title'=>'',//促销标题
                'isdiscount_time_start'=>0,//act_time['start']
                'isdiscount_time'=>0,//act_time['start']
                'issendfree'=>$list['SaleType'],//是否包邮
                'isnodiscount'=>'',//不参与折扣
                'istime'=>'',//是否选中限时卖
                'description' => substr($list['Details'],'0',400),//分享描述
                'goodssn' => $list['goodsNo'],//编码
                'unit' => $this->get_unit($list['Name'])['unit'],//单位, 如: 个/件/包
                'total' => $list['Quantity'],//库存
                'showtotal' => '',//显示库存
                'totalcnf' => 1,//付款减库存
                'unite_total' => 0,
                'marketprice' => $list['SettlePrice']*0.05+$list['SettlePrice'],//现价（商品销售价）
                'weight' => $list['Weight'],//重量（克）
                'costprice' =>$list['SettlePrice'],//成本价
                'productprice' => $list['RetailPrice'],//原价
                'minprice'=>$list['SettlePrice'],
                'maxprice'=>$list['SettlePrice'],
                'productsn' => '',//条码
                'credit' => '',//积分赠送
                'maxbuy' => empty($list['LimitNum']) ? 0: $list['LimitNum'],//单次最多购买
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
                'virtual'=>0,//type==3?intval($_GPC['virtual']):0,
                'ednum' => '',//如果设置0或空，则不支持满件包邮
                'edareas' => '',//不参与单品包邮地区
                'edareas_code' => '',//添加不参加满包邮的地区
                'edmoney' => '',//单品满额包邮 单位元
                'invoice' => '',//发票
                'repair' => '',//保修
                'seven' => '',//7天无理由退换
                'money' => '',//余额返现
                'province' => $list['DeliveryCity'],//商品所在地
                'city' => '',//市
                'quality' => '',//正品保证
                'sharebtn' => '',//
                'autoreceive' => '',//确认收货时间
                'cannotrefund' => 1,//是否支持退换货
                'refund' => 0,//退款
                'returngoods' => 0,//退货退款
                'exchange' => 0,//换货
                'buyagain' => '',//重复购买折扣
                'buyagain_islong' =>0,//购买一次后,以后都使用这个折扣 是否持续使用
                'buyagain_condition' => 0,//重复购买使用条件,是付款后还是完成后 , 默认是付款后
                'buyagain_sale' => 0,//重复购买时,是否与其他优惠共享!其他优惠享受后,在使用这个折扣
                'diypage' => '',//
                'cashier' => '',//支持收银台
                'video' => '',//首图视频
                'nosearch'=>0,
                'isstatustime'=>0,
                'verifytype'=>'',
                'storeids'=>'',
                'cash'=>'',
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
                'cates' =>$Category.','.$TwoCategory.','.$ThreeCategory,//分类
                'content' => $this->_goods_detail($list['detailImgUrls'],$list['Details']),//商品详情
                'buycontent' => '',
                'dispatchtype' => '0',
                'dispatchid' => '0',
                'dispatchprice' => '',

                'spec'=>$list['Spec'],
            );
        }
        foreach ($data as $k=>$itme){
            $data_result[$itme[0]['goodssn']]=$itme[0];
            if (count($itme) > 1) {
                $status = array();
                $minprice=array();
                $maxprice=array();
                foreach ($itme as $key => $vl) {
                    $minprice[] = $vl['minprice'];//最小价格
                    $maxprice[] = $vl['maxprice'];//最大价格
                    $status[]=$vl['status'];//上下架状态
                }
                $data_result[$itme[0]['goodssn']] = $itme[0];
                $data_result[$itme[0]['goodssn']]['status'] = max($status);
                $data_result[$itme[0]['goodssn']]['minprice'] = min($minprice);
                $data_result[$itme[0]['goodssn']]['maxprice'] = max($maxprice);
                $data_result[$itme[0]['goodssn']]['hasoption']=1;
            }
            unset($data_result[$itme['goodssn']]['spec']);
            //插入商品表 ewei_shop_goods
            pdo_insert('ewei_shop_goods', $data_result[$itme[0]['goodssn']]);
            $id = pdo_insertid();

            if ($id && $data_result[$itme[0]['goodssn']]['hasoption']) {
                $totalstocks=0;//总库存
                //规格表
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
                    foreach ($itme as $key => $vl) {
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
                        $itemids=array();
                        if ($itemid){
                            $itemids[]=$itemid;
                            $spc_item_option=array(
                                "uniacid" => 2,
                                "title" => $vl['spec'],
                                "productprice" => $vl['productprice'],
                                "costprice" => $vl['costprice'],
                                "marketprice" => $vl['marketprice'],//销售价
                                "presellprice" => $vl['presellprice'],
                                "stock" => $vl['total'],
                                "weight" => $vl['weight'],
                                "goodssn" => $vl['goodssn'],
                                "productsn" => $vl['productsn'],
                                "goodsid" => $id,
                                "specs" => $itemid,
                                'virtual' => 0,
                            );
                            $totalstocks+=$spc_item_option['stock'];
                            $spc_item_option_id=pdo_insert("ewei_shop_goods_option", $spc_item_option);
                            $spc_item_option_ids[]=$spc_item_option_id;
                        }
                        //修改库存为总库存+杂七杂八的参数很恼火想死
                        $is_discounts=array('type'=>1,'default'=>array());
                        $commission=array('type'=>0,'default'=>array());
                        foreach ($spc_item_option_ids as $oid){
                            $is_discounts['default']=['option'.$oid];
                            $commission['default']['option'.$oid]=array();
                        }
                        $is_discounts_json=json_encode($is_discounts);
                        $commission_json=json_encode($commission);
                        pdo_update('ewei_shop_goods',array('discounts'=>'{"type":"0","default":"","default_pay":""}','total'=>$totalstocks,'isdiscount_discounts'=>$is_discounts_json,'commission'=>$commission_json), array('id' => $id));
                    }
                    pdo_update("ewei_shop_goods_spec", array("content" => serialize($itemids)), array("id" => $spec_id));
                }
            }
        }
    }
    //-------------------------------------------微擎  end----------------------------------------------


    /**
     * 商品单位获取+商品名称
     * @param $goodsname
     * @return array|string[]
     */
    private function get_unit($goodsname)
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
     * 查询分类
     * @param $cat_name
     * @param $level
     * @return |null
     */
    private function cats($cat_name,$level){
        $id=pdo_fetch("select id from " . tablename('ewei_shop_category'). " WHERE name=:name AND level=:level", array(':name'=>$cat_name,':level'=>$level));
        if ($id){
            return $id['id'];
        }
        return null;
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
     * 指定插入文件和内容
     * @param $file //文件名
     * @param $status //状态
     * @param $id //id
     */
    private function _insert_file($file,$status,$id,$content){
        $status=empty($status) ? '失败' : '成功';
        $str=$id.':'.$content.$status.'--'.date('Y-m-d H:i:s',time());
        file_put_contents($file,$str.PHP_EOL,FILE_APPEND);
    }

    /**
     * 毫秒转日期
     */
    private function getMsecToMescdate()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $msectime = $msectime * 0.001;
        if (strstr($msectime, '.')) {
            sprintf("%01.3f", $msectime);
            list($usec, $sec) = explode(".", $msectime);
            $sec = str_pad($sec, 3, "0", STR_PAD_RIGHT);
        } else {
            $usec = $msectime;
            $sec = "000";
        }
        $date = date("Y-m-d H:i:s.x", $usec);
        return $mescdate = str_replace('x', $sec, $date);
    }

    /**
     * 公共api获取内容
     * @param $interfacename
     * @param $contentjson
     * @return array|bool|float|int|mixed|stdClass|string|null
     */
    private function _common_api($interfacename, $contentjson)
    {
        //vip.nysochina.com
        //121.41.84.251:9090
        $url = "vip.nysochina.com/api/" . $interfacename . '.shtml';
        $strTime = date('Y-m-d', time());
        //组装token
        $token = md5($this->app_key . $strTime . $interfacename . $contentjson);

        $params = array(
            'interfacename' => $interfacename,
            'parenter' => $this->app_parenter,
            'token' => $token,
            'content' => $contentjson
        );

        $content = $this->juhecurl($url, $params, 1);

        $result = json_decode($content, true);
        if (!isset($result['success'])) {
            return $result;
        }

        if ($result) {
            if ($result['success'] == true) {
                return $result['result'];
            }
        }
        return null;
    }

    /**
     * 请求接口返回内容
     * @param string $url [请求的URL地址]
     * @param string $params [请求的参数]
     * @param int $ipost [是否采用POST形式]
     * @return  string
     */
    private function juhecurl($url, $params = false, $ispost = 0)
    {
        $httpInfo = array();
        $headers = array(
            "Content-type:application/json;charset=utf-8",
            "Accept:application/json",
            'interfacename:' . $params['interfacename'],
            'parenter:' . $this->app_parenter,
            'token:' . $params['token'],
        );
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['content']);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }
}


?>