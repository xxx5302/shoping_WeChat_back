<?php
namespace Home\Model;
use Think\Model;
/**
 * 拼团模型模型
 * @author fish
 *
 */
class MakeModel{

    /**
     * @author yj
     * @desc 获取码科accessToken
     * 结构：token + express_time
     * @param int $uniacid
     */
	public function getMakeAccessToken()
    {

        $token_json = S('_makeAccessToken');

        $token_data = json_decode( $token_json, true);

        $now_time = time();

        $is_get_new = false;
        if( empty($token_data) )
        {
            $is_get_new = true;
        }else {
            $ver_result = $this->verifyToken();
            if( $ver_result['code'] == 1 )
            {
                $is_get_new = true;
            }
        }


        if( $is_get_new  )
        {
            $make_url = D('Home/Front')->get_config_by_name('localtown_mk_url');
            $wepro_appid = D('Home/Front')->get_config_by_name('wepro_appid');
            $token = D('Home/Front')->get_config_by_name('localtown_mk_token');

            //get_config_by_name('default_comunity_limit_mile');
            $url ="{$make_url}addons/make_speed/core/public/index.php/apis/v2/get_token";

            $post_data = array();
            $post_data['token'] =  $token;
            $post_data['appid'] =  $wepro_appid;

            $result_json =  http_request($url , http_build_query($post_data) );


            $result = json_decode( $result_json , true );

            if( empty($result) || isset($result['error_code']) )
            {
                return ['code' => 1, 'message' => '获取token失败：'.$result['msg'] ];
            }

            $data = array();
            $data['token'] = $result['token'];
            $data['expire_time'] = time() + 86400 ;

            $data_json = json_encode( $data );

            S('_makeAccessToken', $data_json );

            $token_data = $data;
        }

        return ['code' => 0, 'token' => $token_data['token'] ];

    }

    /**
     * @author yj
     * @desc
     * @param int $uniacid
     * @return array
     */
    public function verifyToken()
    {

        $token = D('Home/Front')->get_config_by_name('localtown_mk_token');

        $make_url = D('Home/Front')->get_config_by_name('localtown_mk_url');

        $url = "{$make_url}addons/make_speed/core/public/index.php/apis/v2/verify_token";

        $data = array();
        $data['token'] = $token;

        $result_json = http_request($url, http_build_query($data));

        $result = json_decode( $result_json, true );

        if( empty($result) || !$result['isValid'] )
        {
            return ['code' => 1, 'message' => 'token失效' ];
        }

        return ['code' => 0, 'message' => '有效'];

    }

    /**
     * @author yj
     * @desc 获取订单详情
     * @param $order_num
     * @param int $uniacid
     * @return array
     */
    public function getOrderDetail( $order_num)
    {

        $token_result = $this->getMakeAccessToken();

        if( $token_result['code'] == 1 )
        {
            return ['code' => 1, 'message' => 'token无效' ];
        }
        $token =  $token_result['token'];

        $make_url = D('Home/Front')->get_config_by_name('localtown_mk_url');

        $url = "{$make_url}addons/make_speed/core/public/index.php/apis/v2/get_order_detail";

        $data = [];
        $data['order_num'] = $order_num;
        $data['token'] = $token;

        $result_json = http_request($url, http_build_query($data));

        $result = json_decode( $result_json, true );

        if( empty($result) || $result['error_code'] > 0 )
        {
            return ['code' => 1, 'message' => $result['msg'] ];
        }
        return ['code' =>0, 'data' => $result['data'] ];
    }

    /**
     * @author yj
     * @desc 获取订单配送的状态
     * @param $order_num
     * @param int $uniacid
     * @return array
     */
    public function getOrderStatus( $order_num)
    {


        $token_result = $this->getMakeAccessToken();

        if( $token_result['code'] == 1 )
        {
            return ['code' => 1, 'message' => 'token无效' ];
        }

        $token =  $token_result['token'];

        $make_url = D('Home/Front')->get_config_by_name('localtown_mk_url');

        $url = "{$make_url}addons/make_speed/core/public/index.php/apis/v2/cancel_order";

        $data = [];
        $data['order_num'] = $order_num;
        $data['token'] = $token;

        $result_json = http_request($url, http_build_query($data));

        $result = json_decode( $result_json, true );

        if( empty($result) || $result['error_code'] > 0 )
        {
            return ['code' => 1, 'message' => $result['msg'] ];
        }
        //loading = 待付款', cancel='订单已取消', payed='待接单',wait_to_shop='待到店', accepted='待取件', geted='待收件',gotoed= '待评价', completed='订单已完成');
        return ['code' => 0, 'status' => $result['data']['status'], 'remark' => $result['data']['remark'] ];
    }

