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

class SalesroomMemberController extends CommonController{
	
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
		
		$room_id = I('request.salesroom_id');

        $this->state = I('request.state','');
        $this->room_id = $room_id;
        $condition = "";
        if (!empty($keyword)) {
            $condition .= " and (username like '%".$keyword."%' or mobile like '%".$keyword."%' or member_id in (select member_id from ".C('DB_PREFIX')."lionfish_comshop_member where username like '%".$keyword."%')) ";
        }
        if(!empty($room_id)){
            $condition .= " and id in (select smember_id from ".C('DB_PREFIX')."lionfish_comshop_salesroom_relative_member where salesroom_id = ".$room_id.") ";
        }
        if(!empty($this->state)){
            if($this->state == 2){
                $condition .= " and state= 0 ";
            }else{
                $condition .= " and state= ".$this->state;
            }
        }
        if (defined('ROLE') && ROLE == 'agenter' )
        {
            $supper_info = get_agent_logininfo();
            $supply_id = $supper_info['id'];
            $condition .= " and supply_id= ".$supply_id;
        }
        $list = M()->query('SELECT * FROM '. C('DB_PREFIX'). "lionfish_comshop_salesroom_member WHERE 1=1 "
              . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
        foreach ($list as $k=>$v) {
            if(empty($v['supply_id'])){
                $list[$k]['supply_name'] = "平台";
            }else{
                $supply_info = M('lionfish_comshop_supply')->where( array('id' => $v['supply_id']) )->field('shopname')->find();
                $list[$k]['supply_name'] = $supply_info['shopname'];
            }
            $list[$k]['disable_time'] = date('Y-m-d H:i:s',$v['disable_time']);
            $member_info = M('lionfish_comshop_member')->where( array('member_id' => $v['member_id']) )->field('username')->find();
            $list[$k]['member_name'] = $member_info['username'];
            $list[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);

            $room_ids = "";
            $room_id_list = M('lionfish_comshop_salesroom_relative_member')->where(array('smember_id'=>$v['id']))->field('salesroom_id')->select();
            foreach($room_id_list as $rk=>$rv){
                if(empty($room_ids)){
                    $room_ids = $rv['salesroom_id'];
                }else{
                    $room_ids = $room_ids.','.$rv['salesroom_id'];
                }
            }
            $list[$k]['room_list'] = M('lionfish_comshop_salesroom')->where("id in (".$room_ids.")")->field('room_name')->select();
        }
		$total = M('lionfish_comshop_salesroom_member')->where("1=1 ".$condition)->count();
			
        $pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;
        
		//选择门店
		$room_list = D('Seller/Salesroom')->querySalesRoom();
		$this->room_list = $room_list;
		$this->display('Salesroom_member/index');
	}

