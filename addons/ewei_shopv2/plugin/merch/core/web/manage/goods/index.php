<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'merch/core/inc/page_merch.php';

class Index_EweiShopV2Page extends MerchWebPage
{

    function main($goodsfrom = '')
    {

        global $_W, $_GPC;

        if(empty($_W['shopversion'])){
            $goodsfrom = strtolower(trim($_GPC['goodsfrom']));
            if(empty($goodsfrom)){
                header('location: ' . webUrl('goods', array('goodsfrom'=>'sale')));
            }
        }


        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sqlcondition = $groupcondition = '';
        $condition = ' WHERE g.`uniacid` = :uniacid and g.`merchid`=:merchid';
        $params = array(':uniacid' => $_W['uniacid'], ':merchid' => $_W['merchid']);

        $not_add = 0;
        $merch_user = $_W['merch_user'];
        $maxgoods = intval($merch_user['maxgoods']);
        if ($maxgoods > 0) {
            $sql = 'SELECT COUNT(1) FROM ' . tablename('ewei_shop_goods') . ' where uniacid=:uniacid and merchid=:merchid';
            $goodstotal = pdo_fetchcolumn($sql, $params);

            if ($goodstotal >= $maxgoods) {
                $not_add = 1;
            }
        }

        if (!empty($_GPC['keyword'])&&!empty($_GPC['k_type'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $_GPC['k_type']=trim($_GPC['k_type']);
            $sqlcondition = ' left join ' . tablename('ewei_shop_goods_option') . ' op on g.id = op.goodsid';
            $groupcondition = ' group by g.`id`';
            switch ($_GPC['k_type']){
                case '1':
                    $condition .= ' AND (g.`title` LIKE :keyword or g.`productsn` LIKE :keyword or op.`title` LIKE :keyword or op.`goodssn` LIKE :keyword or op.`productsn` LIKE :keyword)';
                    break;
                case '2':
                    $condition .=' AND (g.`id`=:id LIKE :keyword or g.`goodssn` LIKE :keyword or g.`goodssn` LIKE :keyword)';
                    $params[':id'] = $_GPC['keyword'];
                    break;
                case '3':
                    $condition .='AND  productsn=:productsn';
                    $params[':productsn'] = '%' . $_GPC['keyword'] . '%';
                    break;
            }
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';

        }
        if (!empty($_GPC['cats'])){
            $_GPC['cats'] = trim($_GPC['cats']);
            $cats=explode(',',$_GPC['cats']);
            if (is_array($cats)){
                $catsname=array();
                foreach ($cats as $list){
                    $condition .= " AND FIND_IN_SET({$list},cates)<>0 ";
                    $catsname[]=pdo_fetch('select name from '.tablename('ewei_shop_category').' where id=:id ',array(':id'=>$list));
                }
                $num=count($catsname);
                switch ($num){
                    case 1:
                        $catsname=$this->strc($catsname[0]['name']).'/二级/三级';
                        break;
                    case 2:
                        $catsname=$this->strc($catsname[0]['name']).'/'.$this->strc($catsname[1]['name']).'/三级';
                        break;
                    case 3:
                        $catsname=$this->strc($catsname[0]['name']).'/'.$this->strc($catsname[1]['name']).'/'.$this->strc($catsname[2]['name']);
                        break;
                    default:
                        $catsname='province/city/county';
                        break;
                }
            }
        }
        if (!empty($_GPC['goodsbusinesstype'])){
            $goodsbusinesstype=trim($_GPC['goodsbusinesstype']);
            $condition .= " AND goodsbusinesstype=:goodsbusinesstype ";
            $params[':goodsbusinesstype'] = $goodsbusinesstype;
        }
        if (!empty($_GPC['brand'])) {
            $brand = $_GPC['brand'];
            $condition .= " AND brand LIKE :brand ";
            $params[':brand'] = '%' .$brand.'%';
        }

        if (empty($goodsfrom)) {
            $goodsfrom = $_GPC['goodsfrom'];
        }
        if (empty($goodsfrom)) {
            $goodsfrom = 'sale';
        }

        if ($goodsfrom == 'sale') {
            $condition .= ' AND g.`status` = 1  and g.`total`>0 and g.`deleted`=0  AND g.`checked`=0';
            $status = 1;
        } else if ($goodsfrom == 'out') {
            $condition .= ' AND g.`total` <= 0 AND g.`status` <> 0 and g.`deleted`=0  AND g.`checked`=0';
            $status = 1;
        } else if ($goodsfrom == 'stock') {
            $status = 0;
            $condition .= ' AND g.`status` = 0 and g.`deleted`=0 AND g.`checked`=0';
        } else if ($goodsfrom == 'cycle') {
            $status = 0;
            $condition .= ' AND g.`deleted`=1';
        } else if ($goodsfrom == 'check') {
            $status = 0;
            $condition .= ' AND g.`checked`=1 and g.`deleted`=0';
        }


        $sql = 'SELECT COUNT(g.`id`) FROM ' . tablename('ewei_shop_goods') . 'g' . $sqlcondition . $condition . $groupcondition;
        $total = pdo_fetchcolumn($sql, $params);
        $list = array();
        if (!empty($total)) {
            $sql = 'SELECT g.* FROM ' . tablename('ewei_shop_goods') . 'g' . $sqlcondition . $condition . $groupcondition . ' ORDER BY g.`status` DESC, g.`merchdisplayorder` DESC,
                g.`id` DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql, $params);
            foreach ($list as $key => &$value) {
                $url = mobileUrl('goods/detail', array('id' => $value['id']), true);
                $value['qrcode'] = m('qrcode')->createQrcode($url);
            }
            $pager = pagination2($total, $pindex, $psize);
        }

        $categorys = m('shop')->getFullCategory(true);
        $category = array();
        foreach ($categorys as $cate) {
            $category[$cate['id']] = $cate;
        }


        include $this->template('goods');
    }

    function add()
    {
        $this->post();
    }

    function edit()
    {
        $this->post();
    }

    function sale() {
        $this->main('sale');
    }
    function out() {
        $this->main('out');
    }
    function stock() {
        $this->main('stock');
    }
    function cycle() {
        $this->main('cycle');
    }
    function verify() {
        $this->main('verify');
    }
    function check() {
        $this->main('check');
    }

    protected function post()
    {

        require dirname(__FILE__) . "/post.php";
    }

    function strc($name){
        $str= str_replace('/','\\',$name);
        return $str;
    }

    function delete()
    {
        global $_W, $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)) {
            $id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
        }
        $items = pdo_fetchall("SELECT id,title FROM " . tablename('ewei_shop_goods') . " WHERE id in( $id ) AND uniacid=" . $_W['uniacid']);
        foreach ($items as $item) {
            pdo_update('ewei_shop_goods', array('deleted' => 1), array('id' => $item['id']));
            mplog('goods.delete', "删除商品 ID: {$item['id']} 商品名称: {$item['title']} ");
        }

