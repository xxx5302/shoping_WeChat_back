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

class SalesroomController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
	}
	
	public function index()
	{
		
        $pindex    = I('request.page', 1);
        $psize     = 20;

		$keyword = I('get.keyword','','addslashes');
		$keyword2 = stripslashes($keyword);
		$this->keyword = $keyword2;
        $this->state = I('request.state','');
        $condition = "";
        if (!empty($keyword)) {
            $condition .= " and (room_address like '%".$keyword."%' or room_name like '%".$keyword."%' or mobile like '%".$keyword."%') ";
        }
        if(!empty($this->state)){
            if($this->state == 2){
                $condition .= " and state= 0 ";
            }else{
                $condition .= " and state= ".$this->state;
            }
        }
        $supply_id  = 0;
        if (defined('ROLE') && ROLE == 'agenter' )
        {
            $supper_info = get_agent_logininfo();
            $supply_id = $supper_info['id'];
            $condition .= " and supply_id= ".$supply_id;
        }

        $list = M()->query('SELECT * FROM '. C('DB_PREFIX'). "lionfish_comshop_salesroom WHERE 1=1 "
              . $condition . ' order by displayorder desc, id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
        foreach ($list as $k=>$v) {
            if(empty($v['supply_id'])){
                $list[$k]['supply_name'] = "平台";
            }else{
                $supply_info = M('lionfish_comshop_supply')->where( array('id' => $v['supply_id']) )->field('shopname')->find();
                $list[$k]['supply_name'] = $supply_info['shopname'];
            }
            $list[$k]['member_count'] = M('lionfish_comshop_salesroom_relative_member')->where( array('salesroom_id' => $v['id']) )->count();
            $list[$k]['disable_time'] = date('Y-m-d H:i:s',$v['disable_time']);
        }
		$total = M('lionfish_comshop_salesroom')->where("1=1 ".$condition)->count();
			
        $pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;
		
		$this->display();
	}

	/**
     * 编辑添加
     */
	public function add()
	{
        $id = I('request.id');
        if (!empty($id)) {
			$item = M('lionfish_comshop_salesroom')->where( array('id' => $id) )->find();
            if(!empty($item['supply_id'])){
                $item['supply_info'] = M('lionfish_comshop_supply')->where( array('id' => $item['supply_id']) )->find();
            }
			$this->id = $id;
			$this->item = $item;
        }
        $supply_id  = 0;
        if (defined('ROLE') && ROLE == 'agenter' )
        {
            $supper_info = get_agent_logininfo();
            $supply_id = $supper_info['id'];
        }
        $this->supply_id = $supply_id;
        if (IS_POST) {
            $data = I('request.data');
            if($supply_id > 0){
                $data['supply_id'] = $supply_id;
            }
            D('Seller/Salesroom')->update($data);
            
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }
		$this->display('Salesroom/add');
	}

	/**
     * 改变状态
     */
    public function change()
    {

        $id = I('request.id');

        //ids
        if (empty($id)) {
			$ids = 	I('request.ids');
            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = I('request.type');
        $value = I('request.value');

        if (!(in_array($type, array('state')))) {
            show_json(0, array('message' => '参数错误'));
        }

		$items = M('lionfish_comshop_salesroom')->where( array('id' => array('in', $id) ) )->select();
		
        foreach ($items as $item) {
            if($value == 0){
                M('lionfish_comshop_salesroom')->where( array('id' => $item['id']) )->save( array($type => $value,'disable_time'=>time()) );
                D('Seller/Salesroom')->update_hxgoods($item['id']);
            }else{
                M('lionfish_comshop_salesroom')->where( array('id' => $item['id']) )->save( array($type => $value) );
            }
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }

	/**
	 * 删除
	 */
    public function delete()
    {
       
        $id = I('request.id');

        if (empty($id)) {
			$ids = I('request.ids');
            $id = (is_array($ids) ? implode(',', $ids) : 0);
        }

		$items = M('lionfish_comshop_salesroom')->field('id,room_name')->where( array('id' => array('in', $id) ) )->select();

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
			M('lionfish_comshop_salesroom')->where( array('id' => $item['id']) )->delete();
            D('Seller/Salesroom')->update_hxgoods($item['id']);
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }

    public function query(){
        $_GPC = I('request.');
        $kwd = I('request.keyword','');
        $condition = ' 1 ';
        if (!empty($kwd)) {
            $condition .= ' AND ( `room_name` LIKE '.'"%' . $kwd . '%"'.' or `mobile` LIKE '.'"%' . $kwd . '%"'.' or `room_address` LIKE '.'"%' . $kwd . '%"'.' )';
        }
        $supply_id  = I('request.supply_id', 0);
        if (defined('ROLE') && ROLE == 'agenter' )
        {
            $supper_info = get_agent_logininfo();
            $supply_id = $supper_info['id'];
            $condition.= ' AND supply_id='.$supply_id;
        }else{
            if(empty($supply_id)){
                $supply_id = 0;
            }
            $condition.= ' AND supply_id='.$supply_id;
        }
        $condition.=' AND state=1 ';
        $ds = M('lionfish_comshop_salesroom')->where(  $condition )->field('id,room_name,room_logo,supply_id,room_address,mobile')->select();
        $s_html = "";
        foreach ($ds as &$value) {
            if(empty($value['supply_id'])){
                $value['supply_name'] = "平台";
            }else{
                $supply_info = M('lionfish_comshop_supply')->where( array('id' => $value['supply_id']) )->field('shopname')->find();
                $value['supply_name'] = $supply_info['shopname'];
            }
            $value['room_name'] = htmlspecialchars($value['room_name'], ENT_QUOTES);
            $value['room_logo'] = tomedia($value['room_logo']);
            $s_html .= "<tr><td><img src='".$value['room_logo']."' style='width:30px;height:30px;padding1px;border:1px solid #ccc' />&nbsp;&nbsp;{$value[room_name]}</td>";
            $s_html .= "<td>{$value['room_address']}</td>";
            $s_html .= "<td>{$value['supply_name']}</td>";
            $s_html .= "<td>{$value['mobile']}</td>";
            if( isset($_GPC['template']) &&  $_GPC['template'] == 'mult'){
                $s_html .= '<td style="width:80px;"><a href="javascript:;" class="choose_mult_link" data-json=\''.json_encode($value).'\'>选择</a></td></tr>';
            }else if( isset($_GPC['template']) &&  $_GPC['template'] == 'mult_goods'){
                $s_html .= '<td style="width:80px;"><a href="javascript:;" class="choose_mult_goods_link" data-json=\''.json_encode($value).'\'>选择</a></td></tr>';
            }else{
                $s_html .= '<td style="width:80px;"><a href="javascript:;" class="choose_dan_link" data-json=\''.json_encode($value).'\'>选择</a></td></tr>';
            }
        }
        unset($value);
        if( isset($_GPC['is_ajax']) &&  $_GPC['is_ajax'] == 1)
        {
            echo json_encode(  array('code' =>0, 'html' => $s_html) );
            die();
        }
        $url = 'salesroom/query';
        $this->url = $url;
        $this->ds = $ds;
        $this->gpc = $_GPC;
        $this->kwd = $kwd;
        $this->supply_id = $supply_id;
        $this->display();
    }
}
?>