	/**
     * 编辑添加
     */
	public function add()
	{
        if (IS_POST) {
            $supply_id  = 0;
            if (defined('ROLE') && ROLE == 'agenter' )
            {
                $supper_info = get_agent_logininfo();
                $supply_id = $supper_info['id'];
            }
            $data = I('request.data');
            $data['supply_id'] = $supply_id;
            $data['salesroom_ids'] = I('request.salesroom_ids');
            if(!empty($data['id'])){
                $member_count = M('lionfish_comshop_salesroom_member')->where( array('member_id' => $data['member_id']) )->where('id <> '.$data['id'])->count();
            }else{
                $member_count = M('lionfish_comshop_salesroom_member')->where( array('member_id' => $data['member_id']) )->count();
            }
            if($member_count > 0){
                show_json(0, array('message' => '会员已绑定核销人员，请更换'));
            }
            D('Seller/SalesroomMember')->update($data);

            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }else{
            $id = I('request.id');
            if (!empty($id)) {
                $item = M('lionfish_comshop_salesroom_member')->where( array('id' => $id) )->find();
                $this->id = $id;
                $this->item = $item;

                $this->member_info = M('lionfish_comshop_member')->where( array('member_id' => $item['member_id']) )->field('avatar,username as nickname')->find();
                $room_ids = "";
                $room_id_list = M('lionfish_comshop_salesroom_relative_member')->where(array('smember_id'=>$item['id']))->field('salesroom_id')->select();
                foreach($room_id_list as $k=>$v){
                    if(empty($room_ids)){
                        $room_ids = $v['salesroom_id'];
                    }else{
                        $room_ids = $room_ids.','.$v['salesroom_id'];
                    }
                }
                $this->room_list = M('lionfish_comshop_salesroom')->where("id in (".$room_ids.")")->field('id,room_name,room_logo')->select();
            }
            $this->display('Salesroom_member/add');
        }
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

		$items = M('lionfish_comshop_salesroom_member')->where( array('id' => array('in', $id) ) )->select();
		
        foreach ($items as $item) {
            if($value == 0){
                M('lionfish_comshop_salesroom_member')->where( array('id' => $item['id']) )->save( array($type => $value,'disable_time'=>time()) );
            }else{
                M('lionfish_comshop_salesroom_member')->where( array('id' => $item['id']) )->save( array($type => $value) );
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

		$items = M('lionfish_comshop_salesroom_member')->field('id,username')->where( array('id' => array('in', $id) ) )->select();

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
			M('lionfish_comshop_salesroom_member')->where( array('id' => $item['id']) )->delete();
            M('lionfish_comshop_salesroom_relative_member')->where( array('smember_id' => $item['id']) )->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }
    
    public function query(){
        $_GPC = I('request.');
        $kwd = I('request.keyword','');
        $is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;
        $room_id = I('request.room_id','');
        $condition = ' ';
        if (!empty($kwd)) {
            $condition .= ' AND ( `username` LIKE '.'"%' . $kwd . '%"'.' or `mobile` LIKE '.'"%' . $kwd . '%"'.' )';
        }
        if(!empty($room_id)){
            $condition .= " and id in (select smember_id from ".C('DB_PREFIX')."lionfish_comshop_salesroom_relative_member where salesroom_id = ".$room_id.") ";
        }
        $condition .= " and state= 1";
        $supply_id  = 0;
        if (defined('ROLE') && ROLE == 'agenter' )
        {
            $supper_info = get_agent_logininfo();
            $supply_id = $supper_info['id'];
            $condition.= ' AND supply_id='.$supply_id;
        }else{
            $condition.= ' AND supply_id=0';
        }
        
        /**分页开始**/
        $page =  isset($_GPC['page']) ? intval($_GPC['page']) : 1;
        $page = max(1, $page);
        $page_size = 10;
        /**分页结束**/
        $sql ='SELECT * FROM ' . C('DB_PREFIX'). 'lionfish_comshop_salesroom_member WHERE 1 ' . $condition .' order by id desc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size ;
        
        $ds = M()->query($sql);
        $total = M('lionfish_comshop_salesroom_member')->where( '1 ' . $condition )->count();
        
        foreach ($ds as &$value) {
            $member_info = M('lionfish_comshop_member')->where( array('member_id' => $value['member_id']) )->field('username,avatar')->find();
            $value['member_name'] = $member_info['username'];
            $value['avatar'] = $member_info['avatar'];
            if($is_ajax == 1)
            {
                $ret_html .= '<tr>';
                $ret_html .= '	<td><img src="'.$value['avatar'].'" style="width:30px;height: 30px;padding:1px;border:1px solid #ccc" />'. $value['username'].'</td>';
                $ret_html .= '	<td>'.$value['mobile'].'</td>';
                $ret_html .= '	<td>'.$value['member_name'].'</td>';
                
                if( isset($_GPC['template']) &&  $_GPC['template'] == 'mult'){
                    $ret_html .= '<td style="width:80px;"><a href="javascript:;" class="choose_mult_link" data-json=\''.json_encode($value).'\'>选择</a></td></tr>';
                }else if( isset($_GPC['template']) &&  $_GPC['template'] == 'mult_goods'){
                    $ret_html .= '<td style="width:80px;"><a href="javascript:;" class="choose_mult_goods_smember_link" data-json=\''.json_encode($value).'\'>选择</a></td></tr>';
                }else{
                    $ret_html .= '<td style="width:80px;"><a href="javascript:;" class="choose_dan_link" data-json=\''.json_encode($value).'\'>选择</a></td></tr>';
                }
                
                $ret_html .= '</tr>';
                
            }
        }
        
        $pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));
        
        if( $is_ajax == 1 )
        {
            echo json_encode( array('code' => 0, 'html' => $ret_html,'pager' => $pager) );
            die();
        }
        
        
        unset($value);
        
        $this->ds = $ds;
        $this->pager = $pager;
        $this->gpc = $_GPC;
        $this->kwd = $kwd;
        $this->display('Salesroom_member/query');
    }
}
?>