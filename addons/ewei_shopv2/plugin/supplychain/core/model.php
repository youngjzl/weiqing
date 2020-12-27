<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}

function supplychain_sort_enoughs($a, $b)
{
    $enough1 = floatval($a['enough']);
    $enough2 = floatval($b['enough']);
    if ($enough1 == $enough2) {
        return 0;
    } else {
        return ($enough1 < $enough2) ? 1 : -1;
    }
}
class SupplychainModel extends PluginModel
{

    public static $allPerms = array();
    public static $getLogTypes = array();
    public static $formatPerms = array();

    public function getSet($name = '', $supplychainid = 0, $refresh = false)
    {
        global $_W, $_GPC;
        $supplychainid = empty($supplychainid) ? $_W['supplychainid'] : intval($supplychainid);
        $key = 'supplychain_sets_' . $supplychainid;
        if ($refresh) {
            return $this->refreshSet($supplychainid);
        }
        $allset = m('cache')->getArray($key);
        if (!empty($name) && empty($allset[$name])) {
            $allset = $this->refreshSet($supplychainid);
        } elseif (empty($allset)) {
            $allset = $this->refreshSet($supplychainid);
        }
        return $name ? $allset[$name] : $allset;
    }
    public function refreshSet($supplychainid = 0)
    {
        global $_W;
        $supplychainid = empty($supplychainid) ? $_W['supplychainid'] : $supplychainid;
        $key = 'supplychain_sets_' . $supplychainid;
        $supplychain_set = pdo_fetch('select sets from ' . tablename('ewei_shop_supplychain_user') . ' where uniacid=:uniacid and id=:id limit 1 ', array(':uniacid' => $_W['uniacid'], ':id' => $supplychainid));
        $allset = iunserializer($supplychain_set['sets']);
        if (!is_array($allset)) {
            $allset = array();
        }
        m('cache')->set($key, $allset);
        return $allset;
    }

