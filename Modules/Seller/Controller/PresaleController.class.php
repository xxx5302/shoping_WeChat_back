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
 * @author    fish
 *
 */
namespace Seller\Controller;

class PresaleController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();

	}


	public function index()
    {
        $_GET['goods_type'] = 'presale';

        $Goods_controller = A('Goods');

        $Goods_controller->index();

    }

    /**
     * @author yj
     * @desc 预售配置
     */
	public function config()
    {
        $_GPC = I('request.');

        if (IS_POST) {

            $data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));

            $data['isopen_presale'] = isset($data['isopen_presale']) ? $data['isopen_presale']:0;

            $data['presale_layout'] = isset($data['presale_layout']) ? $data['presale_layout']:0;
            $data['is_open_presale_show'] = isset($data['is_open_presale_show']) ? $data['is_open_presale_show']:0;
            $data['iscan_presale_dingrefund'] = isset($data['iscan_presale_dingrefund']) ? $data['iscan_presale_dingrefund']:0;
            $data['presale_name_modify'] = isset($data['presale_name_modify']) ? $data['presale_name_modify']:'';
            $data['presale_share_title'] = isset($data['presale_share_title']) ? $data['presale_share_title']:'';
            $data['presale_share_img'] = isset($data['presale_share_img']) ? $data['presale_share_img']:'';
            $data['presale_publish'] = isset($data['presale_publish']) ? $data['presale_publish']:'';

            D('Seller/Config')->update($data);

            show_json(1,  array('url' => $_SERVER['HTTP_REFERER']) );
            die();

        }

        $data = D('Seller/Config')->get_all_config();
        $this->data = $data;

        $this->display();
    }

    public function addgoods()
    {
        $_GET['goods_type'] = 'presale';

        $Goods_controller = A('Goods');

        $Goods_controller->addgoods();
    }
}
?>