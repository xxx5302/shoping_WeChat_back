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
 * @author    fld
 *
 */
namespace Home\Controller;
class PopadvController extends CommonController{

	/**
	 * 弹窗广告数据
	 * 需要参数：
	 * 1、token 会员token,
	 * 2、pop_page 投放页面类型：0、商城首页，1、商品分类，2、商城购物车，3、商城个人中心
	 *
	 * 1、时间判断，2、投放对象判断，3、
	 */
	public function popadv_list()
	{
		$popadvs = array();

		$_GPC = I('request.');
		//获取用户编号信息
		$token = $_GPC['token'];
		$weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		$member_id = $weprogram_token['member_id'];
		//投放页面
		$pop_page = $_GPC['pop_page'];

		$pop_list = D('Seller/Redisorder')->get_popadv_list();
		$now = time();
		if(empty($pop_list)){
			$pop_info = M('lionfish_comshop_pop_adv')->where(" begin_time <= ".$now." and end_time >= ".$now." and status = 1 and pop_page=".$pop_page)->find();

			if($pop_info['send_person'] == 1){//指定用户发送
				if(strpos($pop_info['member_id'],$member_id.',') !== false){
					$popadvs = $pop_info;
				}
			}else if($pop_info['send_person'] == 2){//用户组发送
				if(!empty($pop_info['member_group_id'])){
					$member_info = M('lionfish_comshop_member')->where(array('groupid'=>$pop_info['member_group_id'],'member_id'=>$member_id))->find();
					if(!empty($member_info)){
						$popadvs = $pop_info;
					}
				}else{
					$popadvs = $pop_info;
				}
			}else{//全部发送
				$popadvs = $pop_info;
			}
			if(!empty($popadvs)){
				$adv_list = M('lionfish_comshop_pop_adv_list')->where(array('ad_id'=>$popadvs['id']))->select();

				if(!empty($adv_list)) {
					foreach($adv_list as $k=>$v){
						if($v['thumb']){
							$v['thumb'] = tomedia($v['thumb']);
						}
						$adv_list[$k] = $v;
					}
				}
				$popadvs['adv_list'] = $adv_list;
			}
		}else{
			foreach($pop_list as $k=>$v){
				//启用，投放页面，时间
				if($v['status'] == 1 && $v['pop_page'] == $pop_page && $v['begin_time'] <= $now && $now <= $v['end_time']){
					if($v['send_person'] == 1){//指定用户发送
						if(strpos($v['member_id'].',',$member_id.',') !== false){
							$popadvs = $v;
						}
					}else if($v['send_person'] == 2){//用户组发送
						if(!empty($v['member_group_id'])){
							$member_info = M('lionfish_comshop_member')->where(array('groupid'=>$v['member_group_id'],'member_id'=>$member_id))->find();
							if(!empty($member_info)){
								$popadvs = $v;
							}
						}
					}else{//全部发送
						$popadvs = $v;
					}
				}
			}
		}
		if(!empty($popadvs)){
			//判断弹窗广告是否还要再出现
			$is_show = D('Seller/Redisorder')->check_pop_advs($popadvs,$member_id,$pop_page);
			if($is_show == 0){
				$popadvs = array();
			}
		}
		if(!empty($popadvs)){
			$view_num = $popadvs['view_num']+1;
			M('lionfish_comshop_pop_adv')->where(array('id'=>$popadvs['id']))->save(array('view_num'=>$view_num));
		}
		$result = array('popadvs' =>$popadvs);
		echo json_encode($result);
		die();
	}
	//弹窗广告次数累加
	/**
	 * 参数 弹窗广告编号 id
	 */
	public function popadv_click(){
		$_GPC = I('request.');
		$id = $_GPC['id'];
		if(!empty($id)){
			$pop_info = M('lionfish_comshop_pop_adv')->where(array('id'=>$id))->field('open_num')->find();
			$open_num = $pop_info['open_num']+1;
			M('lionfish_comshop_pop_adv')->where(array('id'=>$id))->save(array('open_num'=>$open_num));
		}

		echo json_encode(array('code'=>0));
		die();
	}
}