    public function check_perm($permtypes = '')
    {
        global $_W;
        $check = true;
        if (empty($permtypes)) {
            return false;
        }

        if (!strexists($permtypes, '&') && !strexists($permtypes, '|')) {

            $check = $this->check($permtypes);
        } else if (strexists($permtypes, '&')) {

            $pts = explode('&', $permtypes);
            foreach ($pts as $pt) {
                $check = $this->check($pt);
                if (!$check) {
                    break;
                }
            }
        } else if (strexists($permtypes, '|')) {
            $pts = explode('|', $permtypes);
            foreach ($pts as $pt) {
                $check = $this->check($pt);
                if ($check) {
                    break;
                }
            }
        }
        return $check;
    }
    private function check($permtype = '')
    {
        global $_W, $_GPC;
        if ($_W['supplychainisfounder'] == 1) {
            return true;
        }
        $uid = $_W['supplychainuid'];
        if (empty($permtype)) {
            return false;
        }
        $user = pdo_fetch('select u.status as userstatus,r.status as rolestatus,r.perms as roleperms,u.roleid from ' . tablename('ewei_shop_supplychain_account') . ' u '
            . ' left join ' . tablename('ewei_shop_supplychain_perm_role') . ' r on u.roleid = r.id '
            . ' where u.id=:supplychainuid limit 1 ', array(':supplychainuid' => $uid));

        if (empty($user) || empty($user['userstatus'])) {
            return false;
        }
        if (!empty($user['roleid']) && empty($user['rolestatus'])) {
            return false;
        }

        $perms = explode(',', $user['roleperms']);
        if (empty($perms)) {
            return false;
        }


        $is_xxx = $this->check_xxx($permtype);

        if ($is_xxx) {
            if (!in_array($is_xxx, $perms)) {
                return false;
            }
        } else {
            if (!in_array($permtype, $perms)) {
                return false;
            }
        }
        return true;
    }
    /**
     * 查看是不是继承
     * @param $permtype
     * @return bool|string
     */
    function check_xxx($permtype)
    {
        if ($permtype) {
            $allPerm = $this->allPerms();
            $permarr = explode('.', $permtype);
            if (isset($permarr[3])) {
                $is_xxx = isset($allPerm[$permarr[0]][$permarr[1]][$permarr[2]]['xxx'][$permarr[3]]) ? $allPerm[$permarr[0]][$permarr[1]][$permarr[2]]['xxx'][$permarr[3]] : false;
            } elseif (isset($permarr[2])) {
                $is_xxx = isset($allPerm[$permarr[0]][$permarr[1]]['xxx'][$permarr[2]]) ? $allPerm[$permarr[0]][$permarr[1]]['xxx'][$permarr[2]] : false;
            } elseif (isset($permarr[1])) {
                $is_xxx = isset($allPerm[$permarr[0]]['xxx'][$permarr[1]]) ? $allPerm[$permarr[0]]['xxx'][$permarr[1]] : false;
            } else {
                $is_xxx = false;
            }
            if ($is_xxx) {
                $permarr = explode('.', $permtype);
                array_pop($permarr);
                $is_xxx = implode('.', $permarr) . '.' . $is_xxx;
            }
            return $is_xxx;
        }
        return false;

    }
    public function allPerms()
    {
        if (empty(self::$allPerms)) {
            //系统
            $perms = array(
                'shop' => $this->perm_shop(),

                'goods' => $this->perm_goods(),

                'order' => $this->perm_order(),

                'statistics' => $this->perm_statistics(),

                'sale' => $this->perm_sale(),

                'creditshop' => $this->perm_creditshop(),

                'perm' => $this->perm_perm(),

                'apply' => $this->perm_apply(),

                'taobao' => $this->perm_taobao(),

                'exhelper' => $this->perm_exhelper(),

                'diypage' => $this->perm_diypage(),

                'quick' => $this->perm_quick(),
                //'mmanage' => $this->perm_mmanage(),
            );
            self::$allPerms = $perms;
        }
        return self::$allPerms;
    }
    protected function perm_diypage()
    {
        return array(
            'text' => m('plugin')->getName('diypage'),
            'page' => array(
                'sys' => array(
                    'text' => '系统页面',
                    'main' => '查看列表',
                    'add' => '添加-log',
                    'edit' => '编辑-log',
                    'delete' => '删除-log',
                    'savetemp' => '另存为模板-log'
                ),
                'plu' => array(
                    'text' => '应用页面',
                    'main' => '查看列表',
                    'add' => '添加-log',
                    'edit' => '编辑-log',
                    'delete' => '删除-log',
                    'savetemp' => '另存为模板-log'
                ),
                'diy' => array(
                    'text' => '自定义页面',
                    'main' => '查看列表',
                    'add' => '添加-log',
                    'edit' => '编辑-log',
                    'delete' => '删除-log',
                    'savetemp' => '另存为模板-log'
                ),
                'mod' => array(
                    'text' => '公用模块',
                    'main' => '查看列表',
                    'add' => '添加-log',
                    'edit' => '编辑-log',
                    'delete' => '删除-log'
                )
            ),
            'menu' => array(
                'text' => '自定义菜单',
                'main' => '查看列表',
                'add' => '添加-log',
                'edit' => '编辑-log',
                'delete' => '删除-log'
            ),
            'shop' => array(
                'text' => '商城页面设置',
                'page' => array(
                    'text' => '页面设置',
                    'main' => '查看',
                    'save' => '保存-log'
                ),
                'menu' => array(
                    'text' => '按钮设置',
                    'main' => '查看',
                    'save' => '保存-log'
                ),
                'layer' => array(
                    'text' => '悬浮按钮',
                    'main' => '编辑-log'
                ),
                'followbar' => array(
                    'text' => '关注条',
                    'main' => '编辑-log'
                ),
                'danmu' => array(
                    'text' => '下单提醒',
                    'main' => '编辑-log'
                ),
                'adv' => array(
                    'text' => '启动广告',
                    'main' => '查看列表',
                    'add' => '添加-log',
                    'edit' => '编辑-log',
                    'delete' => '删除-log'
                )
            ),
            'temp' => array(
                'text' => '模板管理',
                'main' => '通过模板创建页面',
                'delete' => '删除模板',
                'category' => array(
                    'text' => '模板分类',
                    'main' => '查看',
                    'add' => '添加-log',
                    'edit' => '编辑-log',
                    'delete' => '删除-log'
                )
            )
        );
    }