    /**
     * @author yj
     * @desc 取消配送订单
     * @param $order_num
     * @param int $uniacid
     * @return array
     */
    public function cancelOrder( $order_num)
    {

        $token_result = $this->getMakeAccessToken();

        if( $token_result['code'] == 1 )
        {
            return ['code' => 1, 'message' => 'token无效' ];
        }

        $token =  $token_result['token'];

        $make_url = D('Home/Front')->get_config_by_name('localtown_mk_url');

        $url = "{$make_url}addons/make_speed/core/public/index.php/apis/v2/cancel_order";

        $data = [];
        $data['order_num'] = $order_num;
        $data['token'] = $token;

        $result_json = http_request($url, http_build_query($data));

        $result = json_decode( $result_json, true );

        if( empty($result) || $result['error_code'] > 0 )
        {
            return ['code' => 1, 'message' => $result['msg'] ];
        }

        return ['code' => 0 ];

    }

    /**
     * @author yj
     * @desc 查询订单的配送费
     * @param $order_info
     */
    public function queryDeliverFee($order_info)
    {
        $fromcoord = $order_info['shipping_lat'].",".$order_info['shipping_lng'];

        $localtown_shop_lat = D('Home/Front')->get_config_by_name('localtown_shop_lat');
        $localtown_shop_lon = D('Home/Front')->get_config_by_name('localtown_shop_lon');

        $tocoord = $localtown_shop_lat.",".$localtown_shop_lon;

        $shop_id = 1;

        //供应商地址
        if($order_info['store_id'] > 0){
            $tocoord = $order_info['store_data']['shop_lat'].",".$order_info['store_data']['shop_lon'];
            //大客户版，供应商采用，否则都用平台的 todo..
        }

        $result = $this->getDeliveryPrice($fromcoord , $tocoord , $shop_id);


        return $result;
    }
    /**
     * @author yj
     * @desc 获取配送单价格 (运费)
     * @param $fromcoord
     * @param $tocoord
     * @param $shop_id
     */
    private function getDeliveryPrice($fromcoord , $tocoord , $shop_id)
    {


        $token_result = $this->getMakeAccessToken();

        if( $token_result['code'] == 1 )
        {
            return ['code' => 1, 'message' => 'token无效' ];
        }

        $token =  $token_result['token'];

        $make_url = D('Home/Front')->get_config_by_name('localtown_mk_url');

        $url = "{$make_url}addons/make_speed/core/public/index.php/apis/v2/get_delivery_price";

        $data = [];
        $data['token'] = $token;
        $data['fromcoord'] = $fromcoord;
        $data['tocoord'] = $tocoord;
        $data['shop_id'] = $shop_id;

        $result_json = http_request($url.'?'.http_build_query($data) );

        $result = json_decode( $result_json, true );

        if( empty($result) || $result['error_code'] > 0 )
        {
            return ['code' => 1, 'message' => $result['msg'] ];
        }
        return [
                    'code' => 0 ,
                    'distance' => $result['data']['distance'],// 距离 单位km
                    'init' => $result['data']['init'], //起步价
                    'total_price' => $result['data']['total_price'], //配送价格
                    'mileage_price' => $result['data']['mileage_price'],//里程费
                    'night_price' => $result['data']['night_price'],//夜间价格
                    'premium' => $result['data']['premium'], //溢价
         ];
    }


