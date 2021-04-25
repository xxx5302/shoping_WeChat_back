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
namespace Seller\Controller;

class UtilController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		
		$this->full       = intval($_GPC['full']);
        $this->platform   = trim($_GPC['platform']);
        $this->defaultUrl = trim($_GPC['url']);
        $this->allUrls    = array(
            array(
                'name' => '商城页面',
                'list' => array(
                    array('name' => '社区团购首页', 'url' => '/lionfish_comshop/pages/index/index', 'url_wxapp' => '/lionfish_comshop/pages/index/index'),
                    array('name' => '购物车', 'url' => '/lionfish_comshop/pages/order/shopCart', 'url_wxapp' => '/lionfish_comshop/pages/order/shopCart'),
                    array('name' => '团长申请页面', 'url' => '/lionfish_comshop/moduleA/groupCenter/apply', 'url_wxapp' => '/lionfish_comshop/moduleA/apply'),
					array('name' => '团长申请介绍页面', 'url' => '/lionfish_comshop/moduleA/groupCenter/recruit', 'url_wxapp' => '/lionfish_comshop/moduleA/groupCenter/recruit'),
					
					array('name' => '供应商申请页面', 'url' => '/lionfish_comshop/pages/supply/apply', 'url_wxapp' => '/lionfish_comshop/pages/supply/apply'),
					array('name' => '供应商介绍页面地址', 'url' => '/lionfish_comshop/pages/supply/recruit', 'url_wxapp' => '/lionfish_comshop/pages/supply/recruit'),
					// array('name' => '会员表单信息收集页面', 'url' => '/lionfish_comshop/pages/form/apply', 'url_wxapp' => '/lionfish_comshop/pages/form/apply'),
					
					array('name' => '分类页', 'url' => '/lionfish_comshop/pages/type/index', 'url_wxapp' => '/lionfish_comshop/pages/type/index'),
                    array('name' => '余额充值', 'url' => '/lionfish_comshop/pages/user/charge', 'url_wxapp' => '/lionfish_comshop/pages/user/charge'),

					array('name' => '视频商品列表', 'url' => '/lionfish_comshop/moduleA/video/index', 'url_wxapp' => '/lionfish_comshop/moduleA/video/index'),
					
					array('name' => '群接龙', 'url' => '/lionfish_comshop/moduleA/solitaire/index', 'url_wxapp' => '/lionfish_comshop/moduleA/solitaire/index'),

                    array('name' => '仅快递商品列表页', 'url' => '/lionfish_comshop/moduleB/generalmall/index', 'url_wxapp' => '/lionfish_comshop/moduleB/generalmall/index'),
				
				),
            ),
			/**
            array(
                'name' => '商品属性',
                'list' => array(
                    array('name' => '分类搜索', 'url' => '/lionfish_comshop/pages/goods/search', 'url_wxapp' => '/lionfish_comshop/pages/goods/search'),
                ),
            ),
			**/
            array(
                'name' => '会员中心',
                'list' => array(
                    array('name' => '会员中心', 'url' => '/lionfish_comshop/pages/user/me', 'url_wxapp' => '/lionfish_comshop/pages/user/me'),
                    array('name' => '订单列表', 'url' => '/lionfish_comshop/pages/order/index', 'url_wxapp' => '/lionfish_comshop/pages/order/index'),
					
					array('name' => '关于我们', 'url' => '/lionfish_comshop/pages/user/articleProtocol?about=1', 'url_wxapp' => '/lionfish_comshop/pages/user/articleProtocol?about=1'),
                    array('name' => '常见帮助', 'url' => '/lionfish_comshop/pages/user/protocol', 'url_wxapp' => '/lionfish_comshop/pages/user/protocol'),
					
				   // array('name' => '订单列表', 'url' => '/lionfish_comshop/pages/order/pintuan', 'url_wxapp' => '/lionfish_comshop/pages/order/pintuan'),
                   // array('name' => '拼团列表', 'url' => '/lionfish_comshop/pages/order/pintuan', 'url_wxapp' => '/lionfish_comshop/pages/order/pintuan'),
                   // array('name' => '我的收藏', 'url' => '/lionfish_comshop/pages/dan/myfav', 'url_wxapp' => '/lionfish_comshop/pages/dan/myfav'),
                   // array('name' => '我的优惠券', 'url' => '/lionfish_comshop/pages/dan/quan', 'url_wxapp' => '/lionfish_comshop/pages/dan/quan'),

                ),
            ),
			array(
                'name' => '其他',
                'list' => array(
                    array('name' => '供应商列表', 'url' => '/lionfish_comshop/pages/supply/index', 'url_wxapp' => '/lionfish_comshop/pages/supply/index'),
                    array('name' => '专题列表', 'url' => '/lionfish_comshop/moduleA/special/list', 'url_wxapp' => '/lionfish_comshop/pages/special/list'),
                    array('name' => '拼团首页', 'url' => '/lionfish_comshop/moduleA/pin/index', 'url_wxapp' => '/lionfish_comshop/moduleA/pin/index'),
					array('name' => '付费会员首页', 'url' => '/lionfish_comshop/moduleA/vip/upgrade', 'url_wxapp' => '/lionfish_comshop/moduleA/vip/upgrade'),
					array('name' => '积分/签到/兑换', 'url' => '/lionfish_comshop/moduleA/score/signin', 'url_wxapp' => '/lionfish_comshop/moduleA/score/signin'),
					array('name' => '菜谱', 'url' => '/lionfish_comshop/moduleA/menu/index', 'url_wxapp' => '/lionfish_comshop/moduleA/menu/index'),
                    array('name' => '整点秒杀', 'url' => '/lionfish_comshop/moduleA/seckill/list', 'url_wxapp' => '/lionfish_comshop/moduleA/seckill/list'),
					array('name' => '直播列表', 'url' => '/lionfish_comshop/moduleB/live/index', 'url_wxapp' => '/lionfish_comshop/moduleB/live/index'),
				)
            )
        );
	}
	
	
	public function selecturl()
    {

        $platform = $this->platform;
        $full     = $this->full;

        $allUrls = $this->allUrls;

         $this->display();

    }
	
	public function query()
    {
        
        $type     = I('request.type');
        $kw       = I('request.kw');
        $full     = I('request.full');
        $platform = I('request.platform');
		
		$this->type = $type;
		$this->kw = $kw;
		$this->full = $full;
		$this->platform = $platform;
		
        if (!empty($kw) && !empty($type)) {

            if ($type == 'good') {
				
                $list = M()->query('SELECT id,goodsname as title,productprice,price as marketprice,sales,type FROM ' .
                    C('DB_PREFIX') . 'lionfish_comshop_goods WHERE  grounding=1 and total > 0 
					AND goodsname LIKE "'.'%' . $kw . '%'.'" ');

                if (!empty($list)) {
                    foreach ($list as &$val) {
                        
						$thumb = M('lionfish_comshop_goods_images')->where( array('goods_id' => $val['id']) )->order('id asc')->find();
					
                        $val['thumb'] = tomedia($thumb['image']);
                    }
                }

                //$list = set_medias($list, 'thumb');
                //thumb
            } else if ($type == 'article') {
                
				$list = M('lionfish_comshop_article')->field('id,title')->where( " (title LIKE '%".$kw."%' or id like '%".$kw."%' ) and enabled=1" )->select();
            } else if ($type == 'coupon') {
                $list = M('lionfish_comshop_coupon')->field('id,voucher_title')->where( " (voucher_title LIKE '%".$kw."%' or id like '%".$kw."%' ) " )->select();
            } else if ($type == 'groups') {
               
            } else if ($type == 'sns') {
                
            } else if ($type == 'url') {
            	$list = $this->searchUrl($this->allUrls, "name", $kw);
			} else if ($type == 'special') {
                
				$list = M('lionfish_comshop_special')->field('id,name')->where("name LIKE '%{$kw}%' and enabled=1  ")->select();
            } 
			else if ($type == 'category') {
                	
				$list = M('lionfish_comshop_goods_category')->field('id,name')->where( " name LIKE '%{$kw}%' " )->where(['cate_type'=>'normal'])->select();
            }
            else if ($type == 'solitaire') {

                $list = M('lionfish_comshop_solitaire')->field('id,solitaire_name as name')->where( " solitaire_name LIKE '%{$kw}%' and state = 1 " )->select();
            }
            else if ($type == 'pintuan') {

                $list = M('lionfish_comshop_goods')->field('id,goodsname as name')->where( " goodsname LIKE '%{$kw}%' and type = 'pin' and grounding = 1 " )->select();

                $list = M()->query("SELECT id,goodsname as title,productprice,price as marketprice,sales,type FROM " .
                    C('DB_PREFIX') . "lionfish_comshop_goods WHERE  grounding=1 and total > 0 and type = 'pin' "
					. " AND goodsname LIKE '%" . $kw . "%'");

                if (!empty($list)) {
                    foreach ($list as &$val) {

                        $thumb = M('lionfish_comshop_goods_images')->where( array('goods_id' => $val['id']) )->order('id asc')->find();

                        $val['thumb'] = tomedia($thumb['image']);
                    }
                }
            }
			else {
                if ($type == 'creditshop') {
                    
                }
            }
        }
		
		$this->list = $list;
       //dump($list);die;
        $this->display('Util/selecturl_tpl');
    }
	
	
	
}
?>