    protected function perm_quick()
    {
        return array(
            'text' => m('plugin')->getName('quick'),
            'adv' => array(
                'text' => '幻灯片管理',
                'main' => '查看列表',
                'view' => '查看',
                'add' => '添加-log',
                'edit' => '编辑-log',
                'delete' => '删除-log',
                'xxx' => array(
                    'enabled' => 'edit'
                )
            ),
            'pages' => array(
                'text' => '页面管理',
                'main' => '查看列表',
                'add' => '添加-log',
                'edit' => '编辑-log',
                'delete' => '删除-log',
                'xxx' => array(
                    'status' => 'edit'
                )
            )
        );
    }

    protected function perm_creditshop()
    {
        return array(
            'text' => m('plugin')->getName('creditshop'),
            'goods' =>
                array(
                    'text' => '商品',
                    'main' => '查看列表',
                    'view' => '查看详细',
                    'add' => '添加-log',
                    'edit' => '修改-log',
                    'delete' => '删除-log',
                    'xxx' =>
                        array(
                            'property' => 'edit'
                        )
                ),
            'log' =>
                array(
                    'text' => '订单/记录',
                    'exchange' => '兑换记录',
                    'draw' => '抽奖记录',
                    'order' => '待发货',
                    'convey' => '待收货',
                    'finish' => '已完成',
                    'verifying' => '待核销',
                    'verifyover' => '已核销',
                    'verify' => '全部核销',
                    'detail' => '详情',
                    'doexchange' => '确认兑换-log',
                    'export' => '导出明细-log'
                ),
            'comment' => array(
                'text' => '评价管理',
                'edit' => '回复评价',
                'check' => '审核评价'
            ),
        );
    }

    protected function perm_shop()
    {
        return array(
            'text' => '商城管理',
            'adv' =>
                array(
                    'text' => '幻灯片',
                    'main' => '查看列表',
                    'view' => '查看内容',
                    'add' => '添加-log',
                    'edit' => '修改-log',
                    'delete' => '删除-log',
                    'xxx' => array(
                        'displayorder' => 'edit',
                        'enabled' => 'edit'
                    )
                ),
            'nav' =>
                array(
                    'text' => '首页导航图标',
                    'main' => '查看列表',
                    'view' => '查看内容',
                    'add' => '添加-log',
                    'edit' => '修改-log',
                    'delete' => '删除-log',
                    'xxx' => array(
                        'displayorder' => 'edit',
                        'status' => 'edit'
                    )
                ),
            'banner' =>
                array(
                    'text' => '首页广告',
                    'main' => '查看列表',
                    'view' => '查看内容',
                    'add' => '添加-log',
                    'edit' => '修改-log',
                    'delete' => '删除-log',
                    'xxx' => array(
                        'displayorder' => 'edit',
                        'enabled' => 'edit',
                        'setswipe' => 'edit'
                    )
                ),
            'cube' =>
                array(
                    'text' => '首页魔方',
                    'main' => '查看',
                    'edit' => '修改-log'
                ),
            'recommand' =>
                array(
                    'text' => '首页商品推荐',
                    'main' => '编辑推荐商品-log',
                    'setstyle' => '设置商品组样式-log',
                ),
            'sort' =>
                array(
                    'text' => '首页元素排版',
                    'main' => '修改-log'
                ),
            'dispatch' =>
                array(
                    'text' => '配送方式',
                    'main' => '查看列表',
                    'view' => '查看内容',
                    'add' => '添加-log',
                    'edit' => '修改-log',
                    'delete' => '删除-log',
                    'xxx' => array(
                        'displayorder' => 'edit',
                        'enabled' => 'edit',
                        'setdefault' => 'edit'
                    )
                ),
            'notice' =>
                array(
                    'text' => '公告',
                    'main' => '查看列表',
                    'view' => '查看内容',
                    'add' => '添加-log',
                    'edit' => '修改-log',
                    'delete' => '删除-log',
                    'xxx' => array(
                        'displayorder' => 'edit',
                        'status' => 'edit'
                    )
                ),
            'comment' =>
                array(
                    'text' => '评价',
                    'main' => '查看列表',
                    'add' => '添加-log',
                    'edit' => '编辑-log',
                    'post' => '回复-log',
                    'delete' => '删除-log'
                ),
            'refundaddress' =>
                array(
                    'text' => '退货地址',
                    'main' => '查看列表',
                    'view' => '查看内容',
                    'add' => '添加-log',
                    'edit' => '修改-log',
                    'delete' => '删除-log',
                    'xxx' => array(
                        'setdefault' => 'edit'
                    )
                ),
            'verify' =>
                array(
                    'text' => 'O2O核销',
                    'saler' =>
                        array(
                            'text' => '店员管理',
                            'main' => '查看列表',
                            'view' => '查看内容',
                            'add' => '添加-log',
                            'edit' => '修改-log',
                            'delete' => '删除-log',
                            'xxx' => array(
                                'status' => 'edit'
                            )
                        ),
                    'store' =>
                        array(
                            'text' => '门店管理',
                            'main' => '查看列表',
                            'view' => '查看内容',
                            'add' => '添加-log',
                            'edit' => '修改-log',
                            'delete' => '删除-log',
                            'xxx' => array(
                                'displayorder' => 'edit',
                                'status' => 'edit',
                            )
                        ),
                    'set' =>
                        array(
                            'text' => '关键词设置',
                            'main' => '查看',
                            'edit' => '编辑-log'
                        )
                )
        );
    }

