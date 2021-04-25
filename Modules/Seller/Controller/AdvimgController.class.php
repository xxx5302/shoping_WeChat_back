<?php
/**
 * lionfish 商城系统
 *
 * ==========================================================================
 * @link      http://www.liofis.com/
 * @copyright Copyright (c) 2015 liofis.com. 
 * @license   http://www.liofis.com/license.html License
 * ==========================================================================
 *
 * @author    J_da
 *
 */
namespace Seller\Controller;

class AdvimgController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='广告图片';
		$this->breadcrumb2='广告列表';
		$this->sellerid = SELLERUID;
	}

	/**
	 * 广告列表
	 */
	public function index()
	{
		$_GPC = I('request.');
		
		$this->gpc = $_GPC;
        
		$condition = ' 1 ';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
        $time = $_GPC['time'];
		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and adv_name like "%'.$_GPC['keyword'].'%" ';
		}
		if (isset($_GPC['enabled']) && trim($_GPC['enabled']) != '') {
			$_GPC['enabled'] = trim($_GPC['enabled']);
			$condition .= ' and enabled = ' . $_GPC['enabled'];
		}

		$label = M('lionfish_comshop_advimg')->where( $condition )->order(' displayorder desc ')->limit( (($pindex - 1) * $psize) . ',' . $psize )->select();
		$total = M('lionfish_comshop_advimg')->where( $condition )->count();
		
		$pager = pagination2($total, $pindex, $psize);
		$this->label = $label;
		$this->pager = $pager;

		$this->display();
	}

	/**
	 * 添加广告
	 */
	public function add(){
		$_GPC = I('request.');

		if (IS_POST) {
			$data = $_GPC['data'];
			if(empty($data['thumb'])){
				show_json(0, array('message' => '广告图片不能为空'));
			}

			if(empty($data['link']) && $data['linktype']<3){
				show_json(0, array('message' => '广告链接不能为空'));
			}
            
			D('Seller/Advimg')->update($_GPC);

			show_json(1, array('url' => U('advimg/add')));
		}

		// $id = intval($_GPC['id']);
		$id = M('lionfish_comshop_advimg')->field('id')->order('id desc')->find();
		$item = '';
		if (!empty($id)) {
			$item = M('lionfish_comshop_advimg')->where( $id )->find();
			$item['pos'] = explode(',', $item['pos']);
		}

		$category = D('Seller/GoodsCategory')->getFullCategory(false, true);

		$this->category = $category;
		$this->item = $item;
		$this->display();
	}

	/**
	 * 更新弹窗广告状态
	 */
	public function change_status(){
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = $_GPC['ids'];
		}
		if( is_array($id) )
		{
			M('lionfish_comshop_advimg')->where( array('id' => array('in', $id)) )->save( array('enabled' => intval($_GPC['enabled'])) );
		}else{
			M('lionfish_comshop_advimg')->where( array('id' =>$id ) )->save( array('enabled' => intval($_GPC['enabled'])) );
		}
		show_json(1, array('url' => U('advimg/index')));
	}

	/**
	 * 删除弹窗广告
	 */
	public function delete(){
		$_GPC = I('request.');
		$id = intval($_GPC['id']);
		if (empty($id)) {
			$id = $_GPC['ids'];
		}
		if( is_array($id) )
		{
			M('lionfish_comshop_advimg')->where( array('id' => array('in', $id)) )->delete();
		}else{
			M('lionfish_comshop_advimg')->where( array('id' =>$id ) )->delete();
		}

		show_json(1, array('url' => U('advimg/index')));
	}
}
?>