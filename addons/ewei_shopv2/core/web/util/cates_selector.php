<?php
//禁止非法访问
if (!defined('IN_IA')) {
    exit('Access Denied');
}

class Cates_selector_EweiShopV2Page extends WebPage
{
    /**
     * 商品选择器
     */
    public function main($page = 0)
    {
        global $_W;
//        $catList=pdo_fetchall('select * from'.tablename('ewei_shop_category').'where parentid=0');
        $catList=pdo_fetchall('select * from'.tablename('ewei_shop_category'));
        $data=array();
        foreach ($catList as $leve1){
            $list['id']=intval($leve1['id']);
            $list['pid']=intval($leve1['parentid']);
            $list['imgurl']='http://www.weiqin.com/attachment/headimg_2.jpg?time=1606109539';
            $list['status']=intval($leve1['enabled']);
            $list['name']=$leve1['name'];
            $list['upPrice']=0;//数据库还未加此字段（加价率）
            $list['time']='2020-10-10';//数据库还未加此字段(最新修改时间)
            $list['hasChildren']='true';//数据库还未加此字段(最新修改时间)
            $data[]=$list;
        }
        $data=json_encode($data,320);
        include $this->template('util/cates');
    }
}