    protected function perm_taobao()
    {
        return array(
            'text' => m('plugin')->getName('taobao'),
            'main' => '获取宝贝',
            'jingdong' => array(
                'text' => '京东助手',
                'main' => '获取宝贝'
            ),
            'one688' => array(
                'text' => '1688宝贝助手',
                'main' => '获取宝贝'
            ),
            'taobaocsv' => array(
                'text' => '淘宝CSV助手',
                'main' => '获取宝贝'
            ),
            'set' => array(
                'text' => '淘宝助手客户端',
                'main' => '获取宝贝'
            )
        );
    }

    protected function perm_goods()
    {
        return array(
            'text' => '商品管理',
            'main' => '浏览列表',
            'view' => '查看详情',
            'add' => '添加-log',
            'edit' => '修改-log',
            'delete' => '删除-log',
            'delete1' => '彻底删除-log',
            'restore' => '恢复到仓库-log',
            'xxx' => array(
                'status' => 'edit',
                'property' => 'edit',
                'change' => 'edit'
            ),
            'category' => array(
                'text' => '商品分类',
                'add' => '添加-log',
                'edit' => '修改-log',
                'delete' => '删除-log',
                'xxx' => array(
                    'enabled' => 'edit'
                )
            ),
            'virtual' =>
                array(
                    'text' => '虚拟卡密',
                    'temp' => array(
                        'text' => '卡密模板管理',
                        'view' => '浏览',
                        'add' => '添加-log',
                        'edit' => '修改-log',
                        'delete' => '删除-log',
                    ),
                    'category' => array(
                        'text' => '卡密分类管理',
                        'add' => '添加-log',
                        'edit' => '编辑-log',
                        'delete' => '删除-log',
                    ),
                    'data' => array(
                        'text' => '卡密数据',
                        'add' => '添加-log',
                        'edit' => '修改-log',
                        'delete' => '删除-log',
                        'export' => '导出-log',
                        'temp' => '下载模板',
                        'import' => '导入-log',
                    ),
                )
        );
    }

    protected function perm_sale()
    {
        $array = array(
            'text' => '营销',
            'coupon' =>
                array(
                    'text' => '优惠券管理',
                    'view' => '浏览',
                    'add' => '添加-log',
                    'edit' => '修改-log',
                    'delete' => '删除-log',
                    'send' => '发放-log',
                    'set' => '修改设置-log',
                    'xxx' => array(
                        'displayorder' => 'edit'
                    ),
                    'category' => array(
                        'text' => '优惠券分类',
                        'main' => '查看',
                        'edit' => '修改-log'
                    ),
                    'log' => array(
                        'text' => '优惠券记录',
                        'main' => '查看',
                        'export' => '导出记录'
                    ),
                )
        );
        $sale = array(
            'enough' => '修改满额立减-log',
            'enoughfree' => '修改满额包邮-log',
        );
        $array = array_merge($array, $sale);
        return $array;
    }