    public function addOrder( $order_info )
    {
        $goods_name = "";

        foreach($order_info['goods_list'] as $k=>$v){

            $goods_name .= $v['goods_name'];//物品名称
        }
        $pick_time = date('Y-m-d H:i:s');


        $remark = "";
        if( !empty( $order_info['expected_delivery_time'] ) )
        {
            $remark = "预约送达时间： ".$order_info['expected_delivery_time'];
        }

        /**
          {
             "begin_detail":"万达国际B座 1918室","begin_address":"南宁市西乡塘区安吉万达广场",
             "begin_lat":22.873332342342342333,"begin_lng":108.2927,"begin_username":"sweetsლ",
             "begin_phone":"15977966741",
         * "end_detail":"","end_address":"南宁-东盟企业总部基地二期",
             "end_lat":22.866558,"end_lng":108.280449,"end_username":"星星的亮光","end_phone":"13557432464"
         }

         */

        //判断是否独立供应商

        $localtown_shop_province_id = D('Home/Front')->get_config_by_name( 'localtown_shop_province_id');
        $localtown_shop_city_id = D('Home/Front')->get_config_by_name( 'localtown_shop_city_id' );
        $localtown_shop_area_id = D('Home/Front')->get_config_by_name( 'localtown_shop_area_id');
        $localtown_shop_country_id = D('Home/Front')->get_config_by_name( 'localtown_shop_country_id' );
        $localtown_shop_address = D('Home/Front')->get_config_by_name( 'localtown_shop_address');


        $address_json = [];

        /**
         * $store_data = [];
        $store_data['address'] = $province_id.$city_id.$area_id.$country_id.$shop_address;
        $store_data['city'] = $city_id;
        $store_data['shop_lon'] = $shop_lon;
        $store_data['shop_lat'] = $shop_lat;
        $store_data['shop_telephone'] = $shop_telephone;
         */
        if( $order_info['store_id'] > 0 )
        {

            $address_json['begin_detail'] = $order_info['store_data']['begin_detail'];
            $address_json['begin_address'] = $order_info['store_data']['begin_address'];
            $address_json['begin_lat'] = $order_info['store_data']['shop_lat'];
            $address_json['begin_lng'] = $order_info['store_data']['shop_lon'];
            $address_json['begin_username'] = $order_info['store_data']['begin_username'];//todo...
            $address_json['begin_phone'] = $order_info['store_data']['shop_telephone'];

        }else{
            $address_json['begin_detail'] = $localtown_shop_address;
            $address_json['begin_address'] = $localtown_shop_province_id.$localtown_shop_city_id.$localtown_shop_area_id.$localtown_shop_country_id;
            $address_json['begin_lat'] = D('Home/Front')->get_config_by_name( 'localtown_shop_lat' );
            $address_json['begin_lng'] = D('Home/Front')->get_config_by_name( 'localtown_shop_lon');
            $address_json['begin_username'] = $order_info['shoname'];
            $address_json['begin_phone'] = D('Home/Front')->get_config_by_name( 'localtown_shop_telephone');
        }





        $address_json['end_detail'] = "";
        $address_json['end_address'] = $order_info['shipping_address'];
        $address_json['end_lat'] = $order_info['shipping_lat'];
        $address_json['end_lng'] = $order_info['shipping_lng'];
        $address_json['end_username'] = $order_info['shipping_name'];
        $address_json['end_phone'] = $order_info['shipping_tel'];



        $address = json_encode( $address_json );

        //lionfish_comshop_orderdistribution_order
		$orderdistribution_order = M('lionfish_comshop_orderdistribution_order')->where( array('order_id' => $order_info['order_id']) )->find();
        $pay_price = $orderdistribution_order['shipping_money'];
        $total_price = $orderdistribution_order['shipping_money'];

        $shop_id = 1;//暂时1，未来大客户版本，加上商家后台配置的

		$shop_domain = D('Home/Front')->get_config_by_name('shop_domain');
        $notify_url = $shop_domain.'make_notify.php';

        $result = $this-> createOrder($goods_name,$pick_time,$remark,$address,$pay_price,$total_price,$shop_id,$notify_url);

        return $result;
    }

    /**
     * @author yj
     * @desc 创建订单
     * @param $goods_name
     * @param $pick_time
     * @param $remark
     * @param $address
     * @param $pay_price
     * @param $total_price
     * @param $shop_id
     * @param $notify_url
     * @param int $uniacid
     * @return array
     */
    private function createOrder($goods_name,$pick_time,$remark,$address,$pay_price,$total_price,$shop_id,$notify_url )
    {


        $token_result = $this->getMakeAccessToken();

        if( $token_result['code'] == 1 )
        {
            return ['code' => 1, 'message' => 'token无效' ];
        }

        $token =  $token_result['token'];
        $make_url = D('Home/Front')->get_config_by_name('localtown_mk_url');

        $url = "{$make_url}addons/make_speed/core/public/index.php/apis/v2/create_order";

        $data = [];
        $data['token'] = $token;
        $data['goods_name'] = $goods_name;
        $data['pick_time'] = $pick_time;
        $data['remark'] = $remark;
        $data['address'] = $address;
        $data['pay_price'] = $pay_price;
        $data['total_price'] = $total_price;
        $data['shop_id'] = $shop_id;
        $data['notify_url'] = $notify_url;

        $result_json = http_request($url, http_build_query($data));

        $result = json_decode( $result_json, true );

        if( empty($result) || $result['error_code'] > 0 )
        {
            return ['code' => 1, 'message' => $result['msg'] ];
        }

        $order_number = $result['data']['order_number'];

        return ['code' =>0, 'order_number' => $order_number ];

    }



}


?>