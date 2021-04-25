<?php
/**
 * lionfish 商城系统
 *
 * ==========================================================================
 * @link      http://www.liofis.com/
 * @copyright Copyright (c) 2015 liofis.com. 
 * @license   http://www.liofis.com/license.html License
 * ==========================================================================
 * 小程序直播模块
 * @author J_da
 *
 */
namespace Home\Controller;

class LivevideoController extends CommonController {
	
	protected function _initialize()
    {
    	parent::_initialize();
    }

	/**
     * 获取列表
     */
	public function get_roominfo()
	{
		$gpc = I('request.');
		$pindex = max(1, intval($gpc['page']));
		$psize = 5;
		$params = array(':uniacid' => $_W['uniacid']);
		$offset = ($pindex - 1) * $psize;

		$expirtime = S('_inc_live_expirtime_');
		// if (time() > 600 + intval($expirtime, 0)) {
		// 	D('Livevideo')->syncRoomList();
		// }

		$list = M("lionfish_comshop_wxlive")
			->where(array('is_show'=>1))
			->order('is_top desc,start_time desc')
            ->limit($offset, $psize)
            ->select();

		if (!empty($list)) {
			foreach ($list as &$row) {
				$row['goods_list'] = json_decode($row['goods'], true);
				unset($row['goods']);

				$row['start_time'] = date('Y-m-d H:i', $row['start_time']);
				$row['end_time'] = date('Y-m-d H:i', $row['end_time']);

				$replayRes = M("lionfish_comshop_wxlive_replay")->where(array('roomid'=>$row['roomid']))->find();
				$row['has_replay'] = 0;
				if($replayRes) $row['has_replay'] = 1;
			}
		}

		// 分享信息
		$share = array();
		$share['name'] = D('Home/Front')->get_config_by_name('live_nav_name');
		$share['title'] = D('Home/Front')->get_config_by_name('live_share_title');
		$live_share_image = D('Home/Front')->get_config_by_name('live_share_image');
		if(!empty($live_share_image)) $share['img'] = tomedia($live_share_image);

		$showTabbar = false;
		$tabbar_out_type = D('Home/Front')->get_config_by_name('tabbar_out_type');
		if($tabbar_out_type==7) $showTabbar = true;

		if(count($list))
		{
			echo json_encode( array('code' => 0, 'data'=>$list, 'showTabbar'=>$showTabbar, 'share'=>$share) );
			die();
		} else{
			echo json_encode( array('code' => 1, 'showTabbar'=>$showTabbar, 'share'=>$share) );
			die();
		}
	}

	public function get_replay()
	{
		$gpc = I('request.');
		$roomid = intval($gpc['room_id'], 0);

		if(!$roomid) {
			echo json_encode( array('code' => 1, 'msg'=>'直播间id错误') );
			die();
		}

		$roomInfo = D('Livevideo')->getRoomInfo($roomid);

		if($roomInfo && $roomInfo['live_replay']) {
			$live_replay = unserialize($roomInfo['live_replay']);
			$roomInfo['goods_list'] = json_decode($roomInfo['goods'], true);
			unset($roomInfo['goods']);
			unset($roomInfo['live_replay']);
			echo json_encode( array('code' => 0, 'data'=>$live_replay, 'roominfo'=>$roomInfo, 'from'=>'sql') );
			die();
		} else {
			$res = D('Livevideo')->syncLiveReplay($roomid);

			if($res) {
				echo json_encode( array('code' => 0, 'data'=>$res, 'roominfo'=>$roomInfo, 'from'=>'wechat') );
				die();
			} else {
				echo json_encode( array('code' => 1, 'msg'=>'暂无回放') );
				die();
			}
		}

	}

	function getReqNum()
	{
		// S('_inc_live_replay_reqnum_', 0);
		// S('_inc_live_roominfo_reqnum_', 0);
		$replayReqNum = S('_inc_live_replay_reqnum_');
		$roominfoReqNum = S('_inc_live_roominfo_reqnum_');

		echo json_encode( array('code' => 0, 'replay'=>$replayReqNum, 'roominfo'=>$roominfoReqNum) );
		die();
	}
	
}