    protected function perm_statistics()
    {
        return array(
            'text' => '数据统计',
            'sale' =>
                array(
                    'text' => '销售统计',
                    'main' => '查看',
                    'export' => '导出-log'
                ),
            'sale_analysis' =>
                array(
                    'text' => '销售指标',
                    'main' => '查看'
                ),
            'order' =>
                array(
                    'text' => '订单统计',
                    'main' => '查看',
                    'export' => '导出-log'
                ),
            'goods' =>
                array(
                    'text' => '商品销售明细',
                    'main' => '查看',
                    'export' => '导出-log'
                ),
            'goods_rank' =>
                array(
                    'text' => '商品销售排行',
                    'main' => '查看',
                    'export' => '导出-log'
                ),
            'goods_trans' =>
                array(
                    'text' => '商品销售转化率',
                    'main' => '查看',
                    'export' => '导出-log'
                ),
            'member_cost' =>
                array(
                    'text' => '会员消费排行',
                    'main' => '查看',
                    'export' => '导出-log'
                ),
            'member_increase' =>
                array(
                    'text' => '会员增长趋势',
                    'main' => '查看'
                )
        );
    }

    protected function perm_order()
    {
        return array(
            'text' => '订单',
            'detail' => array(
                'text' => '订单详情',
                'edit' => '编辑',
            ),
            'export' => array(
                'text' => '自定义导出-log',
                'main' => '浏览页面',
                'xxx' => array(
                    'save' => 'main',
                    'delete' => 'main',
                    'gettemplate' => 'main',
                    'reset' => 'main'
                )
            ),

            'batchsend' => array(
                'text' => '批量发货',
                'main' => '批量发货-log',
                'xxx' => array(
                    'import' => 'main'
                )
            ),
            'list' => array(
                'text' => '订单管理',
                'main' => '浏览全部订单',
                'status_1' => '浏览关闭订单',
                'status0' => '浏览待付款订单',
                'status1' => '浏览已付款订单',
                'status2' => '浏览已发货订单',
                'status3' => '浏览完成的订单',
                'status4' => '浏览退货申请订单',
                'status5' => '浏览已退货订单'
            ),
            'op' => array(
                'text' => '操作',
                'delete' => '订单删除-log',
                'pay' => '确认付款-log',
                'send' => '发货-log',
                'sendcancel' => '取消发货-log',
                'finish' => '确认收货(快递单)-log',
                'verify' => '确认核销(核销单)-log',
                'fetch' => '确认取货(自提单)-log',
                'close' => '关闭订单-log',
                'changeprice' => '订单改价-log',
                'changeaddress' => '修改收货地址-log',
                'remarksaler' => '订单备注-log',
                'paycancel' => '订单取消付款-log',
                'fetchcancel' => '订单取消取货-log',
                'changeexpress' => '修改快递状态',
                'refund' => array(
                    'text' => '维权',
                    'main' => '维权信息',
                    'submit' => '提交维权申请',
                ),
            ),
        );
    }

    protected function perm_perm()
    {
        return array(
            'text' => '权限系统',
            'log' => array(
                'text' => '操作日志',
                'main' => '查看列表'
            ),
            'role' => array(
                'text' => '角色管理',
                'main' => '查看列表',
                'add' => '添加-log',
                'edit' => '修改-log',
                'delete' => '删除-log',
                'xxx' => array(
                    'status' => 'edit',
                    'query' => 'main'
                )
            ),
            'user' => array(
                'text' => '操作员管理',
                'main' => '查看列表',
                'add' => '添加-log',
                'edit' => '修改-log',
                'delete' => '删除-log',
                'xxx' => array(
                    'status' => 'edit'
                )
            )
        );
    }

