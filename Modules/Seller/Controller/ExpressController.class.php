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
use Seller\Model\ExpressModel;
class ExpressController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='发货设置';
			$this->breadcrumb2='快递管理';
	}
	
     public function index(){
     	
		$model=new ExpressModel();   
		
		$search = array();
		$search['store_id']  = array('in','0,'.SELLERUID);
		
		$data=$model->show_express_page($search);	
		$seller_express_relat = M('seller_express_relat')->where( array('store_id' => SELLERUID) )->select();
		
		$express_ids = array();
		foreach($seller_express_relat as $express)
		{
		    $express_ids[] = $express['express_id'];
		}
		
		foreach($data['list'] as $key => $val)
		{
		    $val['is_selected'] = 0;
		    if(!empty($express_ids) && in_array($val['id'], $express_ids)) 
		    {
		        $val['is_selected'] = 1;
		    }
		    $data['list'][$key] = $val;
		}
		$this->assign('seller_id',SELLERUID);
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		
    	$this->display();
	 }
    
	 function toggle_express_show()
	 {
	     $eid = intval(I('post.eid'));
	     $rel_ex = M('seller_express_relat')->where( array('store_id' => SELLERUID, 'express_id' => $eid) )->find();
	     if(empty($rel_ex)) 
	     {
	         $data = array();
	         $data['express_id'] = $eid;
	         $data['store_id'] = SELLERUID;
	         M('seller_express_relat')->add($data);
	     } else {
	         M('seller_express_relat')->where( array('store_id' => SELLERUID, 'express_id' => $eid) )->delete();
	     }
	     
	     echo json_encode( array('code' => 1) );
	     die();
	 }
	 
	function add(){
		
		if(IS_POST){
			
			$data=I('post.');
			$data['store_id'] = SELLERUID;
			$data['addtime'] = time();
			
			
			if( empty($data['express_name']) )
			{
				$return = array(
					'status'=>'fail',
					'message'=>'请填写快递名称',
					'jump'=>U('Express/index')
        		);
			}else{
				$res = M('seller_express')->add($data);	
			
				if($res) {
				   $return = array(
						'status'=>'success',
						'message'=>'新增成功',
						'jump'=>U('Express/index')
					);
				} else {
					$return = array(
						'status'=>'fail',
						'message'=>'新增失败',
						'jump'=>U('Express/index')
					);
				}
				
			}
			
			
			$this->osc_alert($return);
		}
		
		$this->crumbs='新增';		
		$this->action=U('Express/add');
		$this->display('edit');
	}

	function edit(){
		if(IS_POST){
		    
		    $data=I('post.');
			
			$data['addtime'] = time();
			
			$ck_info = M('seller_express')->where(array('id' =>$data['id'],'store_id' =>SELLERUID))->find();
			if(empty($ck_info)) {
				$return = array(
        			        'status'=>'fail',
        			        'message'=>'非法操作',
        			        'jump'=>U('Express/index')
        			    );
				$this->osc_alert($return);
			}
			$res = M('seller_express')->save($data);	
			
			if($res) {
			   $return = array(
        			        'status'=>'success',
        			        'message'=>'编辑成功',
        			        'jump'=>U('Express/index')
        			     );
			} else {
			    $return = array(
        			        'status'=>'fail',
        			        'message'=>'编辑失败',
        			        'jump'=>U('Express/index')
        			    );
			}		
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';		
		$this->action=U('Express/edit');
		$this->d=M('seller_express')->find(I('id'));
		$this->display('edit');		
	}

	 public function del(){
	     
	    $id = I('get.id', 0);
	    $res = M('seller_express')->where( array('id' => $id) )->delete();
	     
	    if($res) {
	        $return = array(
	            'status'=>'success',
	            'message'=>'删除成功',
	            'jump'=>U('Express/index')
	        );
	    } else {
	        $return = array(
	            'status'=>'fail',
	            'message'=>'删除失败',
	            'jump'=>U('Express/index')
	        );
	    }		
		$this->osc_alert($return); 	
	 }
	 
	 public function config()
	{
		
	    $_GPC = I('request.');
	    $this->gpc = $_GPC;
	   
	    $condition = '';
	    $pindex = max(1, intval($_GPC['page']));
	    $psize = 20;
	    
	   
	    
	    if (!empty($_GPC['keyword'])) {
	        $condition .= ' and name like "%'.$_GPC['keyword'].'%" ';
	    }
	    
	    $label = M()->query('SELECT id,name,simplecode,customer_name,customer_pwd FROM ' . C('DB_PREFIX') . "lionfish_comshop_express
						WHERE 1 " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
	    
		
		$total = M('lionfish_comshop_express')->where('1 ' . $condition)->count();
		
	    
	    $pager = pagination2($total, $pindex, $psize);
		
		
		$this->label = $label;
		$this->total = $total;
		$this->pager = $pager;
	    
		$this->display();
	}
	
	public function addexpress()
	{
		$_GPC = I('request.');
		
		if (IS_POST) {
			
			$data = $_GPC['data'];
			
			D('Seller/Express')->update($data);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$this->display();
	}
	
	public function editexpress()
	{
		$_GPC = I('request.');
		
		
		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$item = M('lionfish_comshop_express')->field('id,name,simplecode,customer_name,customer_pwd')->where( array('id' => $id) )->find();
			
			$this->item = $item;
		}	
		
		if (IS_POST) {
			
			$data = $_GPC['data'];
			
			D('Seller/Express')->update($data);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$this->display('Express/addexpress');
	}
	
	public function delexpress()
	{
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}
		
		$items = M('lionfish_comshop_express')->field('id,name')->where('id in( ' . $id . ' ) ')->select();			

		if (empty($item)) {
			$item = array();
		}

		foreach ($items as $item) {
			M('lionfish_comshop_express')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	
	public function localtownconfig()
	{
		$_GPC = I('request.');
		//供应商
		$supper_info = get_agent_logininfo();
		if (IS_POST) {
			$data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
			$localtown_confirm_delivery_distance = $data['localtown_confirm_delivery_distance'];
			if(!is_numeric($localtown_confirm_delivery_distance) || $localtown_confirm_delivery_distance < 10){
				$ret = '确认送达距离必须为数字，且大于10米';
				show_json(0, $ret);
			}
			$localtown_grabbing_distance = $data['localtown_grabbing_distance'];
			if(!is_numeric($localtown_grabbing_distance) || $localtown_grabbing_distance < 10){
				$ret = '配送员抢单距离必须为数字，且大于10米';
				show_json(0, $ret);
			}
			$localtown_expected_delivery_status = $data['localtown_expected_delivery_status'];
			$localtown_delivery_space_time = $data['localtown_delivery_space_time'];
			if($localtown_expected_delivery_status == 1){
				if(empty($localtown_delivery_space_time)  || floor($localtown_delivery_space_time)!=$localtown_delivery_space_time || $localtown_delivery_space_time < 15){
					$ret = '配送时间段间隔必须为整数，且不小于15';
					show_json(0, $ret);
				}
			}
			$localtown_business_hours_status = $data['localtown_business_hours_status'];
			if($localtown_business_hours_status == 1){
				$localtown_business_hours_begin = $data['localtown_business_hours_begin'];
				$localtown_business_hours_end = $data['localtown_business_hours_end'];
				if(empty($localtown_business_hours_begin) || empty($localtown_business_hours_end)){
					$ret = '营业时间段不能为空';
					show_json(0, $ret);
				}
				$hours_end_time = strtotime($localtown_business_hours_end);
				$hours_end_begin = strtotime($localtown_business_hours_begin);
				if($hours_end_time < $hours_end_begin){//结束时间小于开始时间
					$xc_time = $hours_end_time+24*60*60-$hours_end_begin;
					if($xc_time < 60*60){
						$ret = '营业时间段不能小于1个小时';
						show_json(0, $ret);
					}
				}else{
					$xc_time = $hours_end_time-$hours_end_begin;
					if($xc_time < 60*60){
						$ret = '营业时间段不能小于1个小时';
						show_json(0, $ret);
					}
				}
			}
			if(!empty($supper_info['id'])){
				D('Seller/SupplyConfig')->update($data);
			}else{
				D('Seller/Config')->update($data);
			}
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		if(!empty($supper_info['id'])){
			$data = D('Seller/SupplyConfig')->get_all_config();
			$this->is_supply = 1;
		}else{
			$data = D('Seller/Config')->get_all_config();
			$this->is_supply = 0;
		}

        if( isset($data['localtown_shop_province_id']) && $data['localtown_shop_province_id'] != '' )
        {
            $data['province_name'] =$data['localtown_shop_province_id'];
            $data['city_name'] = $data['localtown_shop_city_id'];
            $data['area_name'] = $data['localtown_shop_area_id'];
            $data['country_name'] = $data['localtown_shop_country_id'];
        }

		$this->data = $data;
		if(empty($supper_info['id'])){
			$this->display('Express/localtownconfig');
		}else{
			$this->display('Express/supply_localtownconfig');
		}
	}
	
	public function deconfig()
	{
		$_GPC = I('request.');
		
		if (IS_POST) {
			$data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
			
			$data['delivery_type_ziti'] = trim($data['delivery_type_ziti']);
			$data['delivery_type_express'] = $data['delivery_type_express'];
			
			$data['delivery_type_tuanz'] = $data['delivery_type_tuanz'];
			$data['delivery_tuanz_money'] = $data['delivery_tuanz_money'];
			$data['delivery_express_name'] = $data['delivery_express_name'];
			$data['delivery_diy_sort'] = $data['delivery_diy_sort'];
			
			$data['shopcar_tab_all_name'] = $data['shopcar_tab_all_name'];
			$data['shopcar_tab_express_name'] = $data['shopcar_tab_express_name'];
			$data['order_lou_meng_hao'] = $data['order_lou_meng_hao'];
			$data['order_lou_meng_hao_placeholder'] = $data['order_lou_meng_hao_placeholder'];
			
			
			D('Seller/Config')->update($data);
			
			if(empty($data['delivery_diy_sort']) || !isset($data['delivery_diy_sort'])) 
				$data['delivery_diy_sort'] = '0,1,2';
			
			$data['delivery_diy_sort_arr'] = explode(",", $data['delivery_diy_sort']);
		
		
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$data = D('Seller/Config')->get_all_config();
		
		if(empty($data['delivery_diy_sort']) || !isset($data['delivery_diy_sort'])) $data['delivery_diy_sort'] = '0,1,2';
		$data['delivery_diy_sort_arr'] = explode(",", $data['delivery_diy_sort']);
		
		$this->data = $data;
		$this->display();
	}
	
	
	
	 public function config2()
	 {
		 $open_info = M('config')->where( array('name' => 'EXPRESS_OPEN') )->find();
		 $ebuss_info = M('config')->where( array('name' => 'EXPRESS_EBUSS_ID') )->find();
		 $exappkey = M('config')->where( array('name' => 'EXPRESS_APPKEY') )->find();
		 
		$is_open = $open_info['value'];
		$ebuss_id = $ebuss_info['value'];
		$express_appkey = $exappkey['value'];
		 
		$this->is_open = $is_open;
		$this->ebuss_id = $ebuss_id;
		$this->express_appkey = $express_appkey;
		
		 $this->type = 1;
		 $this->display();
	 }
	 function configadd()
	 {
		 $data = I('post.');
		 /**
		 array(4) { ["is_open"]=> string(1) "1" ["ebuss_id"]=> string(7) "1276098" ["express_appkey"]=> string(36) "9933541f-2d17-4312-8250-a9cecdbe633d" ["send"]=> string(6) "提交" }
		 **/
		 M('config')->where( array('name' => 'EXPRESS_OPEN') )->save( array('value' => $data['is_open']) );
		 M('config')->where( array('name' => 'EXPRESS_EBUSS_ID') )->save( array('value' => $data['ebuss_id']) );
		 M('config')->where( array('name' => 'EXPRESS_APPKEY') )->save( array('value' => $data['express_appkey']) );
		 $return = array(
        			        'status'=>'success',
        			        'message'=>'保存成功',
        			        'jump'=>U('Express/config')
        			     );
		$this->osc_alert($return); 
	 }

	/**
	 * @author cy 2020-08-04
	 * 达达配送平台配置
	 */
	public function localtown_imdada_config(){
		$_GPC = I('request.');

		if (IS_POST) {
			$data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
			//达达第三方配送开启状态
			$data['is_localtown_imdada_status'] = trim($data['is_localtown_imdada_status']);
			//商户编号
			$data['localtown_imdada_merchant_id'] = $data['localtown_imdada_merchant_id'];
			//APPKEY
			$data['localtown_imdada_appkey'] = $data['localtown_imdada_appkey'];
			//AppSecret
			$data['localtown_imdada_appsecret'] = $data['localtown_imdada_appsecret'];

			D('Seller/Config')->update($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;
		$this->display();
	}

	/**
	 * @author cy 2020-08-04
	 * 顺丰同城配置
	 */
	public function localtown_sf_config(){
		$_GPC = I('request.');

		if (IS_POST) {
			$data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
			//顺丰同城开启状态
			$data['is_localtown_sf_status'] = trim($data['is_localtown_sf_status']);
			//开发者ID
			$data['localtown_sf_dev_id'] = $data['localtown_sf_dev_id'];
			//密钥
			$data['localtown_sf_dev_key'] = $data['localtown_sf_dev_key'];
			//顺丰店铺ID
			$data['localtown_sf_store_id'] = $data['localtown_sf_store_id'];

			D('Seller/Config')->update($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;
		$this->display();
	}
	
	    /**
     * @author yj 2021-01-14
     * 码科同城配置
     */
    public function localtown_mk_config(){
		$_GPC = I('request.');


        //IA_ROOT . '/addons/lionfish_comshop/
        //$addons_check_filepath = IA_ROOT."/addons/lionfish_comshop_plugin_make/module.php";

        //$addons_check_filepath = ROOT_PATH."/addons/lionfish_comshop_plugin_make/module.php";
        //$is_exist_plu = file_exists( $addons_check_filepath );
		$is_exist_plu =1;
		$this->is_exist_plu = $is_exist_plu;
		if (IS_POST) {
            $data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
            //码科同城开启状态
            $data['is_localtown_mk_status'] = trim($data['is_localtown_mk_status']);
            //码科
            $data['localtown_mk_token'] = $data['localtown_mk_token'];

            $last_code  = substr( $data['localtown_mk_url'] , -1 ,1);

            if( $last_code != '/' )
            {
                $data['localtown_mk_url'] = $data['localtown_mk_url'].'/';
            }
            D('Seller/Config')->update($data);

            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }

        $data = D('Seller/Config')->get_all_config();

		$this->data = $data;
        $this->display();
    }

	/**
	 * @author cy 2021-02-02
	 * @desc 蜂鸟即配配置
	 */
	public function localtown_ele_config(){
		$_GPC = I('request.');

		if (IS_POST) {
			$data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
			//蜂鸟即配开启状态
			$data['is_localtown_ele_status'] = $data['is_localtown_ele_status'];
			//商户APPID
			$data['localtown_ele_app_id'] = trim($data['localtown_ele_app_id']);
			//商户SecretKey
			$data['localtown_ele_secret_key'] = trim($data['localtown_ele_secret_key']);
			//门店编号
			$data['localtown_ele_store_code'] = trim($data['localtown_ele_store_code']);
			//门店名称
			$data['localtown_ele_transport_name'] = trim($data['localtown_ele_transport_name']);
			//取货点地址
			$data['localtown_ele_transport_address'] = trim($data['localtown_ele_transport_address']);
			//取货点经度
			$data['localtown_ele_transport_longitude'] = trim($data['localtown_ele_transport_longitude']);
			//取货点纬度
			$data['localtown_ele_transport_latitude'] = trim($data['localtown_ele_transport_latitude']);
			//取货点联系方式
			$data['localtown_ele_transport_tel'] = trim($data['localtown_ele_transport_tel']);
			//取货点经纬度来源
			$data['localtown_ele_position_source'] = trim($data['localtown_ele_position_source']);
			//取货点备注
			$data['localtown_ele_transport_remark'] = trim($data['localtown_ele_transport_remark']);

			if($data['is_localtown_ele_status'] == 1){
				if(empty($data['localtown_ele_app_id'])){
					show_json(0, '请正确填写商户APPID');
				}
				if(empty($data['localtown_ele_secret_key'])){
					show_json(0, '请正确填写商户SecretKey');
				}
				if(empty($data['localtown_ele_store_code'])){
					show_json(0, '请正确填写门店编号');
				}
				if(!ctype_alnum($data['localtown_ele_store_code'])){
					show_json(0, '门店编号必须是数字、字母的组合');
				}
				if(empty($data['localtown_ele_transport_name'])){
					show_json(0, '请正确填写门店名称');
				}
				if(empty($data['localtown_ele_transport_address'])){
					show_json(0, '请正确填写取货点地址');
				}
				if(empty($data['localtown_ele_transport_longitude'])){
					show_json(0, '请正确填写取货点经度');
				}
				if(empty($data['localtown_ele_transport_latitude'])){
					show_json(0, '请正确填写取货点纬度');
				}
				if(empty($data['localtown_ele_transport_tel'])){
					show_json(0, '请正确填写取货点联系方式');
				}
			}
			D('Seller/Config')->update($data);
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();
		$this->data = $data;
		$this->display();
	}
	 
}
?>