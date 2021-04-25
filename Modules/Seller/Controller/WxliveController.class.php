<?php
/**
 * lionfish 小程序直播插件
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
namespace Seller\Controller;

class WxliveController extends CommonController
{

    protected function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $gpc = I('request.');
        $pindex = max(1, intval($gpc['page']));
        $psize = 10;
        $condition = '1';
        $offset = ($pindex - 1) * $psize;

        $keywords = addslashes(trim($gpc['keywords']));
		$keyword2 = stripslashes($keywords);
		$this->keywords = $keyword2;
        if ($keywords !== '') {
            $condition .= " and name like '%". $keywords ."%' or anchor_name like '%". $keywords ."%'";
        }

        $list = M("lionfish_comshop_wxlive")
            ->where($condition)
            ->order('is_top desc,start_time asc')
            ->limit($offset, $psize)
            ->select();

        $total = M('lionfish_comshop_wxlive')->where($condition)->count();
        $pager = pagination2($total, $pindex, $psize);

        $this->list  = $list;
        $this->pager = $pager;
        $this->display();
    }

    public function setting()
    {
        if (IS_POST) {
            $data = I('request.parameter');

            $data['live_share_image'] = save_media($data['live_share_image']);
            $data['live_nav_name'] = trim($data['live_nav_name']);
            $data['live_share_title'] = trim($data['live_share_title']);
            
            D('Seller/Config')->update($data);
            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }
        $data = D('Seller/Config')->get_all_config();
        $this->data = $data;
        $this->display();
    }

    /**
     * 同步直播间
     */
    public function sync()
    {
        $ret = D('Home/Livevideo')->syncRoomList();
        if (isset($ret['errcode'])) {
            show_json(0, $ret['msg']);
        }

        show_json(1);
    }

    /**
     * 同步直播间回放
     */
    public function syncreplay()
    {
        $live_id = I('request.live_id');
        $roomid = I('request.roomid');

        $list = D('Home/Livevideo')->syncLiveReplayNew($roomid);

        if ($list["errcode"] != 0 || $list["errmsg"] != "ok") {
            return show_json(0, $list["errmsg"]);
        }

        if (0 < $list["total"]) {
            M('lionfish_comshop_wxlive_replay')->where(array('roomid'=>$roomid))->delete();
            $result = $list["live_replay"];

            foreach ($result as $item) {
                $item["expire_time"] = strtotime($item["expire_time"]);
                $item["create_time"] = strtotime($item["create_time"]);
                $item["add_time"] = time();
                $item["live_id"] = $live_id;
                $item["roomid"] = $roomid;
                M('lionfish_comshop_wxlive_replay')->add($item);
            }
        }

        return show_json(1, "获取回放成功");
    }

    /**
     * 获取回放
     */
    public function replay()
    {
        $live_id = I('request.id');
        $roomid = I('request.roomid');

        $list = M("lionfish_comshop_wxlive_replay")
            ->where(array('roomid'=>$roomid))
            ->select();

        $this->live_id = $live_id;
        $this->roomid = $roomid;
        $this->list = $list;
        $this->display();
    }

    /**
     * 显示隐藏直播间
     * @return [type] [Boolean]
     */
    public function change()
    {
        $id = I('request.id');
        //ids
        if (empty($id)) {
            $ids = I('request.ids');
            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = I('request.type');
        $value = I('request.value');

        if (!(in_array($type, array('is_show', 'displayorder')))) {
            show_json(0, array('message' => '参数错误'));
        }

        $items = M('lionfish_comshop_wxlive')->where( array('id' => array('in', $id)) )->select();
        foreach ($items as $item) {
            M('lionfish_comshop_wxlive')->where( array('id' => $item['id']) )->save(  array($type => $value) );
        }

        show_json(1 , array('url' => $_SERVER['HTTP_REFERER']));

    }

}
