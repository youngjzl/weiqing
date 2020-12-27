<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}
require EWEI_SHOPV2_PLUGIN . 'supplychain/core/inc/page_supplychain.php';

class Category_EweiShopV2Page extends SupplychainWebPage
{
	public function main()
	{
		global $_W;
		global $_GPC;

		if ($_W['ispost']) {
			if (!empty($_GPC['datas'])) {
				$datas = json_decode(html_entity_decode($_GPC['datas']), true);

				if (!is_array($datas)) {
					show_json(0, '分类保存失败，请重试!');
				}

				$cateids = array();
				$displayorder = count($datas);

				foreach ($datas as $row) {
					$cateids[] = $row['id'];
					pdo_update('ewei_shop_supplychain_category', array('parentid' => 0, 'displayorder' => $displayorder, 'level' => 1), array('id' => $row['id']));
					if ($row['children'] && is_array($row['children'])) {
						$displayorder_child = count($row['children']);

						foreach ($row['children'] as $child) {
							$cateids[] = $child['id'];
							pdo_query('update ' . tablename('ewei_shop_supplychain_category') . ' set  parentid=:parentid,displayorder=:displayorder,level=2 where id=:id', array(':displayorder' => $displayorder_child, ':parentid' => $row['id'], ':id' => $child['id']));
							--$displayorder_child;
							if ($child['children'] && is_array($child['children'])) {
								$displayorder_third = count($child['children']);

								foreach ($child['children'] as $third) {
									$cateids[] = $third['id'];
									pdo_query('update ' . tablename('ewei_shop_supplychain_category') . ' set  parentid=:parentid,displayorder=:displayorder,level=3 where id=:id', array(':displayorder' => $displayorder_third, ':parentid' => $child['id'], ':id' => $third['id']));
									--$displayorder_third;
									if ($third['children'] && is_array($third['children'])) {
										$displayorder_fourth = count($third['children']);

										foreach ($child['children'] as $fourth) {
											$cateids[] = $fourth['id'];
											pdo_query('update ' . tablename('ewei_shop_supplychain_category') . ' set  parentid=:parentid,displayorder=:displayorder,level=3 where id=:id', array(':displayorder' => $displayorder_third, ':parentid' => $third['id'], ':id' => $fourth['id']));
											--$displayorder_fourth;
										}
									}
								}
							}
						}
					}

					--$displayorder;
				}

				if (!empty($cateids)) {
					pdo_query('delete from ' . tablename('ewei_shop_supplychain_category') . ' where id not in (' . implode(',', $cateids) . ') and uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
				}

				plog('shop.category.edit', '批量修改分类的层级及排序');
				m('shop')->getCategory(true);
				m('shop')->getAllCategory(true);
				show_json(1, array('url' => webUrl('goods/category')));
			}
		}

		$children = array();
		$category = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_supplychain_category') . ' WHERE uniacid = \'' . $_W['uniacid'] . '\' AND supplychainid='.$_W['supplychainid'].' ORDER BY parentid ASC, displayorder DESC');

		foreach ($category as $index => $row) {
			if (!empty($row['parentid'])) {
				$children[$row['parentid']][] = $row;
				unset($category[$index]);
			}
		}

		include $this->template();
	}

	public function add()
	{
		$this->post();
	}

	public function edit()
	{
		$this->post();
	}

	protected function post()
	{
		global $_W;
		global $_GPC;
		$parentid = intval($_GPC['parentid']);
		$id = intval($_GPC['id']);

		if (!empty($id)) {
			$item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_supplychain_category') . ' WHERE id = \'' . $id . '\' AND supplychainid='.$_W['supplychainid'].'  limit 1');
			$parentid = $item['parentid'];
		}
		else {
			$item = array('displayorder' => 0);
		}

		if (!empty($parentid)) {
			$parent = pdo_fetch('SELECT id, parentid, name FROM ' . tablename('ewei_shop_supplychain_category') . ' WHERE id = \'' . $parentid . '\' AND supplychainid='.$_W['supplychainid'].' limit 1');

			if (empty($parent)) {
				$this->message('抱歉，上级分类不存在或是已经被删除！', webUrl('category/add'), 'error');
			}

			if (!empty($parent['parentid'])) {
				//前端页面用到
				$parent1 = pdo_fetch('SELECT id, name FROM ' . tablename('ewei_shop_supplychain_category') . ' WHERE id = \'' . $parent['parentid'] . '\' AND supplychainid='.$_W['supplychainid'].' limit 1');
			}
		}

		if (empty($parent)) {
			$level = 1;
		}
		else if (empty($parent['parentid'])) {
			$level = 2;
		}
		else {
			$level = 3;
		}

		if ($_W['ispost']) {
			$data = array(
				'uniacid' => $_W['uniacid'],
				'name' => trim($_GPC['catename']),
				'displayorder' => intval($_GPC['displayorder']),
				'parentid' => intval($parentid),
				'supplychainid'=>$_W['supplychainid'],
				'level' => $level
			);

			if (!empty($id)) {
				unset($data['parentid']);
				pdo_update('ewei_shop_supplychain_category', $data, array('id' => $id,'supplychainid'=>$_W['supplychainid']));
				load()->func('file');
				file_delete($_GPC['thumb_old']);
				plog('shop.category.edit', '修改分类 ID: ' . $id);
			}
			else {
				pdo_insert('ewei_shop_supplychain_category', $data);
				$id = pdo_insertid();
				plog('shop.category.add', '添加分类 ID: ' . $id);
			}
			show_json(1, array('url' => webUrl('goods/category')));
		}

		include $this->template();
	}

	public function delete()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		$item = pdo_fetch('SELECT id, name, parentid FROM ' . tablename('ewei_shop_supplychain_category') . ' WHERE id = \'' . $id . '\' AND supplychainid='.$_W['supplychainid'].' ');

		if (empty($item)) {
			$this->message('抱歉，分类不存在或是已经被删除！', webUrl('goods/category', array('op' => 'display')), 'error');
		}

		pdo_delete('ewei_shop_supplychain_category', array('id' => $id, 'parentid' => $id), 'OR');
		plog('shop.category.delete', '删除分类 ID: ' . $id . ' 分类名称: ' . $item['name'].'supplychain:'.$_W['supplychainid']);
		m('shop')->getCategory(true);
		show_json(1, array('url' => referer()));
	}

	public function enabled()
	{
		exit();
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$items = pdo_fetchall('SELECT id,name FROM ' . tablename('ewei_shop_supplychain_category') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

		foreach ($items as $item) {
			pdo_update('ewei_shop_supplychain_category', array('enabled' => intval($_GPC['enabled'])), array('id' => $item['id']));
			plog('shop.dispatch.edit', ('修改分类状态<br/>ID: ' . $item['id'] . '<br/>分类名称: ' . $item['name'] . '<br/>状态: ' . $_GPC['enabled']) == 1 ? '显示' : '隐藏');
		}

		m('shop')->getCategory(true);
		show_json(1, array('url' => referer()));
	}
}

?>