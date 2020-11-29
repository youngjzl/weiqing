<?php
/*
 * 人人商城
 *
 * 青岛易联互动网络科技有限公司
 * http://www.we7shop.cn
 * TEL: 4000097827/18661772381/15865546761
 */
if(!defined('IN_IA')) {
    exit('Access Denied');
}

return array(
    'version'=>'1.0',
    'id'=>'goodshelper',
    'name'=>'商品助手',
    'v3'=>true,
    'menu'=>array(
        'plugincom'=>1,
        'items'=>array(
            array(
                'title'=>'淘宝',
            ),
            array(
                'title'=>'天猫',
                'route'=>'tianmao'
            ),
            array(
                'title'=>'京东',
                'route'=>'jingdong'
            ),
            array(
                'title'=>'拼多多',
                'route'=>'pdd'
            ),
            array(
                'title'=>'苏宁',
                'route'=>'sn'
            ),
            array(
                'title'=>'1688',
                'route'=>'one688'
            ),
            array(
                'title'=>'小红书',
                'route'=>'xhs'
            ),
            array(
                'title'=>'接口设置',
                'route'=>'set'
            )
        )
    )
);
