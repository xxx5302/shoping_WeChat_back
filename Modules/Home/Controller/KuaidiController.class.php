<?php
/**
 * Class KuaidiController
 * 商家快递包裹信息
 *
 */
namespace Home\Controller;

class KuaidiController extends CommonController {

    /*
    记录快递包裹存放信息
    */
    public function save_package(){
        $_GPC = I('request.');
        $records_data = array();
        $records_data['package_code'] = $_GPC['packageCode'];
        $records_data['status'] = $_GPC['status'];
        $records_data['save_time'] = date("Y-m-d H:i:s");
        $records_data['head_name'] = $_GPC['headName'];
        $records_data['community_name'] = $_GPC['communityName'];
        $records_data['operator'] = $_GPC['operator'];
        $records_data['place'] = $_GPC['place'];
        $result = M('package')->add($records_data);
        echo $result;
        

    }
  //  /*
  //  查询快递包裹存放信息
  //  */
  //  public function get_package(){
    	
  //      $_GPC = I('request.');
  //      $package_data = $_GPC['packageCode'];
		//  $result = M('package')->where( array('package_code' => $package_data ) )->find();

		// // $package_info = M('package')->field('place')->where( array('package_code' => $package_data ) )->find();
		// echo json_encode($result);
	   


  //  }
    
    
    /*
    查询快递包裹存放信息
    */
    public function get_package(){
    	
        $_GPC = I('request.');
        //返回结果
        $result = array();
        $result['code'] = 1;
        $result['data'] = '';
        //查询条件
        $param = array();
        $param['package_code'] = $_GPC['packageCode'];
		//更新字段内容
		$data = array();
        $data['last_query_name'] = $_GPC['lastQueryName'];
        $data['last_query_time'] = date("Y-m-d H:i:s");
	    
		$result['data'] = M('package')->where( array('package_code' => $param['package_code'] ) )->find();
		if (empty($result['data'])) {
			
			$result['code'] = 0;
			//无记录
			echo json_encode($result);
			die();
		}
		//TODO..最后一次查询时间更新
		M('package')->where( $param )->save( $data );
		M('package')->where( $param )->setInc('query_times');
		//M('package')->where( array('package_code' => $param['package_code']))->save($param['last_query_time']);
		// $package_info = M('package')->field('place')->where( array('package_code' => $package_data ) )->find();
		echo json_encode($result);
	   


    }
     /*
    查询快递包裹存放信息（包裹录入前检查，仅检查是否录入过，不做查询日志记录）
    */
    public function get_package_head(){
    	
        $_GPC = I('request.');
        //返回结果
        $result = array();
        $result['code'] = 1;
        $result['data'] = '';
        //查询条件
        $param = array();
        $param['package_code'] = $_GPC['packageCode'];
		
	    
		$result['data'] = M('package')->where( array('package_code' => $param['package_code'] ) )->find();
		if (empty($result['data'])) {
			
			$result['code'] = 0;
			//无记录
			echo json_encode($result);
			die();
		}
		
		echo json_encode($result);
	   


    }


	/*
    更新取件信息
    */
    public function update_package_getinfo(){
    	
        $_GPC = I('request.');
        //返回结果
        $result = array();
        $result['code'] = 1;
        $result['data'] = '';
        //查询条件
        $param = array();
        $param['package_code'] = $_GPC['packageCode'];
		//更新字段内容
		$data = array();
        $data['get_user'] = $_GPC['get_user'];
        $data['take_time'] = date("Y-m-d H:i:s");
        $data['status'] = 2;

		//TODO..最后一次查询时间更新
		$result['data'] = M('package')->where( $param )->save( $data );
		//M('package')->where( array('package_code' => $param['package_code']))->save($param['last_query_time']);
		// $package_info = M('package')->field('place')->where( array('package_code' => $package_data ) )->find();
		echo json_encode($result);
	   


    }


}