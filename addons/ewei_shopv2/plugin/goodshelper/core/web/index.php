<?php

/*
 * 人人商城
 *
 * 青岛易联互动网络科技有限公司
 * http://www.we7shop.cn
 * TEL: 4000097827/18661772381/15865546761
 */
if (!defined('IN_IA')) {
    exit('Access Denied');
}

class Index_EweiShopV2Page extends PluginWebPage
{

    /**
     * @author 徐子轩
     */
    public function main()
    {
        global $_W;

        $type = 'taobao';

        $sql = 'SELECT * FROM ' . tablename('ewei_shop_category') . ' WHERE `uniacid` = :uniacid ORDER BY `parentid`, `displayorder` DESC';

        $category = m('shop')->getFullCategory(true, true);

        include $this->template();
    }


    function fetch()
    {
        global $_GPC, $_W;
        $url = $_GPC['url'];
        $type = $_GPC['type'];
        $cates = $_GPC['cate'];


        //判断是否直接设置的商品ID，如果是的话，直接应用
        if (is_numeric($url)) {
            $goods_id = $url;
        } else {
            //如果不是，正则匹配提取商品ID
            $goods_id = $this->model->matchid($url, $type);
        }


        if (empty($goods_id)) {
            show_json(0, '未获取到商品ID');
        }

        // 检测是否设置apikey
        $set = m('common')->getSysset('goodshelper');
        if (empty($set['apikey'])) {
            show_json(0, '请先设置apikey');
        }

        //处理请求接口需要的数据
        $auth = get_auth();

        if ($type == 'suning') {
            $params = [
                'type' => $type,
                'goods_id' => $goods_id['goods_id'],
                'shop_id' => $goods_id['shop_id'],
                'api_key' => $set['apikey'],
                'wechat_name' => $_W['account']['name'],
                'timestamp' => time(),
                'site_id' => (int)$auth['id'],
            ];
        } else {
            $params = [
                'type' => $type,
                'goods_id' => $goods_id,
                'api_key' => $set['apikey'],
                'wechat_name' => $_W['account']['name'],
                'timestamp' => time(),
            ];
        }

        $params['site_id'] = (int)$auth['id'];
        $params['uniacid'] = (int)$_W['uniacid'];
        $params['request_domain'] = $_SERVER['HTTP_HOST'];

        $data = $this->model->getcontent($params);


        if ($data['error'] == '-1') {
            show_json(0, $data['message']);
        }

        if ($type == 'taobao' || $type == 'tmall') {
            return $this->model->get_item_taobao($data['goods'], $cates);
        } elseif ($type == 'jd') {
            return $this->model->get_item_jd($data['goods'], $cates);
        } elseif ($type == 'alibaba') {
            return $this->model->get_item_on688($data['goods'], $cates);
        } elseif ($type == 'suning') {
            return $this->model->get_item_suning($data['goods'], $cates);
        } elseif ($type == 'pdd') {
            return $this->model->get_item_pdd($data['goods'], $cates);
        } elseif ($type == 'redbook') {
            return $this->model->get_item_redbook($data['goods'], $cates);
        }


    }

    /*
     *  设置商品助手apikey
     *  @author xzx
     */
    function set()
    {
        global $_W, $_GPC;

        $setApiKey = m('common')->getSysset('goodshelper');


        if ($_W['ispost']) {
            $status = $_GPC['apikey'];

            $data = array();
            $data['apikey'] = $status;
            m('common')->updateSysset(array('goodshelper' => $data));
            show_json(1);
        }


        include $this->template();
    }

}
