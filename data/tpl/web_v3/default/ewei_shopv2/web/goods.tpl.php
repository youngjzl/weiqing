<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>
<style>
    tbody tr td{
        position: relative;
    }
    tbody tr  .icow-weibiaoti--{
        visibility: hidden;
        display: inline-block;
        color: #fff;
        height:18px;
        width:18px;
        background: #e0e0e0;
        text-align: center;
        line-height: 18px;
        vertical-align: middle;
    }
    tbody tr:hover .icow-weibiaoti--{
        visibility: visible;
    }
    tbody tr  .icow-weibiaoti--.hidden{
        visibility: hidden !important;
    }
    .full .icow-weibiaoti--{
        margin-left:10px;
    }
    .full>span{
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        vertical-align: middle;
        align-items: center;
    }
    tbody tr .label{
        margin: 5px 0;
    }
    .goods_attribute a{
        cursor: pointer;
    }
    .newgoodsflag{
        width: 22px;height: 16px;
        background-color: #ff0000;
        color: #fff;
        text-align: center;
        position: absolute;
        bottom: 70px;
        left: 57px;
        font-size: 12px;
    }
    .modal-dialog {
        min-width: 720px !important;
        position: absolute;
        left: 0;
        right: 0;
        top: 50%;
    }
    .catetag{
        overflow:hidden;

        text-overflow:ellipsis;

        display:-webkit-box;

        -webkit-box-orient:vertical;

        -webkit-line-clamp:2;
    }
    /* 更改价格库存排序 */
    .sorting {
        color: #bcbdba;
        display: none;
    }

    .hover_sanj, .hover_sank {
        position: relative;
    }

    .hover_sanj:hover .sorting, .hover_sank:hover .sorting {
        display: block;
    }

    .sorting .icon {
        height: 8px;
        position: absolute;
        left: 40px;
        font-size: 0.3rem;
        text-align: center;
    }

    .sorting .icon-sanjiao2 {
        top: 5px;
    }

    .sorting .icon-sanjiao1 {
        bottom: 12px;
    }

    .sorting .color_red {
        color: #f55;
    }
    .page-toolbar {
        height: 135px;
    }
    .input-group-btn button{
        margin-right: 20px;
    }
    .pick-area{display:inline-block;position:relative;background:#fff;text-decoration: none;cursor:default;}

    .pick-show{position:relative;padding:0 8px;height:28px;line-height:24px;border:1px solid #dedede;border-radius: 3px;;}
    .pick-show span{float:left;display:inline-block;max-width:100px;height:14px;line-height:12px;padding: 0 3px;margin-top:6px;overflow: hidden;text-overflow:ellipsis;white-space: nowrap;color:#333;cursor:pointer;}
    .pick-show span:hover{color:#fff!important;border-radius:3px;height: 14px;}
    .pick-show span.pressActive{background:#7894D4;color:#fff!important;border-radius:3px}
    .pick-show em.pick-arrow{position:absolute;top:11px;right:5px;display: block;border:6px solid #999;border-left:4px solid transparent;border-right:4px solid transparent;border-bottom:4px solid transparent;}
    .pick-show i{float:left;display:inline-block;padding:0 3px;color:#333;font-style:normal;}

    .pick-list{display:none;position:absolute;line-height:36px;margin:0;padding:0;background:#fff;z-index:999999999;overflow-y:auto;overflow-x:hidden;border:1px solid #dedede;border-top:none;}
    .pick-list li{margin:0;padding-left:8px;list-style: none;color:#888;overflow: hidden;text-overflow:ellipsis;white-space: nowrap;}
    .pick-list li:hover{color:#fff;font-weight:bold;}
</style>
<div class="page-header">
    当前位置：<span class="text-primary">商品管理</span>
</div>
<div class="page-content">
    <div class="fixed-header">
        <div style="width:25px;"></div>
        <div style="width:80px;text-align:center;">排序</div>
        <div style="width:80px;">商品</div>
        <div class="flex1">&nbsp;</div>
        <div style="width: 100px;">价格</div>
        <div style="width: 80px;">库存</div>
        <div style="width: 80px;">销量</div>
        <div style="width: 80px;">实际销量</div>
        <?php  if($goodsfrom!='cycle') { ?>
        <div  style="width:80px;" >状态</div>
        <?php  } ?>
        <div class="flex1">属性</div>
        <div  style="width:80px;" >供应链</div>
        <div style="width: 120px;">操作</div>
    </div>
    <form action="./index.php" method="get" class="form-horizontal form-search" role="form">
        <input type="hidden" name="c" value="site" />
        <input type="hidden" name="a" value="entry" />
        <input type="hidden" name="m" value="ewei_shopv2" />
        <input type="hidden" name="do" value="web" />
        <input type="hidden" name="r"  value="goods.<?php  echo $goodsfrom;?>" />
        <input type="hidden" name="goodscatsurl" value="<?php  echo webUrl('goods/goods_cat')?>">
        <div class="page-toolbar">
            <span style="margin-right:30px;">
                <?php if(cv('goods.add')) { ?>
                <button class="btn btn-sm btn-primary" data-toggle="ajaxModal" href="<?php  echo webUrl('util/cats_selector')?>">按照商品分类管理</button>
                <a class='btn btn-sm btn-primary' href="<?php  echo webUrl('goods/add')?>"><i class='fa fa-plus'></i> 添加商品</a>
                <?php  } ?>
                <?php if(cv('goods.add')) { ?>
                <a class='btn btn-sm btn-primary' href="<?php  echo webUrl('goods/create')?>"><i class='fa fa-plus'></i> 快速添加商品</a>
                <?php  } ?>

                <?php  if(p('taobao')) { ?>
                    <?php if(cv('taobao')) { ?>
                        <a class='btn btn-sm btn-primary' href="<?php  echo webUrl('taobao')?>"><i class='fa fa-plus'></i> 导入商品</a>
                    <?php  } ?>
                <?php  } ?>

            </span>
            <div class="input-group col-sm-6" style="margin-top: 6px">
                <span class="input-group-select" style="width: 1%;">
                    <span style="font-size: 12px">商品搜索：</span>
                    <select name="k_type" class='form-control select2' style="width:200px;" data-placeholder="商品名称">
                        <option value="1">商品名称</option>
                        <option value="2">SKU货号/id</option>
                        <option value="3">条形码</option>
                    </select>
                </span>
                <input type="text" class="input-sm form-control" name='keyword' value="<?php  echo $_GPC['keyword'];?>" placeholder="请输入">
            </div>
            <div class="input-group col-sm-9" style="margin-top: 6px">
                <span class="input-group-select">
                    <span style="font-size: 12px">商品分类：</span>
                    <div class="pick-area pick-area1" name=""></div>
                    <input type="hidden" name="cats">
                </span>
                <span class="input-group-select">
                    <span style="font-size: 12px">商品属性：</span>
                    <select name="attribute" class='form-control select2' style="width:200px;">
                        <option value="" <?php  if(empty($_GPC['attribute'])) { ?>selected<?php  } ?>>商品属性</option>
                        <option value="new" <?php  if($_GPC['attribute']=='new') { ?>selected<?php  } ?>>新品</option>
                        <option value="hot" <?php  if($_GPC['attribute']=='hot') { ?>selected<?php  } ?> >热卖</option>
                        <option value="recommand" <?php  if($_GPC['attribute']=='recommand') { ?>selected<?php  } ?> >推荐</option>
                        <option value="discount" <?php  if($_GPC['attribute']== 'discount') { ?>selected<?php  } ?> >促销</option>
                        <option value="sendfree" <?php  if($_GPC['attribute']== 'sendfree') { ?>selected<?php  } ?> >包邮</option>
                        <option value="time" <?php  if($_GPC['attribute'] == 'time') { ?>selected<?php  } ?> >限时卖</option>
                        <option value="nodiscount" <?php  if($_GPC['attribute'] == 'nodiscount') { ?>selected<?php  } ?> >不参与折扣</option>
                    </select>
                </span>
                <span class="input-group-select" style="margin-left: 10px">
                    <span style="font-size: 12px">贸易类型：</span>
                    <select name="goodsbusinesstype" class='form-control select2' style="width:200px;" data-placeholder="贸易类型">
                        <option value="0">全部</option>
                        <option value="1">保税区发货</option>
                        <option value="2">香港直邮</option>
                        <option value="4">海外直邮</option>
                        <option value="5">国内发货</option>
                    </select>
                </span>
                <span class="input-group-select" style="font-size: 12px">品牌：</span>
                <input type="text" class="input-sm form-control" name='brand' value="<?php  echo $_GPC['brand'];?>" placeholder="商品品牌">
            </div>
            <div class="col-sm-6" style="margin-top: 6px;margin-left: 135px;">
                <span class="input-group-btn">
                    <button class="btn btn-sm btn-primary" type="submit"> 搜索</button>&nbsp;
                    <a href="<?php  echo webUrl('goods')?>" class="btn btn-sm btn-primary"> 重置/刷新</a>
                    <button type="submit" value="1" name="export" class="btn btn-sm btn-success">导出商品</button>
                </span>
            </div>
        </div>
    </form>
    <?php  if(!empty($list) && count($list)>0 && cv('goods.main')) { ?>
    <div class="row">
        <div class="col-md-12">
            <div class="page-table-header">
                <input type='checkbox' />
                <div class="btn-group">
                    <?php if(cv('goods.edit')) { ?>
                    <?php  if($_GPC['goodsfrom']=='sale') { ?>
                    <button class="btn btn-default btn-sm  btn-operation" type="button" data-toggle='batch'  data-href="<?php  echo webUrl('goods/status',array('status'=>0))?>">
                        <i class='icow icow-xiajia3'></i> 下架
                    </button>
                    <?php  } ?>
                    <?php  if($_GPC['goodsfrom']=='stock') { ?>
                    <button class="btn btn-default btn-sm  btn-operation" type="button" data-toggle='batch' data-href="<?php  echo webUrl('goods/status',array('status'=>1))?>">
                        <i class='icow icow-shangjia2'></i> 上架
                    </button>
                    <?php  } ?>
                    <?php  } ?>
                    <?php  if($_GPC['goodsfrom']=='cycle') { ?>
                    <?php if(cv('goods.delete1')) { ?>
                    <button class="btn btn-default btn-sm  btn-operation" type="button" data-toggle='batch-remove' data-confirm="如果商品存在购买记录，会无法关联到商品, 确认要彻底删除吗?" data-href="<?php  echo webUrl('goods/delete1')?>">
                        <i class='icow icow-shanchu1'></i> 彻底删除</button>
                    <?php  } ?>
                    <?php if(cv('goods.restore')) { ?>
                    <button class="btn btn-default btn-sm  btn-operation" type="button" data-toggle='batch-remove' data-confirm="确认要恢复?" data-href="<?php  echo webUrl('goods/restore')?>">
                        <i class='icow icow-huifu1'></i> 恢复到仓库</button>
                    <?php  } ?>
                    <?php  } else { ?>
                    <?php if(cv('goods.delete')) { ?>
                    <button class="btn btn-default btn-sm  btn-operation" type="button" data-toggle='batch-remove' data-confirm="确认要删除吗?" data-href="<?php  echo webUrl('goods/delete')?>">
                        <i class='icow icow-shanchu1'></i> 删除</button>
                    <?php  } ?>
                    <?php  } ?>

                    <?php  if($_GPC['goodsfrom']=='verify') { ?>
                    <button class="btn btn-default btn-sm  btn-operation" type="button" data-toggle='batch-remove' data-confirm="确定要通过审核吗?" data-href="<?php  echo webUrl('goods/checked')?>">
                        <i class='icow icow-shenhetongguo'></i> 批量审核通过</button>
                    <?php  } ?>

                    <?php if(cv('goods.edit')) { ?>
                    <button class="btn btn-default btn-sm  btn-operation" type="button" data-toggle='batch-group'  id="batchcatesbut" >批量分类</button>
                    <?php  } ?>
                </div>
            </div>
            <table class="table table-responsive">
                <thead class="navbar-inner">
                <tr>
                    <th style="width:25px;"></th>
                    <th style="width:80px;text-align:center;">排序</th>
                    <th style="width:80px;">商品</th>
                    <th style="">&nbsp;</th>
                    <form action="" method="get" class="form-horizontal form-search" role="form" id="orderBy">
                        <input type="hidden" name="c" value="site" />
                        <input type="hidden" name="a" value="entry" />
                        <input type="hidden" name="m" value="ewei_shopv2" />
                        <input type="hidden" name="do" value="web" />
                        <input type="hidden" name="r"  value="goods.<?php  echo $goodsfrom;?>" />
                        <input type="hidden" name="orderby" value="" />
                        <th class="hover_sanj" style="width: 100px;">
                            <button type="submit" style="background: transparent;text-align: left;width: 100%;">价格
                                <span class="sorting">
                            <i class="icon icon-sanjiao2"></i>
                            <i class="icon icon-sanjiao1"></i>
                        </span>
                            </button>
                        </th>
                        <th class="hover_sank" style="width: 80px;">
                            <button type="submit" style="background: transparent;text-align: left;width: 100%;">库存
                                <span class="sorting">
                            <i class="icon icon-sanjiao2"></i>
                            <i class="icon icon-sanjiao1"></i>
                        </span></button>
                        </th>
                    </form>
                    <th style="width: 80px;">销量</th>
                    <th style="width: 80px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="不包含已申请维权订单">实际销量</th>
                    <?php  if($goodsfrom!='cycle') { ?>
                    <th  style="width:80px;" >状态</th>
                    <?php  } ?>
                    <th>属性</th>
                    <th>供应链</th>
                    <th style="width: 120px;">操作</th>
                </tr>

                </thead>
                <tbody>
                <?php  if(is_array($list)) { foreach($list as $item) { ?>
                <tr>
                    <td>
                        <input type='checkbox'  value="<?php  echo $item['id'];?>"/>
                    </td>
                    <td style='text-align:center;'>
                        <?php if(cv('goods.edit')) { ?>
                        <a href='javascript:;' data-toggle='ajaxEdit' data-href="<?php  echo webUrl('goods/change',array('type'=>'displayorder','id'=>$item['id']))?>" ><?php  echo $item['displayorder'];?></a>
                        <i class="icow icow-weibiaoti-- " data-toggle="ajaxEdit2"></i>
                        <?php  } else { ?>
                        <?php  echo $item['displayorder'];?>
                        <?php  } ?>
                    </td>
                    <td>
                        <a href="<?php  echo webUrl('goods/edit', array('id' => $item['id'],'goodsfrom'=>$goodsfrom,'page'=>$page))?>">
                            <img src="<?php  echo tomedia($item['thumb'])?>" style="width:72px;height:72px;padding:1px;border:1px solid #efefef;margin: 7px 0" onerror="this.src='../addons/ewei_shopv2/static/images/nopic.png'" />
                        </a>
                        <?php  if(!empty($item['newgoods'])) { ?>
                        <!--新-->
                        <div class="newgoodsflag">新</div>
                        <?php  } ?>

                    </td>
                    <td class='full' >
                        <span>
                            <span style="display: block;width: 100%;">
                            <?php if(cv('goods.edit')) { ?>
                                <a href='javascript:;' data-toggle='ajaxEdit' data-edit='textarea'  data-href="<?php  echo webUrl('goods/change',array('type'=>'title','id'=>$item['id']))?>" style="overflow : hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 2;-webkit-box-orient: vertical;">
                                    <?php  echo $item['title'];?>
                                </a>
                            <?php  } else { ?>
                                  <?php  echo $item['title'];?>
                            <?php  } ?>

                                <span class="catetag">
                                    <?php  if(is_array($item['allcates'])) { foreach($item['allcates'] as $index => $item1) { ?>
                                            <?php  if($category[$item['allcates'][$index]]['level'] ==1) { ?>
                                             <span class="text-danger">[<?php  echo $category[$item['allcates'][$index]]['name'];?>]</span>
                                            <?php  } else if($category[$item['allcates'][$index]]['level'] ==2) { ?>
                                               <span class="text-info">[<?php  echo $category[$item['allcates'][$index]]['name'];?>]</span>
                                             <?php  } else if($category[$item['allcates'][$index]]['level'] ==3 && intval($shopset['catlevel']==3)) { ?>
                                              <span class="text-info">[<?php  echo $category[$item['allcates'][$index]]['name'];?>]</span>
                                            <?php  } ?>
                                    <?php  } } ?>
                                    <?php  if(!empty($category[$item['pcate']])) { ?>
                                    <!--<span class="text-danger">[<?php  echo $category[$item['pcate']]['name'];?>]</span>-->
                                    <?php  } ?>
                                    <?php  if(!empty($category[$item['ccate']])) { ?>
                                    <!--<span class="text-info">[<?php  echo $category[$item['ccate']]['name'];?>]</span>-->
                                    <?php  } ?>
                                    <?php  if(!empty($category[$item['tcate']]) && intval($shopset['catlevel'])==3) { ?>
                                    <!--<span class="text-info">[<?php  echo $category[$item['tcate']]['name'];?>]</span>-->
                                    <?php  } ?>
                                </span>
                        </span>
                        <?php if(cv('goods.edit')) { ?>
                             <i class="icow icow-weibiaoti-- " data-toggle="ajaxEdit2"></i>
                        <?php  } ?>
                        </span>
                    </td>

                    <td>&yen;
                        <?php  if($item['hasoption']==1) { ?>
                        <?php if(cv('goods.edit')) { ?>
                        <span data-toggle='tooltip' title='多规格不支持快速修改'><?php  echo $item['marketprice'];?></span>
                        <?php  } else { ?>
                        <?php  echo $item['marketprice'];?>
                        <?php  } ?>
                        <?php  } else { ?>
                        <?php if(cv('goods.edit')) { ?>

                        <a href='javascript:;' data-toggle='ajaxEdit' data-href="<?php  echo webUrl('goods/change',array('type'=>'marketprice','id'=>$item['id']))?>" ><?php  echo $item['marketprice'];?></a>
                        <i class="icow icow-weibiaoti-- " data-toggle="ajaxEdit2"></i>
                        <?php  } else { ?>
                        <?php  echo $item['marketprice'];?>
                        <?php  } ?><?php  } ?>

                    </td>

                    <td>
                        <?php  if(!empty($item['hoteldaystock'])) { ?>
                        <span data-toggle='tooltip' title='民宿类商品显示每日库存'><?php  echo $item['hoteldaystock'];?>/日</span>
                        <?php  } else if($item['hasoption']==1) { ?>
                        <?php if(cv('goods.edit')) { ?>
                        <span data-toggle='tooltip' title='多规格不支持快速修改'>
                                <?php  if($item['total']<=$goodstotal) { ?><span class="text-danger"><?php  echo $item['total'];?></span><?php  } else { ?><?php  echo $item['total'];?><?php  } ?>
                            </span>
                        <?php  } else { ?>
                        <?php  if($item['total']<=$goodstotal) { ?><span class="text-danger"><?php  echo $item['total'];?></span><?php  } else { ?><?php  echo $item['total'];?><?php  } ?>
                        <?php  } ?>
                        <?php  } else { ?>
                        <?php if(cv('goods.edit')) { ?>
                        <a href='javascript:;' data-toggle='ajaxEdit' data-href="<?php  echo webUrl('goods/change',array('type'=>'total','id'=>$item['id']))?>" >
                            <?php  if($item['total']<=$goodstotal) { ?><span class="text-danger"><?php  echo $item['total'];?></span><?php  } else { ?><?php  echo $item['total'];?><?php  } ?>
                        </a>
                        <i class="icow icow-weibiaoti-- " data-toggle="ajaxEdit2"></i>
                        <?php  } else { ?>
                        <?php  if($item['total']<=$goodstotal) { ?><span class="text-danger"><?php  echo $item['total'];?></span><?php  } else { ?><?php  echo $item['total'];?><?php  } ?>
                        <?php  } ?>
                        <?php  } ?>
                    </td>
                    <td><?php  echo $item['salesreal'];?></td>
                    <td><?php echo !empty($item['sale_cpcount']) ? $item['sale_cpcount'] : 0;?></td>
                    <?php  if($goodsfrom!='cycle') { ?>
                    <td  style="overflow:visible;">
                        <?php  if($item['status']==2) { ?>
                        <span class="label label-danger">赠品</span>
                        <?php  } else { ?>
                        <span class='label <?php  if($item['status']==1) { ?>label-primary<?php  } else { ?>label-default<?php  } ?>'
                        <?php if(cv('goods.edit')) { ?>
                        data-toggle='ajaxSwitch'
                        data-confirm = "确认是<?php  if($item['status']==1) { ?>下架<?php  } else { ?>上架<?php  } ?>？"
                        data-switch-refresh='true'
                        data-switch-value='<?php  echo $item['status'];?>'
                        data-switch-value0='0|下架|label label-default|<?php  echo webUrl('goods/status',array('status'=>1,'id'=>$item['id']))?>'
                        data-switch-value1='1|上架|label label-success|<?php  echo webUrl('goods/status',array('status'=>0,'id'=>$item['id']))?>'
                        <?php  } ?>
                        >
                        <?php  if($item['status']==1) { ?>上架<?php  } else { ?>下架<?php  } ?></span>
                        <?php  } ?>
                        <?php  if(!empty($item['merchid'])) { ?>
                        <br>
                        <span class='label <?php  if($item['checked']==0) { ?>label-primary<?php  } else { ?>label-warning<?php  } ?>'
                        <?php if(cv('goods.edit')) { ?>
                        data-toggle='ajaxSwitch'
                        data-confirm = "确认是<?php  if($item['checked']==0) { ?>审核中<?php  } else { ?>审核通过<?php  } ?>？"
                        data-switch-refresh='true'
                        data-switch-value='<?php  echo $item['checked'];?>'
                        data-switch-value1='1|审核中|label label-warning|<?php  echo webUrl('goods/checked',array('checked'=>0,'id'=>$item['id']))?>'
                        data-switch-value0='0|通过|label label-success|<?php  echo webUrl('goods/checked',array('checked'=>1,'id'=>$item['id']))?>'
                        <?php  } ?>
                        >
                        <?php  if($item['checked']==0) { ?>通过<?php  } else { ?>审核中<?php  } ?></span>
                        <?php  } ?>
                    </td>
                    <?php  } ?>
                    <td  class="goods_attribute">
                        <a class='<?php  if($item['isnew']==1) { ?>text-danger<?php  } else { ?>text-default<?php  } ?>'
                        <?php if(cv('goods.property')) { ?>
                        data-toggle='ajaxSwitch'
                        data-switch-value='<?php  echo $item['isnew'];?>'
                        data-switch-value0='0||text-default|<?php  echo webUrl('goods/property',array('type'=>'new', 'data'=>1,'id'=>$item['id']))?>'
                        data-switch-value1='1||text-danger|<?php  echo webUrl('goods/property',array('type'=>'new','data'=>0,'id'=>$item['id']))?>'
                        <?php  } ?>
                        >新品</a>
                        <a class='<?php  if($item['ishot']==1) { ?>text-danger<?php  } else { ?>text-default<?php  } ?>'
                        <?php if(cv('goods.property')) { ?>
                        data-toggle='ajaxSwitch'
                        data-switch-value='<?php  echo $item['ishot'];?>'
                        data-switch-value0='0||text-default|<?php  echo webUrl('goods/property',array('type'=>'hot', 'data'=>1,'id'=>$item['id']))?>'
                        data-switch-value1='1||text-danger|<?php  echo webUrl('goods/property',array('type'=>'hot','data'=>0,'id'=>$item['id']))?>'
                        <?php  } ?>
                        >热卖</a>
                        <a class='<?php  if($item['isrecommand']==1) { ?>text-danger<?php  } else { ?>text-default<?php  } ?>'
                        <?php if(cv('goods.property')) { ?>
                        data-toggle='ajaxSwitch'
                        data-switch-value='<?php  echo $item['isrecommand'];?>'
                        data-switch-value0='0||text-default|<?php  echo webUrl('goods/property',array('type'=>'recommand', 'data'=>1,'id'=>$item['id']))?>'
                        data-switch-value1='1||text-danger|<?php  echo webUrl('goods/property',array('type'=>'recommand','data'=>0,'id'=>$item['id']))?>'
                        <?php  } ?>
                        >推荐</a>
                        <a class='<?php  if($item['isdiscount']==1) { ?>text-danger<?php  } else { ?>text-default<?php  } ?>'
                        <?php if(cv('goods.property')) { ?>
                        data-toggle='ajaxSwitch'
                        data-switch-value='<?php  echo $item['isdiscount'];?>'
                        data-switch-value0='0||text-default|<?php  echo webUrl('goods/property',array('type'=>'discount', 'data'=>1,'id'=>$item['id']))?>'
                        data-switch-value1='1||text-danger|<?php  echo webUrl('goods/property',array('type'=>'discount','data'=>0,'id'=>$item['id']))?>'
                        <?php  } ?>
                        >促销</a>
                        <a class='<?php  if($item['issendfree']==1) { ?>text-danger<?php  } else { ?>text-default<?php  } ?>'
                        <?php if(cv('goods.property')) { ?>
                        data-toggle='ajaxSwitch'
                        data-switch-value='<?php  echo $item['issendfree'];?>'
                        data-switch-value0='0||text-default|<?php  echo webUrl('goods/property',array('type'=>'sendfree', 'data'=>1,'id'=>$item['id']))?>'
                        data-switch-value1='1||text-danger|<?php  echo webUrl('goods/property',array('type'=>'sendfree','data'=>0,'id'=>$item['id']))?>'
                        <?php  } ?>
                        >包邮</a>
                        <a class='<?php  if($item['istime']==1) { ?>text-danger<?php  } else { ?>text-default<?php  } ?>'
                        <?php if(cv('goods.property')) { ?>
                        data-toggle='ajaxSwitch'
                        data-switch-value='<?php  echo $item['istime'];?>'
                        data-switch-value0='0||text-default|<?php  echo webUrl('goods/property',array('type'=>'time', 'data'=>1,'id'=>$item['id']))?>'
                        data-switch-value1='1||text-danger|<?php  echo webUrl('goods/property',array('type'=>'time','data'=>0,'id'=>$item['id']))?>'
                        <?php  } ?>
                        >限时卖</a>
                        <a class='<?php  if($item['isnodiscount']==1) { ?>text-danger<?php  } else { ?>text-default<?php  } ?>'
                        <?php if(cv('goods.property')) { ?>
                        data-toggle='ajaxSwitch'
                        <?php  if($item['merchid']>0) { ?>data-confirm='多商户的商品如果设置参与会员折扣，给多商户结算的时候是按照折扣前的价格结算，您确定要做修改？'<?php  } ?>
                        data-switch-value='<?php  echo $item['isnodiscount'];?>'
                        data-switch-value0='0||text-default|<?php  echo webUrl('goods/property',array('type'=>'nodiscount', 'data'=>1,'id'=>$item['id']))?>'
                        data-switch-value1='1||text-danger|<?php  echo webUrl('goods/property',array('type'=>'nodiscount','data'=>0,'id'=>$item['id']))?>'
                        <?php  } ?>
                        >不参与折扣</a>
                    </td>
                    <td style="overflow:visible;"><span class="label label-<?php  echo $item['supplychain_bg'];?>"><?php  echo $item['supplychain_type'];?></span></td>
                    <td  style="overflow:visible;position:relative">
                        <?php  if(empty($item['ishotel'])) { ?>
                        <?php if(cv('goods.edit|goods.view')) { ?>
                        <a  class='btn btn-op btn-operation' href="<?php  echo webUrl('goods/edit', array('id' => $item['id'],'goodsfrom'=>$goodsfrom,'page'=>$_GPC['page']))?>" >
                                         <span data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php if(cv('goods.edit')) { ?>编辑<?php  } else { ?>查看<?php  } ?>">
                                            <i class="icow icow-bianji2"></i>
                                         </span>
                        </a>
                        <?php  } ?>
                        <?php  } else { ?>
                        <?php if(cv('hotelreservation.goods.edit|hotelreservation.goods.view')) { ?>
                        <a  class='btn  btn-op btn-operation' href="<?php  echo webUrl('hotelreservation/goods/edit', array('id' => $item['id'],'goodsfrom'=>$goodsfrom,'page'=>$_GPC['page']))?>">
                                   <span data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php if(cv('goods.edit')) { ?>编辑<?php  } else { ?>查看<?php  } ?>">
                                       <?php if(cv('goods.edit')) { ?>
                                        <i class='icow icow-bianji2'></i>
                                       <?php  } else { ?>
                                        <i class='icow icow-chakan-copy'></i>
                                        <?php  } ?>
                                   </span>
                        </a>
                        <?php  } ?>
                        <?php  } ?>
                        <?php  if($_GPC['goodsfrom']=='cycle') { ?>
                        <?php if(cv('goods.restore')) { ?>
                        <a  class='btn  btn-op btn-operation' data-toggle='ajaxRemove' href="<?php  echo webUrl('goods/restore', array('id' => $item['id']))?>" data-confirm='确认要恢复?'>
                                     <span data-toggle="tooltip" data-placement="top" title="" data-original-title="恢复">
                                            <i class='icow icow-huifu1'></i>
                                       </span>
                        </a>
                        <?php  } ?>
                        <?php if(cv('goods.delete1')) { ?>
                        <a  class='btn  btn-op btn-operation' data-toggle='ajaxRemove' href="<?php  echo webUrl('goods/delete1', array('id' => $item['id']))?>" data-confirm='如果此商品存在购买记录，会无法关联到商品, 确认要彻底删除吗?？'>
                                    <span data-toggle="tooltip" data-placement="top" title="" data-original-title="彻底删除">
                                            <i class='icow icow-shanchu1'></i>
                                       </span>
                        </a>
                        <?php  } ?>
                        <?php  } else { ?>
                        <?php if(cv('goods.delete')) { ?>
                        <a  class='btn  btn-op btn-operation' data-toggle='ajaxRemove' href="<?php  echo webUrl('goods/delete', array('id' => $item['id']))?>" data-confirm='确认彻底删除此商品？'>
                                <span data-toggle="tooltip" data-placement="top" title="" data-original-title="删除">
                                     <i class='icow icow-shanchu1'></i>
                                </span>
                        </a>
                        <?php  } ?>
                        <?php  } ?>
                        <?php  if($_GPC['goodsfrom']!='cycle') { ?>
                        <a href="javascript:;" class='btn  btn-op btn-operation js-clip' data-url="<?php  echo mobileUrl('goods/detail', array('id' => $item['id']),true)?>">
                                 <span data-toggle="tooltip" data-placement="top"  data-original-title="复制链接">
                                       <i class='icow icow-lianjie2'></i>
                                   </span>
                        </a>
                        <a href="javascript:void(0);" class="btn  btn-op btn-operation" data-toggle="popover" data-trigger="hover" data-html="true"
                           data-content="<img src='<?php  echo $item['qrcode'];?>' width='130' alt='链接二维码'>" data-placement="auto right">
                            <i class="icow icow-erweima3"></i>
                        </a>
                        <?php  } ?>
                    </td>
                </tr>
                <?php  if(!empty($item['merchname']) && $item['merchid'] > 0) { ?>
                <tr style="background: #f9f9f9">
                    <td colspan='<?php  if($goodsfrom=='cycle') { ?>9<?php  } else { ?>10<?php  } ?>' style='text-align: left;border-top:none;padding:5px 0;' class='aops'>
                    <span class="text-default" style="margin-left: 10px;">商户名称：</span><span class="text-info"><?php  echo $item['merchname'];?></span>
                    </td>
                </tr>
                <?php  } ?>
                <?php  } } ?>
                </tbody>
                <tfoot>
                <tr>
                    <td><input type="checkbox"></td>
                    <td    <?php  if($goodsfrom!='cycle') { ?>colspan="4"<?php  } else { ?>colspan="3" <?php  } ?>>
                    <div class="btn-group">
                        <?php if(cv('goods.edit')) { ?>
                        <?php  if($_GPC['goodsfrom']=='sale') { ?>
                        <button class="btn btn-default btn-sm  btn-operation" type="button" data-toggle='batch'  data-href="<?php  echo webUrl('goods/status',array('status'=>0))?>">
                            <i class='icow icow-xiajia3'></i> 下架</button>
                        <?php  } ?>
                        <?php  if($_GPC['goodsfrom']=='stock') { ?>
                        <button class="btn btn-default btn-sm  btn-operation" type="button" data-toggle='batch' data-href="<?php  echo webUrl('goods/status',array('status'=>1))?>">
                            <i class='icow icow-shangjia2'></i> 上架</button>
                        <?php  } ?>
                        <?php  } ?>

                        <?php  if($_GPC['goodsfrom']=='cycle') { ?>
                        <?php if(cv('goods.delete1')) { ?>
                        <button class="btn btn-default btn-sm  btn-operation" type="button" data-toggle='batch-remove' data-confirm="如果商品存在购买记录，会无法关联到商品, 确认要彻底删除吗?" data-href="<?php  echo webUrl('goods/delete1')?>">
                            <i class='icow icow-shanchu1'></i> 彻底删除</button>
                        <?php  } ?>

                        <?php if(cv('goods.restore')) { ?>
                        <button class="btn btn-default btn-sm  btn-operation" type="button" data-toggle='batch-remove' data-confirm="确认要恢复?" data-href="<?php  echo webUrl('goods/restore')?>">
                            <i class='icow icow-huifu1'></i> 恢复到仓库</button>
                        <?php  } ?>

                        <?php  } else { ?>
                        <?php if(cv('goods.delete')) { ?>
                        <button class="btn btn-default btn-sm  btn-operation" type="button" data-toggle='batch-remove' data-confirm="确认要彻底删除吗?" data-href="<?php  echo webUrl('goods/delete1')?>">
                            <i class='icow icow-shanchu1'></i> 彻底删除</button>
                        <?php  } ?>
                        <?php  } ?>
                    </div>
                    </td>
                    <td colspan="7" style="text-align: left">
                        <?php  echo $pager;?>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php  } else { ?>
    <div class="panel panel-default">
        <div class="panel-body empty-data">暂时没有任何商品!</div>
    </div>
    <?php  } ?>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('goods/batchcates', TEMPLATE_INCLUDEPATH)) : (include template('goods/batchcates', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>
<script>
    //获得分类标签
    // var length = $('#catetag').children().length;
    // if (length >10){
    //     for (var i=2;i<length;i++)
    //     {
    //         $('#catetag').children().eq(i).hide();
    //     }
    //     $('#catetag').append('...等');
    // }
    //显示批量分类
    $('#batchcatesbut').click(function () {
        $('#batchcates').show();
    })

    //关闭批量分类
    $('.modal-header .close').click(function () {
        $('#batchcates').hide();
    })

    // 取消批量分类
    $('.modal-footer .btn.btn-default').click(function () {
        $('#batchcates').hide();
    })


    //确认
    $('.modal-footer .btn.btn-primary').click(function () {
        var selected_checkboxs = $('.table-responsive tbody tr td:first-child [type="checkbox"]:checked');
        var goodsids = selected_checkboxs.map(function () {
            return $(this).val()
        }).get();

        var cates=$('#cates').val();
        var iscover=$('input[name="iscover"]:checked').val();
        $.post(biz.url('goods/ajax_batchcates'),{'goodsids':goodsids,'cates': cates,'iscover':iscover}, function (ret) {
            if (ret.status == 1) {
                $('#batchcates').hide();
                tip.msgbox.suc('修改成功');
                window.location.reload();
                return
            } else {
                tip.msgbox.err('修改失败');
            }
        }, 'json');
    })

    $(document).on("click", '[data-toggle="ajaxEdit2"]',
        function (e) {
            var _this = $(this)
            $(this).addClass('hidden')
            var obj = $(this).parent().find('a'),
                url = obj.data('href') || obj.attr('href'),
                data = obj.data('set') || {},
                html = $.trim(obj.text()),
                required = obj.data('required') || true,
                edit = obj.data('edit') || 'input';
            var oldval = $.trim($(this).text());
            e.preventDefault();

            submit = function () {
                e.preventDefault();
                var val = $.trim(input.val());
                if (required) {
                    if (val == '') {
                        tip.msgbox.err(tip.lang.empty);
                        return;
                    }
                }
                if (val == html) {
                    input.remove(), obj.html(val).show();
                    //obj.closest('tr').find('.icow').css({visibility:'visible'})
                    return;
                }
                if (url) {
                    $.post(url, {
                        value: val
                    }, function (ret) {

                        ret = eval("(" + ret + ")");
                        if (ret.status == 1) {
                            obj.html(val).show();

                        } else {
                            tip.msgbox.err(ret.result.message, ret.result.url);
                        }
                        input.remove();
                    }).fail(function () {
                        input.remove(), tip.msgbox.err(tip.lang.exception);
                    });
                } else {
                    input.remove();
                    obj.html(val).show();
                }
                obj.trigger('valueChange', [val, oldval]);
            },
                obj.hide().html('<i class="fa fa-spinner fa-spin"></i>');
            var input = $('<input type="text" class="form-control input-sm" style="width: 80%;display: inline;" />');
            if (edit == 'textarea') {
                input = $('<textarea type="text" class="form-control" style="resize:none;" rows=3 width="100%" ></textarea>');
            }
            obj.after(input);

            input.val(html).select().blur(function () {
                submit(input);
                _this.removeClass('hidden')

            }).keypress(function (e) {
                if (e.which == 13) {
                    submit(input);
                    _this.removeClass('hidden')
                }
            });

        });
    // 页面排序进入取参orderby
    function orderBy(param) {
        var reg = new RegExp("(^|&)" + param + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null)
            return decodeURI(r[2]);
        return null;
    }
    console.log(orderBy('orderby'));
    if(orderBy('orderby') == 'price1') {
        $('.hover_sanj').find('i:eq(0)').addClass('color_red');
    }else if(orderBy('orderby') == 'price2') {
        $('.hover_sanj').find('i:eq(1)').addClass('color_red');
    }else if(orderBy('orderby') == 'total1') {
        $('.hover_sank').find('i:eq(0)').addClass('color_red');
    }else if(orderBy('orderby') == 'total2') {
        $('.hover_sank').find('i:eq(1)').addClass('color_red');
    }
    // 价格与库存点击排序
    $('.hover_sanj').on('click',function () {
        $('.hover_sank').find('i:eq(0)').removeClass('color_red').end().find('i:eq(1)').removeClass('color_red');
        var xiao = $(this).find('i:eq(0)').hasClass('color_red');
        var da = $(this).find('i:eq(1)').hasClass('color_red');
        if(!xiao && !da) {
            $(this).find('i:eq(0)').addClass('color_red');
            //价格倒叙
            $('input[name=orderby]').val('price1');
        }else if(xiao && !da) {
            $(this).find('i:eq(0)').removeClass('color_red').end().find('i:eq(1)').addClass('color_red');
            //价格正序
            $('input[name=orderby]').val('price2');
        }else if(!xiao && da) {
            $(this).find('i:eq(1)').removeClass('color_red').end().find('i:eq(0)').addClass('color_red');
            //价格倒叙
            $('input[name=orderby]').val('price1');
        }
    });

    $('.hover_sank').on('click',function () {
        $('.hover_sanj').find('i:eq(0)').removeClass('color_red').end().find('i:eq(1)').removeClass('color_red');
        var xiao = $(this).find('i:eq(0)').hasClass('color_red');
        var da = $(this).find('i:eq(1)').hasClass('color_red');
        if(!xiao && !da) {
            $(this).find('i:eq(0)').addClass('color_red');
            //库存倒叙
            $('input[name=orderby]').val('total1');
        }else if(xiao && !da) {
            $(this).find('i:eq(0)').removeClass('color_red').end().find('i:eq(1)').addClass('color_red');
            //库存正序
            $('input[name=orderby]').val('total2');
        }else if(!xiao && da) {
            $(this).find('i:eq(1)').removeClass('color_red').end().find('i:eq(0)').addClass('color_red');
            //库存倒叙
            $('input[name=orderby]').val('total1');
        }
    })
</script>
<script type="text/javascript">
    require(['<?php  echo EWEI_SHOPV2_LOCAL?>/plugin/merch/static/js/manage/pick-pcc.js'],function(){
        $(".pick-area1").pickArea({
            "format":"<?php echo empty($catsname)?'province/city/county':$catsname; ?>",
            "getVal":function(){
                $('input[name=cats]').val($(".pick-area-hidden").val());
            }
        });
    })
</script>
