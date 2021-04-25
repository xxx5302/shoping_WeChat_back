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
namespace Seller\Model;

class KdniaoModel{
	
	
	public function update($data)
	{
		$ins_data = array();
		$ins_data['express_name'] = $data['express_name'];
		$ins_data['express_code'] = $data['express_code'];
		$ins_data['customer_name'] = $data['customer_name'];
		$ins_data['customer_pwd'] = $data['customer_pwd'];
		$ins_data['month_code'] = $data['month_code'];
		$ins_data['send_site'] = $data['send_site'];
		$ins_data['send_staff'] = $data['send_staff'];

		$ins_data['template_size'] = $data['template_size'];
		$ins_data['is_send_message'] = $data['is_send_message'];
		$ins_data['is_send_goods'] = $data['is_send_goods'];
		$ins_data['sender_company'] = $data['sender_company'];
		$ins_data['sender_name'] = $data['sender_name'];
		$ins_data['sender_tel'] = $data['sender_tel'];
		$ins_data['sender_mobile'] = $data['sender_mobile'];
		$ins_data['sender_postcode'] = $data['sender_postcode'];

		$ins_data['sender_address'] = $data['sender_address'];
		$ins_data['sender_province_name'] = $data['sender_province_name'];
		$ins_data['sender_city_name'] = $data['sender_city_name'];
		$ins_data['sender_area_name'] = $data['sender_area_name'];
		
		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			M('lionfish_comshop_kdniao_list')->where( array('id' => $id) )->save( $ins_data );
		}else{
			$ins_data['addtime'] = time();
			M('lionfish_comshop_kdniao_list')->add($ins_data);
		}
	}
	
	
}
?>