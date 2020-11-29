<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;

class Wnapi_EweiShopV2Page extends MobilePage
{
    //    测试
//    public $app_key = "1e55002198db11ea865a7cd30adfe8ac";
//    public $app_parenter = "6000933_6000772";
//  正式
    public $app_key = "dc2455bee0dd4cadb6f2b89603af144d";
    public $app_parenter = "47663_13267";

    //-------------------------------------------上架商城商品 begin----------------------------------------------
    //分类（）
    public function wq_goodscat(){
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
            $this->_insert_wq_goodscat($res,0);
            $wq_goodscat=pdo_fetchall('select * from '.tablename('ewei_shop_category').' where  parentid=:parentid and (wn_catid <> \'\' or wn_catid <> 0) and level=1',array(':parentid'=>0));
            foreach ($wq_goodscat as $key => $cat) {
                //leve2  二级分类
                $content = array(
                    'Level' => '2',
                    'ParentId' => $cat['wn_catid']
                );
                sleep(1);
                $cat['child'] = $this->_common_api($interfacename, json_encode($content));
                if (!empty($cat['child'])) {
                    //插入数据表
                    $this->_insert_wq_goodscat($cat['child'],$cat['id']);
                    $wq_goodscat2=pdo_fetchall('select * from '.tablename('ewei_shop_category').' where  parentid=:parentid and (wn_catid <> \'\' or wn_catid <> 0) and level=2',array(':parentid'=>$cat['id']));
                    foreach ($wq_goodscat2 as $key2 => $child_leve2) {
                        //leve3  三级分类
                        $content = array(
                            'Level' => '3',
                            'ParentId' => $child_leve2['wn_catid']
                        );
                        sleep(1);
                        $child_leve2['child'] = $this->_common_api($interfacename, json_encode($content));
                        if (!empty($child_leve2['child'])) {
                            //插入数据表
                            $this->_insert_wq_goodscat($child_leve2['child'],$child_leve2['id']);
                        }else{
                            echo $child_leve2['wn_catid'].'没有3级<br>';
                        }
                    }
                }else{
                    echo $cat['wn_catid'].'没有二级<br>';
                }
            }
            exit('结束');
        } else {
            exit('接口一级分类有问题');
        }
    }
    private function _insert_wq_goodscat($cat_list,$parentid){
        foreach ($cat_list as $item) {
            $data = array();
            $data['uniacid'] = 2;
            $data['name'] = $item['Name'];
            $data['level'] = $item['Level'];
            $data['wn_catid'] = $item['Id'];
            $data['parentid'] = $parentid;
            $cat = pdo_fetch("select * from " . tablename('ewei_shop_category') . " WHERE wn_catid = :id AND level = :level", array(':id' => $item['Id'], ':level' => $item['Level']));
            if (empty($cat)) {
                $is_up = pdo_insert('ewei_shop_category', $data);
                $id = pdo_insertid();
                $str = '新增分类';
                $this->_insert_file('cats.txt',$is_up,$id,$str);
            }
        }
    }


    //商品
    public function wq_goods(){
        //分词库
        ini_set('memory_limit', '-1');
        Jieba::init();
        Finalseg::init();
        JiebaAnalyse::init();
        //维妮添加商品
        $wq_wngoods=pdo_fetchall('select * from '.tablename('ewei_shop_goods_wn'));
        foreach ($wq_wngoods as $wngood){
            $isup=pdo_fetch("select * from" . tablename('ewei_shop_goods') . ' where supplychain_type=1 and supplychain_goods_id=' . $wngood['id'] . '');
            if (empty($isup)){
                $this->_insert_wn($wngood);
            }else{
                continue;
            }
        }
        //澳安
        $wq_wngoodscat=pdo_fetchall('select name from '.tablename('ewei_shop_category').' where  parentid=:parentid and (wn_catid <> \'\' or wn_catid <> 0) and level=1',array(':parentid'=>0));
        $top_k=20;
        $aoan_cat_data=[];
        foreach ($wq_wngoodscat as $item){
            $tags = JiebaAnalyse::extractTags($item['name'], $top_k);
            $case='';
            foreach ($tags as $k=>$t){
                $case.="(CASE WHEN `name` LIKE '%$k%' THEN 1 else 0 END)+";
            }
            $case=substr($case, 0, -1);
            $wq_aoangoodscat=pdo_fetchall('select aoan_id,name,('.$case.') as num from '.tablename('ewei_shop_category_aoan').' where 1=1 and parentid=:parentid  and ('.$case.')>=1',array(':parentid'=>0));
            if (!empty($wq_aoangoodscat)){
                $aoan_cat_data[]=array_column($wq_aoangoodscat, 'aoan_id');
            }
        }
        $cat_ids='';//与维妮相似的分类ID 字符串
        foreach ($aoan_cat_data as $cat_data){
            foreach($cat_data as  $cat_id){
                $cat_ids.='\''.$cat_id.'\',';
            }
        }
        if (!empty($cat_ids)){
            $cat_ids=substr($cat_ids, 0, -1);
            //添加澳安的商品到数据库
            $wq_aoangoods=pdo_fetchall('select * from '.tablename('ewei_shop_goods_aoan').' where pCatId in('.$cat_ids.')');
            if (!empty($wq_aoangoods)){
                foreach ($wq_aoangoods as $wq_aoangood){
                    $this->_insert_aoan($wq_aoangood);
                }
            }
        }
    }

    //商品插入数据库
    private function _insert_aoan($goods){
        //---------准备ims_ewei_shop_goods数据
        //商品图片
        $img_url = $this->array_images($goods['thumbnail']);
        $thumb_url = serialize($img_url);
        $data = array(
            'title' => $goods['name'],//商品名称
            'timestart' => 1598255880,//限卖开始时间
            'timeend' => 1598860680,//限卖结束时间
            'statustimestart' => time(),//上架时间
            'statustimeend' => 1756275682,//下架时间 2025-08-27 14:21:22
            'pcate' => $goods['PcatId'],//一级分类
            'ccate' => '',
            'tcate' => '',
            'pcates' => $goods['PcatId'],//一级分类
            'ccates' => 0,//二级分类
            'tcates' => 0,//三级分类
            'diymode' => 0,
            'thumb' => $goods['big'],//商品图(主图)
            'thumb_url' => $thumb_url,//缩略图地址
            'type' => 1,//商品类型-默认实体商品
            'status' => 1,//上架状态 1上架 2下架
            'isverify' => 1,//包邮设置，默认1
            'hasoption' => 0,//是否启用商品多规格
            'uniacid' => 2,//公众号id
            'displayorder' => '',//排序方式
            'subtitle' => '',//副标题
            'shorttitle' => '',//商品短标题 用于快递打印
            'keywords' => '',//商品关键字,能准确搜到商品
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
            'isrecommand' => '',//推荐
            'ishot' => '',//热卖
            'isnew' => '',//新品
            'isdiscount' => '',//是否选中促销
            'isdiscount_title' => '',//促销标题
            'isdiscount_time_start' => 0,//act_time['start']
            'isdiscount_time' => 0,//act_time['start']
            'issendfree' => $goods['isFreepost'],//包邮
            'isnodiscount' => '',//不参与折扣
            'istime' => '',//是否选中限时卖
            'description' => '',//分享描述
            'goodssn' => $goods['goodsId'],//编码
            'unit' => '件',//单位, 如: 个/件/包
            'total' => $goods['enableStore'],//库存
            'showtotal' => '',//显示库存
            'totalcnf' => 1,//付款减库存
            'unite_total' => 0,
            'marketprice' => $goods['price'] * 0.05 + $goods['price'],//现价（商品销售价）
            'weight' => '',//重量（克）
            'costprice' => $goods['price'],//成本价
            'productprice' => $goods['mktprice'],//原价
            'minprice' => $goods['mktprice'],
            'maxprice' => $goods['mktprice'],
            'productsn' => $goods['sn'],//条码
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
            'province' => $goods['countryName'],//商品所在地
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
            'cates' => $goods['PcatId'],//分类
            'content' => $goods['Details'],//商品详情
            'buycontent' => '',
            'dispatchtype' => '0',
            'dispatchid' => '0',
            'dispatchprice' => '',
            'supplychain_type'=>2,
            'supplychain_goods_id'=>$goods['id'],
            'is_synchronize'=>1,
        );
        //---------准备ims_ewei_shop_goods数据 end

        //准备规格项ims_ewei_shop_goods_option数据
        $goods_spec = pdo_fetchall("select * from" . tablename('ewei_shop_goods_aoan_spec') . ' where goodsId=\'' . $goods['goodsId'] . '\'');
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
                echo $id.'添加澳安商品-'.$data['name'].'<br>';
                $this->_insert_file('insert_aoangoods.txt',$id,$id,'添加澳安商品-'.$data['name']);
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
                echo $spec_id.'--添加澳安商品-'.$spec_id.'<br>';
                $this->_insert_file('insert_aoangoods.txt',$spec_id,$spec_id,'--ewei_shop_goods_spec添加澳安商品多规格-'.$spec_id);
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
                        $this->_insert_file('insert_aoangoods.txt',$spec_id,$spec_id,'--ewei_shop_goods_spec_item添加澳安商品多规格-'.$itemid);
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
                            echo $spec_id.'--添加澳安商品详情-'.$spc_item_option['productName'].'<br>';
                            $this->_insert_file('insert_aoangoods.txt',$spec_id,$spec_id,'--ewei_shop_goods_option添加澳安商品多规格详情-'.$spc_item_option_id);
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
        }
    }

    private function _insert_wn($supplychain_goods)
    {
        //---------准备ims_ewei_shop_goods数据
        //商品图片
        $img_url = $this->array_images($supplychain_goods['displayImgUrls']);
        $thumb = $img_url[0];
        $thumb_url = serialize($img_url);

        $data = array(
            'title' => $this->get_unit_goodsname($supplychain_goods['SkuName'])['goodsname'],//商品名称
            'timestart' => 1598255880,//限卖开始时间
            'timeend' => 1598860680,//限卖结束时间
            'statustimestart' => time(),//上架时间
            'statustimeend' => 1756275682,//下架时间 2025-08-27 14:21:22
            'pcate' => $this->cats($supplychain_goods['Category'],1),//一级分类
            'ccate' => '',
            'tcate' => '',
            'pcates' => $this->cats($supplychain_goods['Category'],1),//一级分类
            'ccates' => $this->cats($supplychain_goods['TwoCategory'],2),//二级分类
            'tcates' => $this->cats($supplychain_goods['ThreeCategory'],3),//三级分类
            'diymode' => 0,
            'thumb' => $thumb,//商品图(主图)
            'thumb_url' => $thumb_url,//缩略图地址
            'type' => 1,//商品类型-默认实体商品
            'status' => $supplychain_goods['Status'],//上架状态 1上架 2下架
            'isverify' => 1,//包邮设置，默认1
            'hasoption' => 0,//是否启用商品多规格
            'uniacid' => 2,//公众号id
            'displayorder' => '',//排序方式
            'subtitle' => '',//副标题
            'shorttitle' => '',//商品短标题 用于快递打印
//            'keywords' => empty($_GPC['keywords']) ? '' : $_GPC['keywords'],//商品关键字,能准确搜到商品
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
            'isrecommand' => '',//推荐
            'ishot' => '',//热卖
            'isnew' => '',//新品
            'isdiscount' => '',//是否选中促销
            'isdiscount_title' => '',//促销标题
            'isdiscount_time_start' => 0,//act_time['start']
            'isdiscount_time' => 0,//act_time['start']
            'issendfree' => $supplychain_goods['SaleType'],//包邮
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
            'cates' => $this->cats($supplychain_goods['Category'],1).','.$this->cats($supplychain_goods['TwoCategory'],2).','.$this->cats($supplychain_goods['ThreeCategory'],3),//分类
            'content' => $this->_goods_detail($supplychain_goods['detailImgUrls'], $supplychain_goods['Details']),//商品详情
            'buycontent' => '',
            'dispatchtype' => '0',
            'dispatchid' => '0',
            'dispatchprice' => '',
            'supplychain_type'=>1,
            'supplychain_goods_id'=>$supplychain_goods['id'],
            'is_synchronize'=>1,
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
            $isup=pdo_fetch("select * from" . tablename('ewei_shop_goods') . ' where supplychain_type=1 and supplychain_goods_id=' . $supplychain_goods['id'] . '');
            if (empty($isup)){
                pdo_insert('ewei_shop_goods', $data);
            }else{
                pdo_update('ewei_shop_goods',$data,array("id" => $isup['id']));
            }

            $id = pdo_insertid();
            if (empty($id)) {
                echo '商品加入失败';
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
                $isup=pdo_fetch("select * from" . tablename('ewei_shop_goods_spec') . ' where goodsid=' . $id . '');
                if (empty($isup)){
                    pdo_insert('ewei_shop_goods_spec', $spc);
                }else{
                    pdo_update('ewei_shop_goods_spec',$spc,array("id" => $isup['id']));
                }
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
                        $isup=pdo_fetch("select * from" . tablename('ewei_shop_goods_spec_item') . ' where specid=' . $spec_id . '');
                        if (empty($isup)){
                            pdo_insert('ewei_shop_goods_spec_item', $spc_item);
                        }else{
                            pdo_update('ewei_shop_goods_spec_item',$spc_item,array("id" => $isup['id']));
                        }
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
                            $isup=pdo_fetch("select * from" . tablename('ewei_shop_goods_option') . ' where goodsid=' . $id . '');
                            if (empty($isup)){
                                $spc_item_option_id=pdo_insert('ewei_shop_goods_option', $spc_item_option);
                                $spc_item_option_ids[] = $spc_item_option_id;
                            }else{
                                pdo_update('ewei_shop_goods_option',$spc_item_option,array("id" => $isup['id']));
                            }
                        }
                        if (!empty($spc_item_option_ids)){
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
                    }
                    pdo_update("ewei_shop_goods_spec", array("content" => serialize($itemids)), array("id" => $spec_id));
                }
            }
        }else{
            //插入商品表 ewei_shop_goods
            //插入商品表 ewei_shop_goods
            $isup=pdo_fetch("select * from" . tablename('ewei_shop_goods') . ' where supplychain_type=1 and supplychain_goods_id=' . $supplychain_goods['id'] . '');
            if (empty($isup)){
                pdo_insert('ewei_shop_goods', $data);
                $id = pdo_insertid();
                if (empty($id)) {
                    echo '加入失败'.$id;
                }
            }else{
                pdo_update('ewei_shop_goods',$data,array("id" => $isup['id']));
            }
        }
    }

    //-------------------------------------------上架商城商品 end----------------------------------------------

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
                $id = pdo_insertid();
                $str = '新增分类';
                $this->_insert_file('cats.txt',$is_up,$id,$str);
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
                file_put_contents('./wnlog/sitet.txt',var_export('结束',true).PHP_EOL,FILE_APPEND);
                $this->wn_goods_num();//更新产品库存+商品上下架
                exit('success');
            }
            for ($i=$page;$i<=$count;$i++){
                file_put_contents('./wnlog/sitet.txt',var_export($page,true).PHP_EOL,FILE_APPEND);
                sleep(2);
                $this->_wn_cron_goodsList($page+1);
            }
        }else{
            file_put_contents('./wnlog/sitet.txt',var_export('空了',true).PHP_EOL,FILE_APPEND);
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
        file_put_contents("./wnlog/$file",$str.PHP_EOL,FILE_APPEND);
    }

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