    protected function perm_apply()
    {
        return array(
            'text' => '提现',
            'detail' => array(
                'text' => '提现详情',
                'export' => '导出提现申请订单详情'
            ),
            'list' => array(
                'text' => '提现管理',
                'post' => '申请提现',
                'status1' => '浏览待审核申请',
                'status2' => '浏览待结算申请',
                'status3' => '浏览已结算申请',
                'export' => '导出申请'
            )
        );
    }

    protected function perm_exhelper()
    {
        return array(
            'text' => '快递助手',
            'print' =>
                array(
                    'single' =>
                        array(
                            'text' => '单个打印',
                            'express' => '打印快递单-log',
                            'invoice' => '打印发货单-log',
                            'dosend' => '一键发货-log'
                        ),
                    'batch' => array(
                        'text' => '批量打印',
                        'express' => '打印快递单-log',
                        'invoice' => '打印发货单-log',
                        'dosend' => '一键发货-log'
                    )
                ),
            'temp' =>
                array(
                    'express' =>
                        array(
                            'text' => '快递单模板管理',
                            'add' => '添加-log',
                            'edit' => '修改-log',
                            'delete' => '删除-log',
                            'xxx' =>
                                array(
                                    'setdefault' => 'edit'
                                )
                        ),
                    'invoice' =>
                        array(
                            'text' => '发货单模板管理',
                            'add' => '添加-log',
                            'edit' => '修改-log',
                            'delete' => '删除-log',
                            'xxx' =>
                                array(
                                    'setdefault' => 'edit'
                                )
                        )
                ),
            'sender' =>
                array(
                    'text' => '发货人信息管理',
                    'main' => '查看列表',
                    'view' => '查看',
                    'add' => '添加-log',
                    'edit' => '修改-log',
                    'delete' => '删除-log',
                    'xxx' =>
                        array(
                            'setdefault' => 'edit'
                        )
                ),
            'short' =>
                array(
                    'text' => '商品简称',
                    'main' => '查看',
                    'edit' => '修改-log',
                ),
            'printset' =>
                array(
                    'text' => '打印端口设置',
                    'main' => '查看',
                    'edit' => '修改-log',
                )

        );
    }
    public function check_edit($permtype = '', $item = array())
    {
        if (empty($permtype)) {
            return false;
        }
        if (!$this->check_perm($permtype)) {
            return false;
        }
        if (empty($item['id'])) {
            $add_perm = $permtype . ".add";
            if (!$this->check($add_perm)) {
                return false;
            }
            return true;
        } else {
            $edit_perm = $permtype . ".edit";
            if (!$this->check($edit_perm)) {
                return false;
            }
            return true;
        }
    }

    public function getCats($fullname = false){
        global $_W;
        $allcategory = array();
        $sql = 'SELECT * FROM ' . tablename('ewei_shop_supplychain_category') . ' WHERE uniacid=:uniacid AND supplychainid=:supplychainid';

        $sql .= ' ORDER BY parentid ASC, displayorder DESC';
        $category = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid'],':supplychainid'=>$_W['supplychainid']));
        $category = set_medias($category, array('thumb', 'advimg'));

        if (empty($category)) {
            return array();
        }

        foreach ($category as &$c) {
            if (empty($c['parentid'])) {
                $allcategory[] = $c;

                foreach ($category as &$c1) {
                    if ($c1['parentid'] != $c['id']) {
                        continue;
                    }

                    if ($fullname) {
                        $c1['name'] = $c['name'] . '-' . $c1['name'];
                    }

                    $allcategory[] = $c1;

                    foreach ($category as &$c2) {
                        if ($c2['parentid'] != $c1['id']) {
                            continue;
                        }

                        if ($fullname) {
                            $c2['name'] = $c1['name'] . '-' . $c2['name'];
                        }

                        $allcategory[] = $c2;

                        foreach ($category as &$c3) {
                            if ($c3['parentid'] != $c2['id']) {
                                continue;
                            }

                            if ($fullname) {
                                $c3['name'] = $c2['name'] . '-' . $c3['name'];
                            }

                            $allcategory[] = $c3;
                        }

                        unset($c3);
                    }

                    unset($c2);
                }

                unset($c1);
            }

            unset($c);
        }

        return $allcategory;
    }

