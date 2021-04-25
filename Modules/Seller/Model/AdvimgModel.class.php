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
namespace Seller\Model;

class AdvimgModel{
	
	
	public function update($_GPC)
	{
	    $data = $_GPC['data'];
	    $pos = $_GPC['pos'];
	    if($data['linktype']==3 || $data['linktype']==4){
            $data['link'] = $data['cid'];
        }

		$ins_data = array();
		$id = $data['id'];
		$ins_data['thumb'] = save_media($data['thumb']);
		$ins_data['link'] = $data['link'];
		$ins_data['linktype'] = $data['linktype'];
		$ins_data['appid'] = $data['appid'];
		$ins_data['displayorder'] = $data['displayorder'];
		$ins_data['enabled'] = $data['enabled'];
		$ins_data['pos'] = implode(',', $pos);
		$ins_data['addtime'] = time();

		if( !empty($id) && $id > 0 )
		{
			M('lionfish_comshop_advimg')->where( array('id' => $id) )->save( $ins_data );
		}else{
			M('lionfish_comshop_advimg')->add($ins_data);
		}
	}
}
?>