        show_json(1, array('url' => referer()));
    }

    function status()
    {
        global $_W, $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)) {
            $id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
        }
        $items = pdo_fetchall("SELECT id,title FROM " . tablename('ewei_shop_goods') . " WHERE id in( $id ) AND uniacid=" . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_update('ewei_shop_goods', array('status' => intval($_GPC['status'])), array('id' => $item['id']));
            mplog('goods.edit', "修改商品状态<br/>ID: {$item['id']}<br/>商品名称: {$item['title']}<br/>状态: " . ($_GPC['status'] == 1 ? '上架' : '下架'));
        }

        show_json(1, array('url' => referer()));
    }

    function delete1()
    {
        global $_W, $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)) {
            $id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
        }
        $items = pdo_fetchall("SELECT id,title FROM " . tablename('ewei_shop_goods') . " WHERE id in( $id ) AND uniacid=" . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_delete('ewei_shop_goods', array('id' => $item['id']));
            mplog('goods.edit', "从回收站彻底删除商品<br/>ID: {$item['id']}<br/>商品名称: {$item['title']}");
        }
        show_json(1, array('url' => referer()));
    }

    function restore()
    {
        global $_W, $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)) {
            $id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
        }
        $items = pdo_fetchall("SELECT id,title FROM " . tablename('ewei_shop_goods') . " WHERE id in( $id ) AND uniacid=" . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_update('ewei_shop_goods', array('deleted' => 0), array('id' => $item['id']));
            mplog('goods.edit', "从回收站恢复商品<br/>ID: {$item['id']}<br/>商品名称: {$item['title']}");
        }

        show_json(1, array('url' => referer()));
    }

    function property()
    {
        global $_W, $_GPC;
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        if (in_array($type, array('new', 'hot', 'recommand', 'discount', 'time', 'sendfree'))) {

            pdo_update("ewei_shop_goods", array("is" . $type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            if ($type == 'new') {
                $typestr = "新品";
            } else if ($type == 'hot') {
                $typestr = "热卖";
            } else if ($type == 'recommand') {
                $typestr = "推荐";
            } else if ($type == 'discount') {
                $typestr = "促销";
            } else if ($type == 'time') {
                $typestr = "限时卖";
            } else if ($type == 'sendfree') {
                $typestr = "包邮";
            }
            mplog('goods.edit', "修改商品{$typestr}状态   ID: {$id}");
        }
        if (in_array($type, array('status'))) {

            pdo_update("ewei_shop_goods", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            mplog('goods.edit', "修改商品上下架状态   ID: {$id}");
        }
        if (in_array($type, array('type'))) {
            pdo_update("ewei_shop_goods", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            mplog('goods.edit', "修改商品类型   ID: {$id}");
        }
        show_json(1);
    }

    function change()
    {
        global $_W, $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }
        $type = trim($_GPC['type']);
        $value = trim($_GPC['value']);
        if (!in_array($type, array('title', 'marketprice', 'total', 'goodssn', 'productsn', 'displayorder','merchdisplayorder'))) {
            show_json(0, array('message' => '参数错误'));
        }
        $goods = pdo_fetch('select id,hasoption,checked from ' . tablename('ewei_shop_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
        if (empty($goods)) {
            show_json(0, array('message' => '参数错误'));
        }

        $typeBucket = array('title','marketprice','total');
        if(in_array($type,$typeBucket)){
            if (empty($_W['merch_user']['goodschecked'])) {
                $checked =1;
            }else{
                if ($goods['checked'] == 0){
                    $checked = 0;
                }

            }
        }else{
            if ($goods['checked'] == 0){
                $checked = 0;
            }else{
                $checked = 1;
            }
        }
        pdo_update('ewei_shop_goods', array($type => $value,'checked'=>$checked), array('id' => $id));

        if($goods['hasoption'] == 0 && !in_array($type,array('displayorder','total'))){
            if($goods['isdiscount'] ==0 || $goods['isdiscount_time']<time()){
                $sql = "update ".tablename('ewei_shop_goods')." set minprice = marketprice,maxprice = marketprice where id = {$goods['id']} and hasoption=0;";
                pdo_query($sql);
            }else{
              
                $sqlgoods = "SELECT id,title,thumb,marketprice,productprice,minprice,maxprice,isdiscount,isdiscount_time,isdiscount_discounts,sales,total,description,merchsale FROM " . tablename('ewei_shop_goods') . " where id=:id and uniacid=:uniacid limit 1";
                $goodsinfo = pdo_fetch($sqlgoods,array(':id'=>$id,':uniacid'=>$_W['uniacid']));
                $goodsinfo = m('goods')->getOneMinPrice($goodsinfo);
                pdo_update('ewei_shop_goods',array('minprice'=>$goodsinfo['minprice'],'maxprice'=>$goodsinfo['maxprice']),array('id'=>$id,'uniacid'=>$_W['uniacid']));
            }
        }
        show_json(1);
    }

    function tpl()
    {
        global $_GPC, $_W;
        $tpl = trim($_GPC['tpl']);
        if ($tpl == 'option') {

            $tag = random(32);
            include $this->template('goods/tpl/option');
        } else if ($tpl == 'spec') {

            $spec = array("id" => random(32), "title" => $_GPC['title']);
            include $this->template('goods/tpl/spec');
        } else if ($tpl == 'specitem') {

            $spec = array("id" => $_GPC['specid']);
            $specitem = array("id" => random(32), "title" => $_GPC['title'], "show" => 1);
            include $this->template('goods/tpl/spec_item');
        } else if ($tpl == 'param') {

            $tag = random(32);
            include $this->template('goods/tpl/param');
        }
    }

    function query()
    {
        global $_W, $_GPC;
        $kwd = trim($_GPC['keyword']);

        $params = array();
        $params[':uniacid'] = $_W['uniacid'];
        $params[':merchid'] = $_W['merchid'];
        $condition = " and status=1 and deleted=0 and uniacid=:uniacid and merchid=:merchid";
        if (!empty($kwd)) {
            $condition .= " AND (`title` LIKE :keywords OR `keywords` LIKE :keywords)";
            $params[':keywords'] = "%{$kwd}%";
        }
        $ds = pdo_fetchall('SELECT id,title,thumb,marketprice,productprice,share_title,share_icon,description,minprice FROM ' . tablename('ewei_shop_goods') . " WHERE 1 {$condition} order by createtime desc", $params);
        $ds = set_medias($ds, array('thumb', 'share_icon'));
        if ($_GPC['suggest']) {
            die(json_encode(array('value' => $ds)));
        }
        include $this->template();

    }

    function diyform_tpl()
    {
        global $_W, $_GPC;
        $globalData = mp('diyform')->globalData();
        extract($globalData);

        $addt = $_GPC['addt'];
        $kw = $_GPC['kw'];
        $flag = intval($_GPC['flag']);
        $data_type = $_GPC['data_type'];
        $tmp_key = $kw;
        include $this->template('diyform/temp/tpl');
    }

    public function goods_selector()
    {
        global $_GPC, $_W;

  
        $page = empty($page) ? max(1, (int)$_GPC['page']) : $page;
        $page_size = 8;
        $page_start = ($page - 1) * $page_size;
        $condition = '';
        if (!empty($_GPC['condition'])) {
            $condition = base64_decode(trim($_GPC['condition']));
        }
      
        $params = array(':uniacid' => $_W['uniacid']);
        $keywords = trim($_GPC['keywords']);
        if (!empty($keywords)) {
            $params[':title'] = '%' . $keywords . '%';
            $keywords = "and title like :title ";
        }

   
        $goodsgroup = intval($_GPC['goodsgroup']);
        $goodsgroup_where = '';
        if (!empty($goodsgroup)) {
            $goodsgroup_where = " and (find_in_set('{$goodsgroup}',ccates) or find_in_set('{$goodsgroup}',pcates) or find_in_set('{$goodsgroup}',tcates)) ";
        }
    
        if ((int)$_GPC['merchid']) {
            $condition .= ' and merchid = ' . (int)$_W['merchid'];
        }
        $limit = "limit {$page_start},{$page_size}";

        $query_field = 'id,title,total,hasoption,marketprice,thumb,minprice,bargain,sales';
        $tablename = tablename('ewei_shop_goods');
        $condition .= ' AND status=1 AND deleted=0 AND checked=0 ';

        $query_sql = "select {$query_field} from " . $tablename . " where uniacid = :uniacid {$condition} " . $goodsgroup_where . $keywords;
        $count_field = 'count(*)';
        $count_sql = str_replace($query_field, $count_field, $query_sql);
        $query_sql .= $limit;
        $list = pdo_fetchall($query_sql, $params);
        if (!empty($list))
            foreach ($list as &$li) {
                $li['thumb'] = tomedia($li['thumb']);
            }
        $count = pdo_fetchcolumn($count_sql, $params);
   
        $page_num = ceil($count / $page_size);
        $total = $page_num;
        $i = 1;
        while ($page_num) {
            $page_num_arr[] = $i++;
            $page_num--;
        }
 
        $slice = 0;
        if ($page > 6) {
            $slice = $page - 6;
        }
        is_array($page_num_arr) && $page_num_arr = array_slice($page_num_arr, $slice, 10);

        if (empty($list) && $page !== 1) {
            
            header('location:'.webUrl('goods.goods_selector',array('merchid'=>$_W['merchid'])));
            exit;
        } else {
            include $this->template('goods/goods_selector');
        }
    }


    public function getcate(){
        if (!empty(  $_W['import']))
        {
        $category = m('shop')->getAllCategory();
        }
        else
        {
            $category = m('shop')->getFullCategory(true, true);
        }
 
        header('Content-type: application/json');
        exit(json_encode($category));
    }

    public function import()
    {
        global $_W, $_GPC;
        if ($_W['merch_user']['can_import'] != 1)
        {
            return $this->message('您没有权限操作','','error');
        }
        $_W['import'] = 1;
        if ($_W['ispost']) {
            $merchgroup = pdo_get('ewei_shop_merch_group', array('id' => $_W['merch_user']['groupid']));
            $ids = (array_keys(json_decode(htmlspecialchars_decode($_GPC['rewardgoods']), true)));
            if (count($ids) > 16)
            {
                show_json(0,'超过最大导入限制');
            }
            pdo_begin();
            foreach ($ids as $id) {
                $goods = pdo_get('ewei_shop_goods', array('id' => $id));
                if (empty($goods)) {
                    continue;
                }
                $options = pdo_getall('ewei_shop_goods_option', array('goodsid' => $id, 'uniacid' => $_W['uniacid']));

                $goods['merchid'] = $_W['merchid'];
                unset($goods['id']);
                if ($merchgroup['goodschecked'] == 1) {
                    $goods['checked'] = 0;
                } else {
                    $goods['checked'] = 1;
                }
                if ($merchgroup['commissionchecked'] == 1) {
                    $goods['hascommission'] = 0;
                    $goods['commission1_rate'] = null;
                    $goods['commission1_pay'] = null;
                    $goods['commission2_rate'] = null;
                    $goods['commission2_pay'] = null;
                    $goods['commission3_rate'] = null;
                    $goods['commission3_pay'] = null;
                }
                $goods['status'] = 0;
                $goods['pcate'] = 0;
                $goods['ccate'] = 0;
                $goods['tcate'] = 0;
                $goods['ccates'] = '';
                $goods['pcates'] = '';
                $goods['tcates'] = '';
                $goods['cates'] = '';
                $goods['allcates'] = '';
                $goods['import_id'] = $id;
               
                pdo_insert('ewei_shop_goods', $goods);
                $newid = pdo_insertid();
              
                $params = pdo_getall('ewei_shop_goods_param', array('goodsid' => $id));
                foreach ($params as $param) {
                    $param['goodsid'] = $newid;
                    unset($param['id']);
                   
                    pdo_insert('ewei_shop_goods_param', $param);
                }
              
                $spec = pdo_getall('ewei_shop_goods_spec', array('goodsid' => $id, 'uniacid' => $_W['uniacid']));
                foreach ($spec as $specitem) {
                    $sid = $specitem['id'];
                    unset($specitem['id']);
                    $specitem['goodsid'] = $newid;
                    pdo_insert('ewei_shop_goods_spec', $specitem);
                    $new_specid = pdo_insertid();
                   
                    $specitems = pdo_getall('ewei_shop_goods_spec_item', array('specid' => $sid));
                    foreach ($specitems as $sitems) {
                        $sitems['specid'] = $new_specid;
                        $iid = $sitems['id'];
                        unset($sitems['id']);
                        pdo_insert('ewei_shop_goods_spec_item', $sitems);
                        $itemsid = pdo_insertid();
                        foreach ($options as &$op) {
                            unset($op['id']);
                            $op['goodsid'] = $newid;
                            $op['specs'] = str_replace($iid . '_', $itemsid . '_', $op['specs']);
                            $op['specs'] = str_replace('_' . $iid, '_' . $itemsid, $op['specs']);
                        }
                        unset($op);
                    }
                }
            }
            foreach ($options as $option) {
                pdo_insert('ewei_shop_goods_option', $option);
            }
            pdo_commit();
            show_json(1,'操作成功');
        }
        include $this->template('goods/import');
        $_W['import'] = null;
    }

    function query_goods()
    {
        global $_W, $_GPC;
        $kwd = trim($_GPC['keyword']);

        $params = array();
        $params[':uniacid'] = $_W['uniacid'];
        $params[':merchid'] = 0;
        $condition = " and status=1 and deleted=0 and uniacid=:uniacid and merchid=:merchid";
        if (!empty($kwd)) {
            $condition .= " AND (`title` LIKE :keywords OR `keywords` LIKE :keywords)";
            $params[':keywords'] = "%{$kwd}%";
        }
        $ds = pdo_fetchall('SELECT id,title,thumb,marketprice,productprice,share_title,share_icon,description,minprice FROM ' . tablename('ewei_shop_goods') . " WHERE 1 {$condition} order by createtime desc", $params);
        $ds = set_medias($ds, array('thumb', 'share_icon'));
        if ($_GPC['suggest']) {
            die(json_encode(array('value' => $ds)));
        }
        include $this->template();

    }

    public function goods_cat(){
        $leve1=pdo_fetchall('select * from'.tablename('ewei_shop_category').'where parentid=0 and level=1');
        $data=array();
        foreach ($leve1 as $l1){
            $leve_list=array(
                'id'=>$l1['id'],
                'name'=> $l1['name'],
                'pid'=> -1,
                'cities'=>array()
            );
            $leve2=pdo_fetchall('select * from'.tablename('ewei_shop_category').'where level=2 and parentid='.$l1['id']);
            foreach ($leve2 as $k=>$l2){
                $leve_list['cities'][]=array(
                    'id'=> $l2['id'],
                    'name'=> $l2['name'],
                    'pid'=> $l1['id'],
                    'district'=>array(),
                );
                $leve3=pdo_fetchall('select * from'.tablename('ewei_shop_category').'where level=3 and  parentid='.$l2['id']);
                foreach ($leve3 as $l3){
                    $leve_list['cities'][$k]['district'][]=array(
                        'id'=> $l3['id'],
                        'name'=> $l3['name'],
                        'pid'=> $l2['id'],
                    );
                }
            }
            $data['data'][]=$leve_list;
        }
        show_json(1, array('data' => $data));
    }
}
