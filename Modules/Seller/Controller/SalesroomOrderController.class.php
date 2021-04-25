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
 * @author    zcy   2020-09-18
 *
 */
namespace Seller\Controller;

class SalesroomOrderController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
	}
	
	public function index()
	{
        $pindex    = I('request.page', 1);
        $psize     = 20;
        $_GPC = I('request.');
		$keyword = I('request.keyword');
		$this->keyword = $keyword;
        $this->state = I('request.state','');

        $this->salesroom_id = $_GPC['salesroom_id'];
        $condition = "";
        //姓名/手机号/会员名
        if (!empty($keyword)) {
            $keyword = htmlspecialchars(addslashes(trim($keyword)));
            $condition .= " and (o.name like '%".$keyword."%' or o.telephone like '%".$keyword."%' or o.shipping_name like '%".$keyword."%' or o.order_num_alias like '%".$keyword."%') ";
        }
        if(!empty($this->state)){
            if($this->state == 2){//已核销
                $condition .= " and ogs.is_hexiao_over = 1 and o.order_status_id != 5 ";
            }else if($this->state == 1){//待核销
                $condition .= " and ogs.is_hexiao_over = 0 and o.order_status_id != 5 ";
            }else if($this->state == 3){
                $condition .= " and o.order_status_id = 5 ";
            }else if($this->state == 4){
                $condition .= " and ogs.is_hexiao_over = 2 ";
            }
        }
        $starttime = strtotime( date('Y-m-d')." 00:00:00" );
        $endtime = $starttime + 86400;
        
        if( isset($_GPC['searchtime']) && $_GPC['searchtime'] == 'create_time' )
        {
            if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
                $starttime = strtotime($_GPC['time']['start']);
                $endtime = strtotime($_GPC['time']['end']);
                
                $condition .= ' AND o.date_added >= '.$starttime.' AND o.date_added <= '.$endtime.' ';
            }
            $this->searchtime = $_GPC['searchtime'];
        }
        if(!empty($this->salesroom_id)){
            $this->salesroom = M('lionfish_comshop_salesroom')->where( array('id' => $this->salesroom_id ) )->find();
            $condition .= ' AND EXISTS(SELECT order_id FROM '. C('DB_PREFIX').'lionfish_comshop_order_goods_saleshexiao_record as osr WHERE osr.order_goods_id=og.order_goods_id and osr.salesroom_id='.$this->salesroom_id.')  ';
        }
        if (defined('ROLE') && ROLE == 'agenter' )
        {
            $supper_info = get_agent_logininfo();
            $supply_id = $supper_info['id'];
            $condition .= " and o.store_id= ".$supply_id;
        }
        $this->starttime = $starttime;
        $this->endtime = $endtime;

        $sql = 'SELECT ogs.*,o.order_num_alias,o.member_id,o.store_id,o.head_id,o.name as member_name,o.telephone,o.shipping_name,o.head_id,'
            . ' o.date_added,o.payment_code,o.order_status_id,og.goods_id,og.name as goods_name,og.goods_images,og.quantity,og.shipping_fare,'
            . ' og.price,og.total '
            . ' FROM '. C('DB_PREFIX').'lionfish_comshop_order_goods_saleshexiao as ogs '
            . ' left join '. C('DB_PREFIX').'lionfish_comshop_order as o on ogs.order_id=o.order_id '
            . ' left join '. C('DB_PREFIX').'lionfish_comshop_order_goods as og on og.order_goods_id=ogs.order_goods_id '
            . ' WHERE 1=1 '.$condition. ' order by o.date_added desc limit ' . (($pindex - 1) * $psize) . ',' . $psize;
        $list = M()->query($sql);
        foreach ($list as $k=>$v) {
            $list[$k]['date_added'] = date('Y-m-d H:i:s',$v['date_added']);
            //团长信息
            $community_info = D('Seller/Front')->get_community_byid($v['head_id']);
            $list[$k]['community_name'] = $community_info['communityName'];
            $list[$k]['head_name'] = $community_info['disUserName'];
            $list[$k]['head_mobile'] = $community_info['head_mobile'];
            $list[$k]['province'] = $community_info['province'];
            $list[$k]['city'] = $community_info['city'];
            //最后一次核销员信息
            if($v['is_hexiao_over'] == 1){
                $record = M('lionfish_comshop_order_goods_saleshexiao_record')->where( array('order_id' => $v['order_id'],'order_goods_id' => $v['order_goods_id'] ) )->order('id desc')->limit(1)->find();
                $list[$k]['smember_name'] = $record['smember_name'];
                if($record['is_admin'] == 0){//不是后台核销
                    $salesroom_member = M('lionfish_comshop_salesroom_member')->field('mobile')->where( array('id' => $record['salesmember_id'] ) )->find();
                    $list[$k]['smember_mobile'] = $salesroom_member['mobile'];
                    $list[$k]['salesroom_name'] = $record['salesroom_name'];
                }
            }else{
                $record_count = M('lionfish_comshop_order_goods_saleshexiao_record')->where( array('order_id' => $v['order_id'],'order_goods_id' => $v['order_goods_id'] ) )->count();
                if($record_count > 0){
                    $list[$k]['record_count'] = $record_count;
                }
            }
        }
        $total_count = M()->query('SELECT count(1) as count '
            . ' FROM '. C('DB_PREFIX').'lionfish_comshop_order_goods_saleshexiao as ogs '
            . ' left join '. C('DB_PREFIX').'lionfish_comshop_order as o on ogs.order_id=o.order_id '
            . ' left join '. C('DB_PREFIX').'lionfish_comshop_order_goods as og on og.order_goods_id=ogs.order_goods_id '
            . ' WHERE 1=1 '.$condition );
        $total =  $total_count[0]['count'];
        $pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;
		$this->display('Salesroom_order/index');
	}
	
	public function member_orders()
	{
	    
	    $pindex    = I('request.page', 1);
	    $psize     = 20;
	    $_GPC = I('request.');
	    $keyword = I('request.keyword');
	    $this->keyword = $keyword;
	    
	    //核销员信息
	    $smember_id = $_GPC['smember_id'];
	    $smember_info = M('lionfish_comshop_salesroom_member')->where( array('id' => $smember_id ) )->find();
	    $this->smember_info = $smember_info;
	    
	    $condition = "";
	    //姓名/手机号/会员名
	    if (!empty($keyword)) {
	        $condition .= " and (o.order_num_alias like '%".$keyword."%' or og.name like '%".$keyword."%' or o.telephone like '%".$keyword."%' or o.shipping_name like '%".$keyword."%') ";
	    }
	    $starttime = strtotime( date('Y-m-d')." 00:00:00" );
	    $endtime = $starttime + 86400;
	    
	    if( isset($_GPC['searchtime']) && $_GPC['searchtime'] == 'create_time' )
	    {
	        if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
	            $starttime = strtotime($_GPC['time']['start']);
	            $endtime = strtotime($_GPC['time']['end']);
	            
	            $condition .= ' AND o.date_added >= '.$starttime.' AND o.date_added <= '.$endtime.' ';
	        }
	        $this->searchtime = $_GPC['searchtime'];
	    }
	    if(!empty($smember_id)){
	        $condition .= ' AND EXISTS(SELECT order_id FROM '. C('DB_PREFIX').'lionfish_comshop_order_goods_saleshexiao_record as osr WHERE osr.order_id=o.order_id and osr.salesmember_id='.$smember_id.')  ';
	    }
	    if(!empty($_GPC['payment_code'])){
	        $condition .= " and o.payment_code = '".$_GPC['payment_code']."' ";
	        $this->payment_code = $_GPC['payment_code'];
	    }
	    $this->starttime = $starttime;
	    $this->endtime = $endtime;
	    $list = M()->query('SELECT ogs.*,o.order_num_alias,o.member_id,o.store_id,o.head_id,o.name as member_name,o.telephone,o.shipping_name,o.head_id,'
	        . ' o.date_added,o.payment_code,og.goods_id,og.name as goods_name,og.goods_images,og.quantity,og.shipping_fare,'
	        . ' og.price,og.total '
	        . ' FROM '. C('DB_PREFIX').'lionfish_comshop_order_goods_saleshexiao as ogs '
	        . ' left join '. C('DB_PREFIX').'lionfish_comshop_order as o on ogs.order_id=o.order_id '
	        . ' left join '. C('DB_PREFIX').'lionfish_comshop_order_goods as og on og.order_goods_id=ogs.order_goods_id '
	        . ' WHERE 1=1 '.$condition. ' order by o.date_added desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
	    foreach ($list as $k=>$v) {
	        $list[$k]['date_added'] = date('Y-m-d H:i:s',$v['date_added']);
	        
	        //最后一次核销员信息
	        $record = M('lionfish_comshop_order_goods_saleshexiao_record')->where( array('order_id' => $v['order_id'],'order_goods_id' => $v['order_goods_id'] ) )->order('id desc')->find();
            $list[$k]['smember_name'] = $record['smember_name'];
            if($record['is_admin'] == 0){//不是后台核销
                $salesroom_member = M('lionfish_comshop_salesroom_member')->field('mobile')->where( array('id' => $record['salesmember_id'] ) )->find();
                $list[$k]['smember_mobile'] = $salesroom_member['mobile'];
                $list[$k]['salesroom_name'] = $record['salesroom_name'];
            }
            if($v['is_hexiao_over'] == 1){
                $list[$k]['hexiao_time'] = $record['addtime'];
            }
	    }
	    $total_count = M()->query('SELECT count(1) as count '
	        . ' FROM '. C('DB_PREFIX').'lionfish_comshop_order_goods_saleshexiao as ogs '
	        . ' left join '. C('DB_PREFIX').'lionfish_comshop_order as o on ogs.order_id=o.order_id '
	        . ' left join '. C('DB_PREFIX').'lionfish_comshop_order_goods as og on og.order_goods_id=ogs.order_goods_id '
	        . ' WHERE 1=1 '.$condition );
	    $total =  $total_count[0]['count'];
	    $pager = pagination2($total, $pindex, $psize);
	    
	    $this->list = $list;
	    $this->pager = $pager;
	    
	    
	    $this->display();
	}

}
?>