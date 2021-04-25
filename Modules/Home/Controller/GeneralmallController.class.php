<?php
/**
 * lionfish 商城系统
 *
 * ==========================================================================
 * @link      http://www.liofis.com/
 * @copyright Copyright (c) 2015 liofis.com.
 * @license   http://www.liofis.com/license.html License
 * ==========================================================================
 * 商城模块
 * @author    fish
 *
 */
namespace Home\Controller;

class GeneralmallController extends CommonController
{

    protected function _initialize()
    {
        parent::_initialize();
    }

    public function get_index_info()
    {
        $_GPC = I('request.');
        $gid = isset($gpc['gid']) ? intval($gpc['gid']) : 0;

        $navigat_list = M('lionfish_comshop_generalmall_navigat')->field('id,navname,thumb,link,type,appid')->where("enabled=1")->order('displayorder desc')->select();

        if (!empty($navigat_list)) {
            foreach ($navigat_list as $key => $val) {
                $val['thumb'] = tomedia($val['thumb']);
                $navigat_list[$key] = $val;
            }
        } else {
            $navigat_list = array();
        }

        $slider_list = M('lionfish_comshop_generalmall_adv')->where(array('enabled' => 1, 'type' => 'slider'))->order('displayorder desc, id desc')->select();
        if (!empty($slider_list)) {
            foreach ($slider_list as $key => $val) {
                $val['image']      = tomedia($val['thumb']);
                $slider_list[$key] = $val;
            }
        } else {
            $slider_list = array();
        }

        $res                 = array();
        $res['navigat_list'] = $navigat_list;
        $res['slider_list']  = $slider_list;

        $res['shoname'] = D('Home/Front')->get_config_by_name('shoname');
        $res['theme'] = D('Home/Front')->get_config_by_name('index_list_theme_type');
        $res['index_type_first_name'] = D('Home/Front')->get_config_by_name('index_type_first_name');
        $res['category_list'] = D('Home/GoodsCategory')->get_index_goods_category($gid);

        $res['index_list_top_image'] = "";
        $index_list_top_image = D('Home/Front')->get_config_by_name('index_list_top_image');
        if( !empty($res['index_list_top_image']) )
        {
            $res['index_list_top_image'] = tomedia($index_list_top_image);
        }
        $res['index_list_top_image_on'] = D('Home/Front')->get_config_by_name('index_list_top_image_on');

        $tabbar_out_type = D('Home/Front')->get_config_by_name('tabbar_out_type');
		$res['showTabbar'] = $tabbar_out_type==8 ? true: false;

        $res['is_show_list_timer'] = D('Home/Front')->get_config_by_name('is_show_list_timer');
        $res['is_show_list_count'] = D('Home/Front')->get_config_by_name('is_show_list_count');
        
        $result = array('code' => 0, 'data' => $res);
        echo json_encode($result);
        die();
    }

}