    public function save_taobaocsv_goods($item = array(), $supplychainid = 0)
    {
        global $_W;
        $data = array('uniacid' => $_W['uniacid'], 'supplychainid' => $supplychainid, 'catch_source' => 'taobaocsv', 'catch_id' => '', 'catch_url' => '', 'title' => $item['title'], 'total' => $item['total'], 'marketprice' => $item['marketprice'], 'pcate' => '', 'ccate' => '', 'tcate' => '', 'cates' => '', 'sales' => 0, 'createtime' => time(), 'updatetime' => time(), 'hasoption' => 0, 'status' => 0, 'deleted' => 0, 'buylevels' => '', 'showlevels' => '', 'buygroups' => '', 'showgroups' => '', 'noticeopenid' => '', 'storeids' => '', 'minprice' => $item['marketprice'], 'maxprice' => $item['marketprice'], 'supplychainsale' => ($item['supplychainid'] == 0 ? 0 : 1));
        if (empty($item['supplychainid']))
        {
            $data['discounts'] = '{"type":"0","default":"","default_pay":""}';
        }
        if (!(empty($supplychainid)))
        {
            //是否需要审核
//            if (empty($_W['supplychainid_user']['goodschecked']))
//            {
//                $data['checked'] = 1;
//            }
//            else
//            {
                $data['checked'] = 0;
//            }
        }
        $thumb_url = array();
        $pics = $item['pics'];
        $piclen = count($pics);
        if (0 < $piclen)
        {
            $data['thumb'] = $this->save_image($pics[0], false);
            if (1 < $piclen)
            {
                $i = 1;
                while ($i < $piclen)
                {
                    $img = $this->save_image($pics[$i], false);
                    $thumb_url[] = $img;
                    ++$i;
                }
            }
        }
        $data['thumb_url'] = serialize($thumb_url);
        pdo_insert('ewei_shop_goods', $data);
        $goodsid = pdo_insertid();
        $content = $item['content'];
        preg_match_all('/<img.*?src=[\\\\\'| \\"](.*?(?:[\\.gif|\\.jpg]?))[\\\\\'|\\"].*?[\\/]?>/', $content, $imgs);
        if (isset($imgs[1]))
        {
            foreach ($imgs[1] as $img )
            {
                $catchimg = $img;
                if (substr($catchimg, 0, 2) == '//')
                {
                    $img = 'http://' . substr($img, 2);
                }
                $im = array('catchimg' => $catchimg, 'system' => $this->save_image($img, true));
                $images[] = $im;
            }
        }
        $html = $content;
        if (isset($images))
        {
            foreach ($images as $img )
            {
                if (!(empty($img['system'])))
                {
                    $html = str_replace($img['catchimg'], $img['system'], $html);
                }
            }
        }
        $html = m('common')->html_to_images($html);
        $d = array('content' => $html);
        pdo_update('ewei_shop_goods', $d, array('id' => $goodsid));
        return array('result' => '1', 'goodsid' => $goodsid);
    }
    public function save_image($url, $iscontent)
    {
        global $_W;
        load()->func('communication');
        $ext = strrchr($url, '.');
        if (($ext != '.jpeg') && ($ext != '.gif') && ($ext != '.jpg') && ($ext != '.png'))
        {
            return $url;
        }
        if (trim($url) == '')
        {
            return $url;
        }
        $filename = random(32) . $ext;
        $save_dir = ATTACHMENT_ROOT . 'images/' . $_W['uniacid'] . '/' . date('Y') . '/' . date('m') . '/';
        if (!(file_exists($save_dir)) && !(mkdir($save_dir, 511, true)))
        {
            return $url;
        }
        $img = ihttp_get($url);
        if (is_error($img))
        {
            return '';
        }
        $img = $img['content'];
        if (strlen($img) != 0)
        {
            file_put_contents($save_dir . $filename, $img);
            $imgdir = 'images/' . $_W['uniacid'] . '/' . date('Y') . '/' . date('m') . '/';
            $saveurl = save_media($imgdir . $filename, true);
            return $saveurl;
        }
        return '';
    }
}
