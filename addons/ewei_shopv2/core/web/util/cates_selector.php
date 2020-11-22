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
        include $this->template('util/goods_selector');
    }

    /**
     * 商品选项编辑器
     */
    public function op()
    {
        global $_GPC, $_W;
        $column = json_decode(htmlspecialchars_decode(urldecode(trim($_GPC['column']))), 1);
//        处理自定义项目
        if (is_array($column))
            foreach ($column as $ck => &$c) {
                if (is_string($c)) {
                    $c = array('name' => $ck, 'title' => $c);
                } elseif (is_array($c) && !empty($c['title'])) {
                    if (empty($c['name'])) {
                        $c['name'] = $ck;
                    }
                    continue;
                } else {
                    show_json(0, 'column参数不合法');
                }
            }
        $id = intval($_GPC['id']);
//        商品
        $sql = "select * from " . tablename('ewei_shop_goods') . " where id = {$id}";
        $goods = pdo_fetch($sql);
        if (empty($goods)) {
            show_json(0, '此商品已经不存在,请移除');
        }

        if (empty($_GPC['nooption'])) {
            //规格
            $sql = "select * from " . tablename('ewei_shop_goods_option') . " where goodsid = {$id}";
            $options = pdo_fetchall($sql);
        }
        include $this->template('util/goods_selector_op');
    }

    public function js()
    {
        global $_W;
        include $this->template('util/goods_selector_js');
    }

    //获取商品分类
    public function getcate()
    {
        global $_W;
        if (empty($_W['import']))
        {
            $category = m('shop')->getAllCategory();
        }
        else
        {
            $category = m('shop')->getFullCategory(true, true);

        }
        /*$category = array_filter($category,function ($v){
            if ($v['parentid'] == 0)return 1;
            return 0;
        });*/
        header('Content-type: application/json');
        exit(json_encode($category));
    }
}