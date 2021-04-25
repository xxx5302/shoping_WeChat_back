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
namespace Home\Controller;

class SupplymobileController extends CommonController {
	
	
	/**
		@title 供应商手机端主页接口
		@param token
	**/
	
	public function supplyindex_info()
	{
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$result = array();
				$result['supply_info'] = array( 
											'shopname' => $supply_info['shopname'],
											'logo' => tomedia( $supply_info['logo'] )
										);
				
				$goods_stock_notice = D('Home/Front')->get_config_by_name('goods_stock_notice');
				
				$notice_gods_count = 0;
				
				if( !empty($goods_stock_notice) && $goods_stock_notice > 0 )
				{
					//库存警告商品数量
					//supply_id 
					
					$sql = " select count(g.id) as count  from ".C('DB_PREFIX')."lionfish_comshop_goods as g left join ".C('DB_PREFIX')."lionfish_comshop_good_common as gc 
						on g.id = gc.goods_id where gc.supply_id = ".$supply_info['id']."  and g.total < {$goods_stock_notice} ";
					
					
					$sp_notice_count_arr = M()->query( $sql );
						
					$notice_gods_count = $sp_notice_count_arr[0]['count'];	
				}
				
				//待发货订单数量
				$wait_send_count = 0;
				//售后订单
				$wait_refund_count = 0;
				//今日订单
				$today_order_count = 0;
				//昨日订单数量
				$yestday_order_count = 0;
				//总订单数量
				$total_order_count = 0;
				
				//待结算金额
				$wait_statement_money = 0;
				//已结算金额
				$has_statement_money = 0;
				//已提现金额
				$has_get_money = 0;
				
				$wait_statement_money = M('lionfish_supply_commiss_order')->where( array('supply_id' => $supply_info['id'], 'state' => 0 ) )->sum('money');
				$has_statement_money = M('lionfish_supply_commiss_order')->where( array('supply_id' => $supply_info['id'], 'state' => 1 ) )->sum('money');
				
				if( empty($wait_statement_money) )
				{
					$wait_statement_money = 0;
				}
				if( empty($has_statement_money) )
				{
					$has_statement_money = 0;
				}
				
				//oscshop_
				$lionfish_supply_commiss = M('lionfish_supply_commiss')->where( array('supply_id' => $supply_info['id'] ) )->find();
				
				if( !empty($lionfish_supply_commiss) )	
				{
					$has_get_money = $lionfish_supply_commiss['getmoney'];
				}
				
				$order_ids_list_tmp = M('lionfish_comshop_order_goods')->field('order_id')->where( array('supply_id' => $supply_info['id'] ) )->select();								
											
				if( !empty($order_ids_list_tmp) )
				{
					$order_ids_tmp_arr = array();
					foreach($order_ids_list_tmp as  $vv)
					{
						if( empty($order_ids_tmp_arr) || !in_array( $vv['order_id'], $order_ids_tmp_arr ) )
						{
							$order_ids_tmp_arr[] = $vv['order_id'];
						}
					}
					$order_ids_tmp_str = implode(',', $order_ids_tmp_arr);
					
					$wait_send_count = M('lionfish_comshop_order')->where(' order_status_id=1 and '." order_id in({$order_ids_tmp_str}) ")->count();
					$wait_refund_count = M('lionfish_comshop_order')->where(' order_status_id=12 and '." order_id in({$order_ids_tmp_str}) ")->count();
					//今日订单
					$today_time = strtotime( date('Y-m-d '.'00:00:00') );
					
					$yesday_time = $today_time - 86400;
					
					$today_order_count = M('lionfish_comshop_order')->where(' date_added >='.$today_time.' and '." order_id in({$order_ids_tmp_str}) ")->count();
					
					$yestday_order_count = M('lionfish_comshop_order')->where(' date_added >='.$yesday_time.' and date_added <'.$today_time.' and '." order_id in({$order_ids_tmp_str}) ")->count();
					
					$total_order_count = count($order_ids_tmp_arr);
				}
				
				
				$result['wait_send_count'] = $wait_send_count;//待发货订单数量
				$result['wait_refund_count'] = $wait_refund_count;//售后订单数量
				$result['today_order_count'] = $today_order_count;//今日订单数量
				$result['yestday_order_count'] = $yestday_order_count;//昨日订单数量
				$result['total_order_count'] = $total_order_count;//总订单数量
				$result['wait_statement_money'] = $wait_statement_money;//待结算金额
				$result['has_statement_money'] = $has_statement_money;//已结算金额
				$result['has_get_money'] = $has_get_money;//已提现金额
			
				echo json_encode( array('code' => 0, 'data' =>$result ) );
				die();
				
			}else{
				echo json_encode( array('code' => 2,'msg' => '未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	/**
		供应商商品管理
	**/
	
	public function get_supply_goodslist()
	{
		
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$result = array();
				
				$goods_stock_notice = D('Home/Front')->get_config_by_name('goods_stock_notice');
				
				$goods_stock_notice = empty($goods_stock_notice) ? 0 : $goods_stock_notice;
				
				
				$page = isset($gpc['page']) && $gpc['page'] >0 ? intval($gpc['page']) : 1;
				
				$per_page = 10;
				
				$offset = ($page - 1) * $per_page;
				
				$where = "";
				
				$grounding = isset($gpc['grounding']) ? $gpc['grounding'] : 1;
				
				if( $grounding != 8 )
				{
					$where .= " and g.grounding = {$grounding} ";
				}else if( $grounding == 8 ){
					$where .= " and g.grounding = 1 and g.total<= {$goods_stock_notice} ";
				}
				
				$keywords = isset($gpc['keywords']) ? $gpc['keywords'] : '';
				
				if( !empty($keywords) )
				{
					$where .= " and  g.goodsname like '%{$keywords}%' ";
				}
				
				//出售中 1
				//已下架 0
				//待审核 4
				//库存警告 8
				
				$sql = " select g.id,g.goodsname,g.total,gc.big_img,g.hasoption  from ".C('DB_PREFIX')."lionfish_comshop_goods as g left join ".C('DB_PREFIX')."lionfish_comshop_good_common as gc 
						on g.id = gc.goods_id where gc.supply_id = ".$supply_info['id']." {$where}  order by g.id desc limit {$offset},{$per_page} ";
				
				
				$goods_list = M()->query( $sql );
					
				
				if( empty($goods_list) )
				{
					echo json_encode( array('code' =>0 , 'data' => array()) );
					die();
				}
				
				foreach( $goods_list as $key => $val )
				{
					$price_arr = D('Home/Pingoods')->get_goods_price($val['id'],$member_id);
					$price = $price_arr['price'];
					
					if( !empty($val['big_img']) )
					{
						$val['big_img'] = tomedia($val['big_img']);
					}
					
					$val['price'] = $price;
					
					$goods_list[$key] = $val;
				}
				
				
				
			
				echo json_encode( array('code' => 0, 'data' =>$goods_list ) );
				die();
				
			}else{
				echo json_encode( array('code' => 2,'msg'=>'未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	/**
		供应商商品管理---删除、实际放到回收站去
	**/
	function delete_supply_goods()
	{
		//grounding/value/3
		
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$goods_id = intval( $gpc['goods_id'] );
				
				$gd_info = M('lionfish_comshop_good_common')->where( array('supply_id' => $supply_info['id'], 'goods_id' => $goods_id  ) )->find();
				
				if( !empty($gd_info) )
				{
					$res = M('lionfish_comshop_goods')->where( array('id' =>$goods_id ) )->save( array('grounding' => 3) );
					
					echo json_encode( array('code' => 0) );
					die();
				}else{
					echo json_encode( array('code' => 2, 'msg'=>'非法操作') );
					die();
				}
				
			}else{
				echo json_encode( array('code' => 2,'msg'=>'未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	/**
		供应商 修改商品的库存（包含多规格）
	**/
	
	function modify_supply_goods_quantity()
	{
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$goods_id = intval( $gpc['goods_id'] );
				$sku_list_str  = $gpc['sku_list_str'];
				$is_has_option = $gpc['is_has_option'];
				$quantity = isset($gpc['quantity']) ? $gpc['quantity'] : 0;
				
				if( !isset($is_has_option) || empty($is_has_option)  )
				{
					$is_has_option = 0;//无修改规格的库存
				}
				
				$gd_info = M('lionfish_comshop_good_common')->where( array('supply_id' => $supply_info['id'], 'goods_id' => $goods_id  ) )->find();
				
				if( !empty($gd_info) )
				{
				    $supply_edit_goods_shenhe = D('Home/Front')->get_config_by_name('supply_edit_goods_shenhe');
				    if( !isset($supply_edit_goods_shenhe) || $supply_edit_goods_shenhe == 0 )
				    {
				        $supply_edit_goods_shenhe = 0;
				    }
				    
					//$total 
					if( $is_has_option == 1 )
					{
						//sku_list_str    skuid_stock,skuid_stock,skuid_stock
						$sku_list_arr = explode(',',$sku_list_str);
						if( empty($sku_list_arr) )
						{
							echo json_encode( array('code' => 2, 'msg'=>'非法操作') );
							die();
						}
						M()->startTrans();
						
						$total = 0;
						$flag = 1;
						
						foreach($sku_list_arr as $val)
						{
							$sku_arr = explode('_', $val);
							
							$sku_id = $sku_arr[0];
							$sku_quantity = $sku_arr[1] >=0 ? $sku_arr[1] : 0;
							
							
							$sku_info = M('lionfish_comshop_goods_option_item_value')->where( array('id' => $sku_id, 'goods_id' => $goods_id) )->find();
							
							if( empty($sku_info) )
							{
								$flag = 0;
								break;
							}
							
							M('lionfish_comshop_goods_option_item_value')->where( array('id' => $sku_id, 'goods_id' => $goods_id) )->save(  array('stock' =>$sku_quantity ) );
							
							$total += $sku_quantity;
							
						}
						
						if($flag == 1)
						{
							M()->commit();
							
							$up_goods_data = array();
							$up_goods_data['total'] = $total;
							if($supply_edit_goods_shenhe == 1)
							{
							    $up_goods_data['grounding'] = 4;
							}
							M('lionfish_comshop_goods')->where( array('id' => $goods_id) )->save( $up_goods_data );
							
						}else if($flag == 0){
							M()->rollback();
						}
						
					}else{
						$up_goods_data = array();
						$up_goods_data['total'] = $quantity;
						if($supply_edit_goods_shenhe == 1)
						{
						    $up_goods_data['grounding'] = 4;
						}
						M('lionfish_comshop_goods')->where( array('id' => $goods_id) )->save( $up_goods_data );
					}
					
					D('Seller/Redisorder')->sysnc_goods_total($goods_id);
					
					echo json_encode( array('code' => 0 ) );
					die();
				}else{
					echo json_encode( array('code' => 2, 'msg'=>'非法操作') );
					die();
				}
				
			}else{
				echo json_encode( array('code' => 2,'msg'=>'未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
	}
	
	
	/**
		供应商 获取商品规格
	**/
	
	function get_supply_goods_sku()
	{
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$goods_id = intval( $gpc['goods_id'] );
				
				$option_item_arr = M('lionfish_comshop_goods_option_item_value')->where( array('goods_id' => $goods_id ) )->order('id asc')->select();
				
				$goods_stock_notice = D('Home/Front')->get_config_by_name('goods_stock_notice');
				
				if( empty($goods_stock_notice) )
				{
					$goods_stock_notice = 0;
				}
				
				if( !empty($option_item_arr) )
				{
					$need_data = array();
					
					foreach( $option_item_arr as $val )
					{
						$tmp_arr = array();
						$tmp_arr['id'] = $val['id'];
						$tmp_arr['goods_id'] = $val['goods_id'];
						$tmp_arr['stock'] = $val['stock'];
						$tmp_arr['title'] = $val['title'];
						
						$need_data[] = $tmp_arr;
					}
					
					
					echo json_encode( array('code' => 0, 'need_data' => $need_data, 'goods_stock_notice' => $goods_stock_notice ) );
					die();
				}else{
					echo json_encode( array('code' => 2, 'msg'=>'非法操作') );
					die();
				}
				
			}else{
				echo json_encode( array('code' => 2,'msg'=>'未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
	}
	
	/**
		供应商商品管理---下架
	**/
	function down_supply_goods()
	{
		//grounding/value/3
		
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			
			$supply_can_goods_updown = D('Home/Front')->get_config_by_name('supply_can_goods_updown');
			if( !isset($supply_can_goods_updown) || $supply_can_goods_updown == 2 )
			{
				echo json_encode( array('code' => 2, 'msg'=>'供应商暂无权限下架商品') );
				die();
			}
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$goods_id = intval( $gpc['goods_id'] );
				
				$gd_info = M('lionfish_comshop_good_common')->where( array('supply_id' => $supply_info['id'], 'goods_id' => $goods_id  ) )->find();
				
				if( !empty($gd_info) )
				{
					$res = M('lionfish_comshop_goods')->where( array('id' =>$goods_id ) )->save( array('grounding' => 0 ) );
					
					echo json_encode( array('code' => 0) );
					die();
				}else{
					echo json_encode( array('code' => 2, 'msg'=>'非法操作') );
					die();
				}
				
			}else{
				echo json_encode( array('code' => 2,'msg'=>'未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	/**
		供应商  团长订单确认配送
	**/
	public function supply_do_opsend_tuanz()
	{
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$order_id = isset($gpc['order_id']) ? intval($gpc['order_id']) : 0;
		
		if( $order_id <= 0 )
		{
			echo json_encode( array('code' => 2,'msg' => '非法订单号') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			$supply_can_confirm_delivery = D('Home/Front')->get_config_by_name('supply_can_confirm_delivery');
			if( !isset($supply_can_confirm_delivery) || $supply_can_confirm_delivery == 2 )
			{
				echo json_encode( array('code' => 2, 'msg'=>'供应商暂无权限确认配送（确认送达团长）') );
				die();
			}
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$order_info = M('lionfish_comshop_order')->where( array('order_id' =>  $order_id ) )->find();
				
				if( empty($order_info) )
				{
					echo json_encode( array('code' => 2,'msg' => '非法订单号') );
					die();
				}
				//
				//order_status_id == 1  delivery   opsend_tuanz_over  
				
				if( $order_info['order_status_id'] == 1 &&  in_array( $order_info['delivery'], array('pickup', 'tuanz_send') )  )
				{
					$order_goods = M('lionfish_comshop_order_goods')->where(  array('order_id' => $order_id, 'supply_id' => $supply_info['id']  ) )->find();
					
					if( empty($order_goods) )
					{
						echo json_encode( array('code' => 2,'msg' => '无此订单操作权限') );
						die();
					}
					
					D('Seller/Order')->do_send_tuanz($order_id,'供应商小程序端发货');
					$order_num_alias = M('lionfish_comshop_order')->where(  array('order_id' => $order_id ) )->find();
					D('Seller/Operatelog')->addOperateLog('detailed_list','供应商小程序端发货--订单编号'.$order_num_alias['order_num_alias']);
					
					//后台自动送达团长
					D('Seller/Order')->order_auto_service($order_info,'后台自动确认送达团长');

					echo json_encode( array('code' => 0, 'msg' => '发货成功') );
					
					die();
				}else{
					echo json_encode( array('code' => 2,'msg' => '订单状态不可发货') );
					die();
				}
				
			}else{
				echo json_encode( array('code' => 2,'msg'=>'未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	

	/**
		供应商  团长订单确认送达团长
	**/
	public function supply_do_tuanz_over()
	{
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$order_id = isset($gpc['order_id']) ? intval($gpc['order_id']) : 0;
		
		if( $order_id <= 0 )
		{
			echo json_encode( array('code' => 2,'msg' => '非法订单号') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
		    $supply_can_confirm_delivery = D('Home/Front')->get_config_by_name('supply_can_confirm_delivery');
			if( !isset($supply_can_confirm_delivery) || $supply_can_confirm_delivery == 2 )
			{
				echo json_encode( array('code' => 2, 'msg'=>'供应商暂无权限确认配送（确认送达团长）') );
				die();
			}
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$order_info = M('lionfish_comshop_order')->where( array('order_id' =>  $order_id ) )->find();
				
				if( empty($order_info) )
				{
					echo json_encode( array('code' => 2,'msg' => '非法订单号') );
					die();
				}
				//
				//order_status_id == 1  delivery   opsend_tuanz_over  
				
				if( $order_info['order_status_id'] == 14 &&  in_array( $order_info['delivery'], array('pickup', 'tuanz_send') )  )
				{
					$order_goods = M('lionfish_comshop_order_goods')->where(  array('order_id' => $order_id, 'supply_id' => $supply_info['id']  ) )->find();
					
					if( empty($order_goods) )
					{
						echo json_encode( array('code' => 2,'msg' => '无此订单操作权限') );
						die();
					}
					
					D('Seller/Order')->do_tuanz_over($order_id, '供应商小程序端,确认送达团长');
					
					D('Home/Frontorder')->send_order_operate($order_id);
					
					echo json_encode( array('code' => 0, 'msg' => '送达团长成功') );
					
					die();
				}else{
					echo json_encode( array('code' => 2,'msg' => '订单状态不可发货') );
					die();
				}
				
			}else{
				echo json_encode( array('code' => 2,'msg'=>'未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	
	/**
		供应商  团长订单确认收货
	**/
	public function supply_do_opreceive()
	{
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$order_id = isset($gpc['order_id']) ? intval($gpc['order_id']) : 0;
		
		if( $order_id <= 0 )
		{
			echo json_encode( array('code' => 2,'msg' => '非法订单号') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			$supply_can_confirm_receipt = D('Home/Front')->get_config_by_name('supply_can_confirm_receipt');
			if( !isset($supply_can_confirm_receipt) || $supply_can_confirm_receipt == 2 )
			{
				echo json_encode( array('code' => 2, 'msg'=>'供应商暂无权限确认收货（已发货待收货）') );
				die();
			}
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$order_info = M('lionfish_comshop_order')->where( array('order_id' =>  $order_id ) )->find();
				
				if( empty($order_info) )
				{
					echo json_encode( array('code' => 2,'msg' => '非法订单号') );
					die();
				}
				//
				//order_status_id == 1  delivery   opsend_tuanz_over  
				
				if( $order_info['order_status_id'] == 4   )
				{
					$order_goods = M('lionfish_comshop_order_goods')->where(  array('order_id' => $order_id, 'supply_id' => $supply_info['id']  ) )->find();
					
					if( empty($order_goods) )
					{
						echo json_encode( array('code' => 2,'msg' => '无此订单操作权限') );
						die();
					}
					
					D('Seller/Order')->receive_order($order_id);
					
					M('lionfish_comshop_order_history')->where( array('order_id' => $order_id ,'order_status_id' => 6) )->save( array( 'comment' => '供应商小程序端，确认收货') );
		
					
					echo json_encode( array('code' => 0, 'msg' => '确认收货成功') );
					
					die();
				}else{
					echo json_encode( array('code' => 2,'msg' => '订单状态不可发货') );
					die();
				}
				
			}else{
				echo json_encode( array('code' => 2,'msg'=>'未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	
	
	
	/**
		供应商  商品上下架
	**/
	function up_supply_goods()
	{
		//grounding/value/3
		
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			
			$supply_can_goods_updown = D('Home/Front')->get_config_by_name('supply_can_goods_updown');
			if( !isset($supply_can_goods_updown) || $supply_can_goods_updown == 2)
			{
				echo json_encode( array('code' => 2, 'msg'=>'供应商暂无权限上架商品') );
				die();
			}
			
			
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$goods_id = intval( $gpc['goods_id'] );
				
				$supply_add_goods_shenhe = D('Home/Front')->get_config_by_name('supply_add_goods_shenhe');
					
				if($supply_add_goods_shenhe == 1 )
				{

					M('lionfish_comshop_goods')->where( array('id' => $goods_id) )->save(  array('grounding' => 4) );		
					echo json_encode( array('code' => 2, 'msg'=>'供应商上架商品需等待审核') );
					die();
				}
				
				
				$gd_info = M('lionfish_comshop_good_common')->where( array('supply_id' => $supply_info['id'], 'goods_id' => $goods_id  ) )->find();
				
				if( !empty($gd_info) )
				{
					$res = M('lionfish_comshop_goods')->where( array('id' =>$goods_id ) )->save( array('grounding' => 1 ) );
					
					echo json_encode( array('code' => 0) );
					die();
				}else{
					echo json_encode( array('code' => 2, 'msg'=>'非法操作') );
					die();
				}
				
			}else{
				echo json_encode( array('code' => 2,'msg'=>'未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	/**
		供应商  快递订单点击确认发货，获取物流公司 以便填写单号
	**/
	
	public function get_express_list()
	{
		
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$order_id  = $gpc['order_id'];
				
				$order_info = M('lionfish_comshop_order')->where( array('order_id' =>  $order_id ) )->find();
				
				if( empty($order_info) )
				{
					echo json_encode( array('code' => 2,'msg' => '非法订单号') );
					die();
				}
				//
				//order_status_id == 1  delivery   opsend_tuanz_over  
				
				if( $order_info['order_status_id'] == 1 &&  in_array( $order_info['delivery'], array('express') )  )
				{
					$order_goods = M('lionfish_comshop_order_goods')->where(  array('order_id' => $order_id, 'supply_id' => $supply_info['id']  ) )->find();
					
					if( empty($order_goods) )
					{
						echo json_encode( array('code' => 2,'msg' => '无此订单操作权限') );
						die();
					}
					
					$province_info = D('Home/Front')->get_area_info($item['shipping_province_id']);
					$city_info = D('Home/Front')->get_area_info($item['shipping_city_id']);
					$area_info = D('Home/Front')->get_area_info($item['shipping_country_id']);
					
					$express_list = D('Seller/Express')->load_all_express();
					
					
					$data = array();
					$data['shipping_name'] = $order_info['shipping_name'];
					$data['shipping_tel'] = $order_info['shipping_tel'];
					$data['address'] = $province_info['name'].$city_info['name'].$area_info['name'].$item['shipping_address'];
					
					$data['express_list'] = $express_list;
					
					
					echo json_encode( array('code' => 0, 'data' => $data ) );
					
					die();
				}else{
					echo json_encode( array('code' => 2,'msg' => '订单状态不可发货') );
					die();
				}
				
			}else{
				echo json_encode( array('code' => 2,'msg'=>'未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	
	/**
		供应商  快递订单 确认发货
	**/
	public function do_send_order_express()
	{
		
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$order_id  = $gpc['order_id'];
				
				$order_info = M('lionfish_comshop_order')->where( array('order_id' =>  $order_id ) )->find();
				
				if( empty($order_info) )
				{
					echo json_encode( array('code' => 2,'msg' => '非法订单号') );
					die();
				}
				
				$express_id = $gpc['express_id']; //快递公司id
				$shipping_no = $gpc['shipping_no']; //快递单号
				
				if( empty($express_id) )
				{
					echo json_encode( array('code' => 2, 'msg' => '请选择快递公司') );
					die();
				}
				if( empty($shipping_no) )
				{
					echo json_encode( array('code' => 2, 'msg' => '请填写快递编号') );
					die();
				}
				
				
				if( $order_info['order_status_id'] == 1 &&  in_array( $order_info['delivery'], array('express') )  )
				{
					$order_goods = M('lionfish_comshop_order_goods')->where(  array('order_id' => $order_id, 'supply_id' => $supply_info['id']  ) )->find();
					
					if( empty($order_goods) )
					{
						echo json_encode( array('code' => 2,'msg' => '无此订单操作权限') );
						die();
					}
					
					
					$express_info = D('Seller/Express')->get_express_info($express_id);
			
					$time = time();
					$data = array(
						'shipping_method' => trim($express_id), 
						'shipping_no' => trim($shipping_no), 
						'dispatchname' => $express_info['name'], 
						'express_time' => $time
					);
					
					$data['order_status_id'] = 4;
					
					
					M('lionfish_comshop_order')->where( array('order_id' => $order_id) )->save( $data );
					
					
				
					$history_data = array();
					$history_data['order_id'] = $order_info['order_id'];
					$history_data['order_status_id'] = 4;
					$history_data['notify'] = 0;
					$history_data['comment'] = '供应商前台：订单发货 ID: ' . $order_id . ' 订单号: ' . $order_info['order_num_alias'] . ' <br/>快递公司: ' . $express_info['name'] . ' 快递单号: ' . $shipping_no;
					$history_data['date_added'] = time();
					
					M('lionfish_comshop_order_history')->add($history_data);
						
					D('Home/Frontorder')->send_order_operate($order_id);
					
					
					echo json_encode( array('code' => 0, 'data' => $data ) );
					
					die();
				}else{
					echo json_encode( array('code' => 2,'msg' => '订单状态不可发货') );
					die();
				}
				
			}else{
				echo json_encode( array('code' => 2,'msg'=>'未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	/**
		供应商  资金管理 页面
	**/
	
	public function supply_managemoney_panel()
	{
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				//$supply_info['id']
				$supply_id = $supply_info['id'];
				
				
				$supply_min_apply_money = D('Home/Front')->get_config_by_name('supply_min_money');
		
				if( empty($supply_min_apply_money) )
				{
					$supply_min_apply_money = 0;
				}
				
				$supply_commiss = M('lionfish_supply_commiss')->where( array('supply_id' => $supply_id ) )->find();
				
				$last_tixian_order = array('bankname' =>'微信','bankaccount' => '','bankusername' => '' ,'supply_apply_type' => -1 );
		
				$lionfish_supply_tixian_order = M('lionfish_supply_tixian_order')->where( array('supply_id' => $supply_id ) )->order('id desc')->find();
				
				if( !empty($lionfish_supply_tixian_order) )
				{
					$last_tixian_order['bankname'] = $lionfish_supply_tixian_order['bankname'];
					$last_tixian_order['bankaccount'] = $lionfish_supply_tixian_order['bankaccount'];
					$last_tixian_order['bankusername'] = $lionfish_supply_tixian_order['bankusername'];
					$last_tixian_order['supply_apply_type'] = $lionfish_supply_tixian_order['supply_apply_type'];
				}
				
				$supply_commiss_tixianway_weixin = D('Home/Front')->get_config_by_name('supply_commiss_tixianway_weixin');
				$supply_commiss_tixianway_alipay = D('Home/Front')->get_config_by_name('supply_commiss_tixianway_alipay');
				$supply_commiss_tixianway_bank   = D('Home/Front')->get_config_by_name('supply_commiss_tixianway_bank');
				$supply_commiss_tixianway_weixin_offline   = D('Home/Front')->get_config_by_name('supply_commiss_tixianway_weixin_offline');
			
				if( isset($supply_commiss_tixianway_weixin) && $supply_commiss_tixianway_weixin == 1 )
				{
					$supply_commiss_tixianway_weixin = 0;
				}else{
					$supply_commiss_tixianway_weixin = 1;
				}
				
				if( isset($supply_commiss_tixianway_alipay) && $supply_commiss_tixianway_alipay == 1 )
				{
					$supply_commiss_tixianway_alipay = 0;
				}else{
					$supply_commiss_tixianway_alipay = 1;
				}
				
				if( isset($supply_commiss_tixianway_bank) && $supply_commiss_tixianway_bank == 1 )
				{
					$supply_commiss_tixianway_bank = 0;
				}else{
					$supply_commiss_tixianway_bank = 1;
				}
				
				if( isset($supply_commiss_tixianway_weixin_offline) && $supply_commiss_tixianway_weixin_offline == 1 )
				{
					$supply_commiss_tixianway_weixin_offline = 0;
				}else{
					$supply_commiss_tixianway_weixin_offline = 1;
				}
				
				$tixian_waylist = array();
				
				$tixian_waylist[1] = array( 'name' => '微信零钱', 'is_show' => 0, 'is_default_check' => 0 );
				$tixian_waylist[2] = array( 'name' => '支付宝', 'is_show' => 0, 'is_default_check' => 0 );
				$tixian_waylist[3] = array( 'name' => '银行卡', 'is_show' => 0, 'is_default_check' => 0 );
				$tixian_waylist[4] = array( 'name' => '微信私下转', 'is_show' => 0, 'is_default_check' => 0 );
				
				if( $supply_commiss_tixianway_weixin == 1 )
				{
					$tixian_waylist[1]['is_show'] = 1;
					
					if( $last_tixian_order['bankname'] == '微信' )
					{
						$tixian_waylist[1]['is_default_check'] = 1;
					}
				}
				
				if( $supply_commiss_tixianway_alipay == 1 )
				{
					$tixian_waylist[2]['is_show'] = 1;
					
					if( $last_tixian_order['bankname'] == '支付宝' )
					{
						$tixian_waylist[2]['is_default_check'] = 1;
					}
				}
				
				if( $supply_commiss_tixianway_bank == 1 )
				{
					$tixian_waylist[3]['is_show'] = 1;
					
					if( $last_tixian_order['bankname'] != '微信' && $last_tixian_order['bankname'] != '支付宝' && $last_tixian_order['supply_apply_type'] != 4 )
					{
						$tixian_waylist[3]['is_default_check'] = 1;
					}
				}
				
				if( $supply_commiss_tixianway_weixin_offline == 1 )
				{
					$tixian_waylist[4]['is_show'] = 1;
					
					if( $last_tixian_order['supply_apply_type'] == 4 )
					{
						$tixian_waylist[4]['is_default_check'] = 1;
					}
				}
				
				// bankaccount
				// $last_tixian_order = array('bankname' =>'微信','bankaccount' => '','bankusername' => '' ,'supply_apply_type' => -1 );
				
				//微信真实姓名 last_tixian_order 中的：bankaccount
				//支付宝账号   last_tixian_order 中的：bankaccount
				
				//银行卡里面的 信息：  银行名称  bankname  , 银行卡账户  bankaccount  ,  持卡人姓名  bankusername
				
				//微信私下转 的微信号：  bankaccount
				
				if( empty($supply_commiss) )
				{
					$supply_commiss  = array();
					$supply_commiss['money'] = 0;//可提现
					$supply_commiss['dongmoney'] = 0; //提现中
					$supply_commiss['getmoney'] = 0; //已提现金额
				}
				
				
				
				$supply_tixian_free = D('Home/Front')->get_config_by_name('supply_tixian_moneyfree');
				
				if( !isset($supply_tixian_free) || $supply_tixian_free <= 0 )
				{
				    $supply_tixian_free = 0;
				}
				
				
				
				$supply_tixian_notice = D('Home/Front')->get_config_by_name('supply_tixian_notice');
				
				if( empty($supply_tixian_notice) )
				{
					$supply_tixian_notice = '';
				}else{
					
					$supply_tixian_notice = htmlspecialchars_decode( htmlspecialchars_decode($supply_tixian_notice) );
				}

				$member_info = M('lionfish_comshop_member')->field('avatar,username')
						->where( array('member_id' => $member_id) )->find();
				
				$result = array();
				$result['supply_min_apply_money'] = $supply_min_apply_money;//最小提现金额
				$result['tixian_waylist'] = $tixian_waylist;//可提现的银行类型
				
				$result['supply_commiss'] = $supply_commiss;//供应商账户信息
				$result['supply_tixian_free'] = $supply_tixian_free;//提现手续费
				$result['supply_tixian_notice'] = $supply_tixian_notice;//提现规则

				$result['member_info'] = $member_info;//用户信息
			
				echo json_encode( array('code' => 0, 'data' =>$result ) );
				die();
				
			}else{
				echo json_encode( array('code' => 2,'msg' => '未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	/**
		供应商  收支记录页面
	**/
	public function supply_applymoney()
	{
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				//$supply_info['id']
				$supply_id = $supply_info['id'];
				
				$supply_apply_type = $gpc['supply_apply_type'];//提现类型： 1 微信零钱 2 支付宝  3银行卡 4微信线下转账
				
				
				$supply_min_apply_money = D('Home/Front')->get_config_by_name('supply_min_money');
		
				if( empty($supply_min_apply_money) )
				{
					$supply_min_apply_money = 0;
				}
				
				$account = $gpc['account'];//微信真实姓名 + 支付宝账户 + 银行卡账户 + 微信线下转的微信名称

				if($supply_apply_type == 3){
					$reg = '/^[\d]{16,19}$/ ';
					if(!preg_match($reg,$account,$match)){
						echo json_encode( array('code' => 2, 'msg' =>  '银行卡账户格式错误') );
						die();
					}
				}
				
				$card_name = $gpc['card_name']; //银行卡名称
				$card_username = $gpc['card_username']; //持卡人姓名
				$ti_money =  floatval( $gpc['ti_money'] ); //提现金额
				
				$supply_commiss = M('lionfish_supply_commiss')->where( array('supply_id' => $supply_id ) )->find();
				
				if($ti_money < $supply_min_apply_money){
					echo json_encode( array('code' => 2, 'msg' => '最低提现'.$supply_min_apply_money ) );
					die();
				}
				
				if($ti_money <=0){
					echo json_encode( array('code' => 2, 'msg' =>  '最低提现大于0元' ) );
					die();
				}
				
				if($ti_money > $supply_commiss['money']){
					echo json_encode( array('code' => 2, 'msg' =>  '当前最多提现'.$supply_commiss['money'] ) );
					die();
				}
				
				
				$supply_tixian_free = D('Home/Front')->get_config_by_name('supply_tixian_moneyfree');
				
				if( !isset($supply_tixian_free) || $supply_tixian_free <= 0 )
				{
				    $supply_tixian_free = 0;
				}
				
				
				$ins_data = array();
				$ins_data['supply_id'] = $supply_id;
				$ins_data['money'] = $ti_money;
				$ins_data['service_charge'] = round( ($supply_tixian_free * $ti_money) / 100 ,2);
				$ins_data['server_bili'] = $supply_tixian_free;
				
				$ins_data['state'] = 0;
				$ins_data['shentime'] = 0;
				$ins_data['is_send_fail'] = 0;
				$ins_data['fail_msg'] = '';
				$ins_data['supply_apply_type'] = $supply_apply_type;
				
				//1 微信 2 支付宝  3银行卡
				if($supply_apply_type == 1)
				{
					$ins_data['bankname'] = '微信零钱';
					$ins_data['bankaccount'] = $account;
					$ins_data['bankusername'] = '';
				}else if($supply_apply_type == 2){
					$ins_data['bankname'] = '支付宝';
					$ins_data['bankaccount'] = $account;
					$ins_data['bankusername'] = $card_username;
				}else if($supply_apply_type == 3){
					$ins_data['bankname'] = $card_name;
					$ins_data['bankaccount'] = $account;
					$ins_data['bankusername'] = $card_username;
				}else if($supply_apply_type == 4){
					$ins_data['bankname'] = '微信私下转';
					$ins_data['bankaccount'] = $account;
					$ins_data['bankusername'] = '';
				}
				
				$ins_data['addtime'] = time();
				
				M('lionfish_supply_tixian_order')->add($ins_data);
				
				M('lionfish_supply_commiss')->where( array('supply_id' => $supply_id ) )->setInc('money',-$ti_money);
				M('lionfish_supply_commiss')->where( array('supply_id' => $supply_id ) )->setInc('dongmoney',$ti_money);
				
				echo json_encode( array('code' => 0, 'msg' =>'提交成功，等待审核' ) );
				die();
				
			}else{
				echo json_encode( array('code' => 2,'msg' => '未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	
	/**
		供应商  提现记录
	**/
	
	public function supply_apply_flowlist()
	{
		$gpc = I('request.');
		
		$token =  $gpc['token'];
		
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' => '未登录') );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		if($member_id > 0){
			
			//是否开启供应商平台
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');
		
			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}
			
			$supply_info = M('lionfish_comshop_supply')->where( array('member_id' => $member_id) )->find();
			
			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				//$supply_info['id']
				$supply_id = $supply_info['id'];
				
				$page = isset($gpc['page']) ? intval($gpc['page']) : 1;
				
				if( $page <= 0 )
				{
					$page = 1;
				}
				
				$perpage = 20;
				
				$offset = ($page - 1) * $perpage;
				
				$list = M('lionfish_supply_tixian_order')->where( array('supply_id' => $supply_id )  )->order('id desc ')->limit($offset, $perpage)->select();
				
				$data = array();
				
				if( !empty($list) )
				{
					
					foreach( $list as $val )
					{
						$tmp = array();
						
						if( $val['state'] == 0 )
						{
							$tmp['state_str'] = '提现中';
							
						}else if( $val['state'] == 1 )
						{
							$tmp['state_str'] = '提现成功';
						}else if( $val['state'] == 2 )
						{
							$tmp['state_str'] = '拒绝提现';
						}
						
						$tmp['money'] = $val['money'];
						$tmp['addtime'] = date('Y-m-d H:i:s', $val['addtime'] );
						
						$data[] = $tmp;
					}
					
				}
				
				echo json_encode( array('code' => 0, 'data' => $data ) );
				die();
				
			}else{
				echo json_encode( array('code' => 2,'msg' => '未开启供应商手机端或未授权供应商手机端或供应商未审核') );
				die();
			}
		}
		
	}
	
	
	
	
	
	
	
	

    
}